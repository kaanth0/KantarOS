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
                
                // 1. String Tablosunu Oku (Varsa)
                $sharedStrings = [];
                if (($index = $zip->locateName('xl/sharedStrings.xml')) !== false) {
                    $xmlContent = $zip->getFromIndex($index);
                    $xml = simplexml_load_string($xmlContent);
                    if ($xml && isset($xml->si)) {
                        foreach ($xml->si as $val) {
                            $sharedStrings[] = (string)$val->t;
                        }
                    }
                }

                $eklenenSayisi = 0;
                $atlananSayisi = 0;

                // 2. Tüm sekmeleri tara
                for ($s = 1; $s <= 30; $s++) {
                    $sheetPath = 'xl/worksheets/sheet' . $s . '.xml';
                    
                    if (($index = $zip->locateName($sheetPath)) !== false) {
                        $xmlContent = $zip->getFromIndex($index);
                        $xml = simplexml_load_string($xmlContent);
                        if (!$xml || !isset($xml->sheetData->row)) continue;

                        foreach ($xml->sheetData->row as $row) {
                            $hucreler = [];
                            foreach ($row->c as $cell) {
                                $coord = (string)$cell['r'];
                                preg_match('/^([A-Z]+)(\d+)$/', $coord, $matches);
                                $colLetters = $matches[1] ?? 'A';
                                
                                $colIndex = 0;
                                $length = strlen($colLetters);
                                for ($i = 0; $i < $length; $i++) {
                                    $colIndex = $colIndex * 26 + (ord($colLetters[$i]) - ord('A') + 1);
                                }
                                $colIndex -= 1;

                                $val = "";
                                $t = (string)$cell['t'];
                                
                                // Değerin nerede saklandığını kontrol et (Inline string, Shared string veya direkt v)
                                if (isset($cell->is->t)) {
                                    $val = (string)$cell->is->t;
                                } elseif ($t == 's' && isset($cell->v)) {
                                    $v = (int)$cell->v;
                                    $val = $sharedStrings[$v] ?? (string)$cell->v;
                                } elseif (isset($cell->v)) {
                                    $val = (string)$cell->v;
                                }
                                
                                $hucreler[$colIndex] = trim($val);
                            }

                            // SÜTUN ENDEXLERİ:
                            // 0: Fiş/S. No, 1: Tarih, 2: Gittiği Yer, 3: Cinsi, 4: Ağırlık, 5: Kg Fiat, 6: Toplam, 7: Nakliye, 8: G.Top, 9: Durum, 11: Plaka
                            $fis_no     = $hucreler[0] ?? '';
                            $tarih_ham  = $hucreler[1] ?? '';
                            $firma_adi  = $hucreler[2] ?? '';
                            $cinsi      = $hucreler[3] ?? '';
                            $agirlik    = (float)($hucreler[4] ?? 0);
                            $kg_fiyat   = (float)($hucreler[5] ?? 0);
                            $nakliye    = (float)($hucreler[7] ?? 0);
                            $tutar      = (float)($hucreler[8] ?? 0);
                            $durum      = $hucreler[9] ?? 'Açık';
                            $plaka      = $hucreler[11] ?? '';
                            $islem_tipi = 'Gelir';

                            // Başlık satırını veya boş satırları atla
                            if (empty($fis_no) || empty($tarih_ham) || $fis_no == 'S.' || $fis_no == 'FİŞ NO' || empty($firma_adi)) {
                                continue;
                            }

                            // Tarih Dönüştürücü
                            $tarih = '';
                            if (is_numeric($tarih_ham)) {
                                $tarih = date('Y-m-d', ($tarih_ham - 25569) * 86400);
                            } else {
                                $tarih_ham = str_replace(['.', '/'], '-', $tarih_ham);
                                // Gün.Ay.Yıl formatı için düzeltme (Örn: 01.01.2026)
                                $parcalar = explode('-', $tarih_ham);
                                if (count($parcalar) == 3 && strlen($parcalar[0]) <= 2) {
                                    $tarih_ham = $parcalar[2] . '-' . $parcalar[1] . '-' . $parcalar[0];
                                }
                                $zaman = strtotime($tarih_ham);
                                if ($zaman) {
                                    $tarih = date('Y-m-d', $zaman);
                                }
                            }

                            if (empty($tarih) || $tarih == '1970-01-01') {
                                continue;
                            }

                            $gercek_fis = preg_replace('/[^0-9]/', '', $fis_no);
                            if (empty($gercek_fis)) $gercek_fis = $fis_no;

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

                            // Mükerrer Kontrolü
                            $kontrolSorgu = $db->prepare("SELECT id FROM islemler WHERE fis_no = ? AND islem_tarihi = ?");
                            $kontrolSorgu->execute([$gercek_fis, $tarih]);
                            $ayniKayitVarMi = $kontrolSorgu->fetch(PDO::FETCH_ASSOC);

                            if (!$ayniKayitVarMi) {
                                $islemEkle = $db->prepare("
                                    INSERT INTO islemler (cari_id, fis_no, islem_tarihi, islem_tipi, tutar, cinsi, agirlik, kg_fiyat, nakliye, durum, plaka) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                                ");
                                $islemEkle->execute([$cari_id, $gercek_fis, $tarih, $islem_tipi, $tutar, $cinsi, $agirlik, $kg_fiyat, $nakliye, $durum, $plaka]);
                                $eklenenSayisi++;
                            } else {
                                $atlananSayisi++;
                            }
                        }
                    }
                }

                $zip->close();
                $mesaj = "Harika! Toplam $eklenenSayisi sevkiyat kaydı başarıyla eklendi. (Mükerrer/Geçersiz atlanan: $atlananSayisi)";
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
            <h3 class="fw-bold mb-1">📊 Kantar Föylerini İçeri Aktar</h3>
            <p class="text-muted small mb-0">Tüm formatlar (Inline ve Shared Strings) desteklenir.</p>
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
                <div class="fs-1 mb-2">🚀</div>
                <label for="excel_dosya" class="form-label fw-semibold text-white d-block">Excel Dosyanızı Seçin</label>
                <input class="form-control" type="file" id="excel_dosya" name="excel_dosya" accept=".xlsx" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-success fw-bold py-2">🔥 Sevkiyatları Yükle</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>