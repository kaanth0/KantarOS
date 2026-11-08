<?php
include 'baglan.php';

$mesaj = "";
$mesajTipi = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['excel_dosya'])) {
    $dosya = $_FILES['excel_dosya'];

    if ($dosya['error'] === UPLOAD_ERR_OK) {
        $geciciYol = $dosya['tmp_name'];
        $dosyaAdi = $dosya['name'];
        $uzanti = pathinfo($dosyaAdi, PATHINFO_EXTENSION);

        if ($uzanti === 'xlsx') {
            $zip = new ZipArchive();
            if ($zip->open($geciciYol) === TRUE) {
                
                // 1. String Tablosunu Oku
                $sharedStrings = [];
                if (($index = $zip->locateName('xl/sharedStrings.xml')) !== false) {
                    $xmlContent = $zip->getFromIndex($index);
                    $xml = simplexml_load_string($xmlContent);
                    foreach ($xml->si as $val) {
                        $sharedStrings[] = (string)$val->t;
                    }
                }

                $eklenenSayisi = 0;
                $atlananSayisi = 0;

                // 2. Tüm Sekmeleri Tara
                for ($s = 1; $s <= 35; $s++) {
                    $sheetPath = 'xl/worksheets/sheet' . $s . '.xml';
                    
                    if (($index = $zip->locateName($sheetPath)) !== false) {
                        $xmlContent = $zip->getFromIndex($index);
                        $xml = simplexml_load_string($xmlContent);

                        $headerMap = []; // Sütun harflerini başlık isimleriyle eşleyeceğiz (Örn: A => FİŞ NO)
                        $satirIndex = 0;

                        foreach ($xml->sheetData->row as $row) {
                            $satirIndex++;
                            
                            $hucreler = [];
                            foreach ($row->c as $cell) {
                                $coord = (string)$cell['r'];
                                preg_match('/^([A-Z]+)(\d+)$/', $coord, $matches);
                                $colLetter = $matches[1] ?? '';

                                $v = (string)$cell->v;
                                $t = (string)$cell['t'];

                                $val = ($t == 's' && isset($sharedStrings[$v])) ? $sharedStrings[$v] : $v;
                                $hucreler[$colLetter] = trim($val);
                            }

                            // 1. Satır Başlıkları Belirle
                            if ($satirIndex == 1 || empty($headerMap)) {
                                foreach ($hucreler as $col => $val) {
                                    $temizBaslik = mb_strtoupper(trim($val), 'UTF-8');
                                    if (strpos($temizBaslik, 'FİŞ') !== false || strpos($temizBaslik, 'FIS') !== false) {
                                        $headerMap['fis_no'] = $col;
                                    } elseif (strpos($temizBaslik, 'TARİH') !== false) {
                                        $headerMap['tarih'] = $col;
                                    } elseif (strpos($temizBaslik, 'GİTTİĞİ') !== false || strpos($temizBaslik, 'GITTIGI') !== false || strpos($temizBaslik, 'FİRMA') !== false) {
                                        $headerMap['firma'] = $col;
                                    } elseif (strpos($temizBaslik, 'CİNSİ') !== false || strpos($temizBaslik, 'CINSI') !== false) {
                                        $headerMap['cinsi'] = $col;
                                    } elseif (strpos($temizBaslik, 'AGIR') !== false || strpos($temizBaslik, 'AĞIR') !== false) {
                                        $headerMap['agirlik'] = $col;
                                    } elseif (strpos($temizBaslik, 'KG FİAT') !== false || strpos($temizBaslik, 'KG FIAT') !== false) {
                                        $headerMap['kg_fiyat'] = $col;
                                    } elseif (strpos($temizBaslik, 'NAKLİ') !== false || strpos($temizBaslik, 'NAKLI') !== false) {
                                        $headerMap['nakliye'] = $col;
                                    } elseif (strpos($temizBaslik, 'G.TOP') !== false) {
                                        $headerMap['tutar'] = $col;
                                    } elseif (strpos($temizBaslik, 'İLETİŞİM') !== false || strpos($temizBaslik, 'DURUM') !== false) {
                                        $headerMap['durum'] = $col;
                                    } elseif (strpos($temizBaslik, 'PLAKA') !== false) {
                                        $headerMap['plaka'] = $col;
                                    }
                                }
                                continue; // Başlık satırını atla
                            }

                            // Eğer başlıklar eşleştiyse verileri çek
                            if (!isset($headerMap['fis_no']) || !isset($headerMap['firma'])) {
                                continue; // Bu sekmede aradığımız tablolar yoksa geç
                            }

                            $fis_no     = $hucreler[$headerMap['fis_no'] ?? ''] ?? '';
                            $tarih_ham  = $hucreler[$headerMap['tarih'] ?? ''] ?? '';
                            $firma_adi  = $hucreler[$headerMap['firma'] ?? ''] ?? '';
                            $cinsi      = $hucreler[$headerMap['cinsi'] ?? ''] ?? '';
                            $agirlik    = (float)($hucreler[$headerMap['agirlik'] ?? ''] ?? 0);
                            $kg_fiyat   = (float)($hucreler[$headerMap['kg_fiyat'] ?? ''] ?? 0);
                            $nakliye    = (float)($hucreler[$headerMap['nakliye'] ?? ''] ?? 0);
                            $tutar      = (float)($hucreler[$headerMap['tutar'] ?? ''] ?? 0);
                            $durum      = $hucreler[$headerMap['durum'] ?? ''] ?? 'Açık';
                            $plaka      = $hucreler[$headerMap['plaka'] ?? ''] ?? '';
                            $islem_tipi = 'Gelir';

                            // Boş veya geçersiz satırları atla
                            if (empty($fis_no) || $fis_no == 'FİŞ NO' || empty($firma_adi) || !is_numeric(str_replace(['.', ','], ['', '.'], $fis_no))) {
                                continue;
                            }

                            // Tarih Formatı Düzeltme
                            $tarih = date('Y-m-d');
                            if (is_numeric($tarih_ham)) {
                                $tarih = date('Y-m-d', ($tarih_ham - 25569) * 86400);
                            } elseif (!empty($tarih_ham)) {
                                $tarih_ham = str_replace(['.', '/'], '-', $tarih_ham);
                                $tarih = date('Y-m-d', strtotime($tarih_ham));
                            }

                            // Cari Kontrolü / Ekleme
                            $cariSorgu = $db->prepare("SELECT id FROM cariler WHERE firma_adi = ?");
                            $cariSorgu->execute([$firma_adi]);
                            $cari = $cariSorgu->fetch(PDO::FETCH_ASSOC);

                            if (!$cari) {
                                $cariEkle = $db->prepare("INSERT INTO cariler (firma_adi) VALUES (?)");
                                $cariEkle->execute([$firma_adi]);
                                $cari_id = $db->lastInsertId();
                            } else {
                                $cari_id = $cari['id'];
                            }

                            // Mükerrer Kontrolü (Fiş No ile)
                            $kontrolSorgu = $db->prepare("SELECT id FROM islemler WHERE fis_no = ?");
                            $kontrolSorgu->execute([$fis_no]);
                            $ayniKayitVarMi = $kontrolSorgu->fetch(PDO::FETCH_ASSOC);

                            if (!$ayniKayitVarMi) {
                                $islemEkle = $db->prepare("
                                    INSERT INTO islemler (cari_id, fis_no, islem_tarihi, islem_tipi, tutar, cinsi, agirlik, kg_fiyat, nakliye, durum, plaka) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                                ");
                                $islemEkle->execute([$cari_id, $fis_no, $tarih, $islem_tipi, $tutar, $cinsi, $agirlik, $kg_fiyat, $nakliye, $durum, $plaka]);
                                $eklenenSayisi++;
                            } else {
                                $atlananSayisi++;
                            }
                        }
                    }
                }

                $zip->close();
                $mesaj = "Harika! Başlık bazlı akıllı motorla $eklenenSayisi kantar fişi eklendi. (Mükerrer atlanan: $atlananSayisi)";
                $mesajTipi = "success";

            } else {
                $mesaj = "Excel dosyası okunamadı.";
                $mesajTipi = "danger";
            }
        } else {
            $mesaj = "Lütfen geçerli bir .xlsx dosyası yükleyin!";
            $mesajTipi = "warning";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kantar Yükle — Finans Otomasyonu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="container my-5" style="max-width: 650px;">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="fw-bold mb-1">📊 Akıllı Başlık Eşlemeli Kantar Aktarımı</h3>
            <p class="text-muted small mb-0">Excel sütun yerleşimi ne olursa olsun nokta atışı okur.</p>
        </div>
        <a href="index.php" class="btn btn-outline-light btn-sm rounded-3">← Panele Dön</a>
    </div>

    <?php if(!empty($mesaj)): ?>
        <div class="alert alert-<?= $mesajTipi ?> border-0 text-white bg-opacity-20 shadow-sm rounded-3 mb-4" role="alert">
            <?= $mesaj ?>
        </div>
    <?php endif; ?>

    <div class="card p-4 shadow-lg">
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="mb-4 text-center p-4 rounded-3" style="border: 2px dashed rgba(255, 255, 255, 0.15); background: rgba(255, 255, 255, 0.02);">
                <div class="fs-1 mb-2">🧠</div>
                <label for="excel_dosya" class="form-label fw-semibold text-white d-block">Hesap 2026.xlsx Dosyanızı Seçin</label>
                <input class="form-control" type="file" id="excel_dosya" name="excel_dosya" accept=".xlsx" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-success fw-bold py-2">🚀 Akıllı Veri Aktarımını Başlat</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>