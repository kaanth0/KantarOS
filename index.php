<?php
include 'baglan.php';

// Seçilen Ay Filtresi
$secilenAy = isset($_GET['ay']) ? $_GET['ay'] : date('Y-m');
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kantar ve Sevkiyat Paneli — Finans Otomasyonu</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts (Inter) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- VIP Black Edition CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="container my-5" style="max-width: 1300px;">

    <!-- ÜST BİLGİ VE AKSİYONLAR -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1 text-white">🚛 Kantar & Sevkiyat Operasyon Paneli</h2>
            <p class="text-muted small mb-0">Şantiye bazlı sevkiyatlar, plaka takibi, nakliye ve ödeme durumları.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="veri-yukle.php" class="btn btn-success btn-sm rounded-3 px-3 fw-semibold">
                📊 Excel / Kantar Yükle
            </a>

            <!-- SIFIRLAMA BUTONU -->
            <form action="verileri-sifirla.php" method="POST" id="sifirlaForm" class="d-inline">
                <input type="hidden" name="onayli_sifirla" value="evet">
                <button type="button" onclick="verileriSifirlaOnay()" class="btn btn-outline-danger btn-sm rounded-3 px-3">
                    🗑️ Verileri Sıfırla
                </button>
            </form>
        </div>
    </div>

    <!-- SIFIRLAMA BAŞARI MESAJI -->
    <?php if(isset($_GET['durum']) && $_GET['durum'] == 'sifirlandi'): ?>
        <div class="alert alert-danger border-0 text-white bg-opacity-25 shadow-sm rounded-3 mb-4 text-center" role="alert">
            🧹 Sistem verileri ve cari geçmişi başarıyla tamamen sıfırlandı!
        </div>
    <?php endif; ?>

    <!-- AYLIK FİLTRELEME ÇUBUĞU -->
    <div class="card p-3 mb-4 border-0 shadow-sm" style="background: rgba(255, 255, 255, 0.02);">
        <form method="GET" action="" class="row g-3 align-items-center">
            <div class="col-auto">
                <label for="ay" class="col-form-label text-muted small fw-semibold">DÖNEM SEÇ:</label>
            </div>
            <div class="col-auto">
                <input type="month" id="ay" name="ay" value="<?= $secilenAy ?>" class="form-control form-control-sm bg-dark text-white border-secondary">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm px-3">Filtrele</button>
                <a href="index.php" class="btn btn-outline-secondary btn-sm px-2">Sıfırla</a>
            </div>
        </form>
    </div>

    <?php
    // ÖZET KARTLAR İÇİN HESAPLAMALAR
    $toplamCiroSorgu = $db->prepare("SELECT SUM(tutar) as toplam FROM islemler WHERE DATE_FORMAT(islem_tarihi, '%Y-%m') = ?");
    $toplamCiroSorgu->execute([$secilenAy]);
    $aylikCiro = $toplamCiroSorgu->fetch(PDO::FETCH_ASSOC)['toplam'] ?? 0;

    $toplamNakliyeSorgu = $db->prepare("SELECT SUM(nakliye) as toplam FROM islemler WHERE DATE_FORMAT(islem_tarihi, '%Y-%m') = ?");
    $toplamNakliyeSorgu->execute([$secilenAy]);
    $aylikNakliye = $toplamNakliyeSorgu->fetch(PDO::FETCH_ASSOC)['toplam'] ?? 0;

    $seferSayisiSorgu = $db->prepare("SELECT COUNT(*) as toplam FROM islemler WHERE DATE_FORMAT(islem_tarihi, '%Y-%m') = ?");
    $seferSayisiSorgu->execute([$secilenAy]);
    $toplamSefer = $seferSayisiSorgu->fetch(PDO::FETCH_ASSOC)['toplam'] ?? 0;
    ?>

    <!-- ÜST ÖZET KARTLARI -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card p-3 border-0 shadow-sm" style="background: rgba(25, 135, 84, 0.1); border-left: 4px solid #198754 !important;">
                <span class="text-muted small text-uppercase fw-semibold">Dönem Toplam Ciro (G.Top)</span>
                <h3 class="fw-bold text-success mt-1 mb-0"><?= number_format($aylikCiro, 2, ',', '.') ?> ₺</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 border-0 shadow-sm" style="background: rgba(13, 110, 253, 0.1); border-left: 4px solid #0d6efd !important;">
                <span class="text-muted small text-uppercase fw-semibold">Toplam Nakliye Tutarı</span>
                <h3 class="fw-bold text-primary mt-1 mb-0"><?= number_format($aylikNakliye, 2, ',', '.') ?> ₺</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 border-0 shadow-sm" style="background: rgba(255, 193, 7, 0.1); border-left: 4px solid #ffc107 !important;">
                <span class="text-muted small text-uppercase fw-semibold">Toplam Sefer Sayısı</span>
                <h3 class="fw-bold text-warning mt-1 mb-0"><?= $toplamSefer ?> Sefer</h3>
            </div>
        </div>
    </div>

    <!-- SEVKİYAT VE KANTAR TABLOSU (KÜÇÜKTEN BÜYÜĞE SIRALI) -->
    <div class="card border-0 shadow-lg p-4" style="background: rgba(20, 20, 20, 0.6); backdrop-filter: blur(10px);">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="fw-bold text-white mb-0">📋 Kantar Fişleri & Sevkiyat Listesi (<?= $secilenAy ?>)</h5>
        </div>

        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0 small" style="background: transparent;">
                <thead>
                    <tr class="text-muted text-uppercase">
                        <th>Fiş No</th>
                        <th>Tarih</th>
                        <th>Gittiği Yer (Firma)</th>
                        <th>Cinsi</th>
                        <th>Ağırlık</th>
                        <th>Kg Fiyat</th>
                        <th>Nakliye</th>
                        <th>G.Top (Tutar)</th>
                        <th>Plaka</th>
                        <th>Durum</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fiş numarasına göre 1'den yukarıya doğru (artan sırada) sıralama
                    $sevkiyatSorgu = $db->prepare("
                        SELECT islemler.*, cariler.firma_adi 
                        FROM islemler 
                        JOIN cariler ON islemler.cari_id = cariler.id 
                        WHERE DATE_FORMAT(islem_tarihi, '%Y-%m') = ? 
                        ORDER BY CAST(fis_no AS UNSIGNED) ASC
                    ");
                    $sevkiyatSorgu->execute([$secilenAy]);
                    $sevkiyatlar = $sevkiyatSorgu->fetchAll(PDO::FETCH_ASSOC);

                    if (count($sevkiyatlar) > 0):
                        foreach ($sevkiyatlar as $sev):
                    ?>
                        <tr>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($sev['fis_no'] ?? '-') ?></span></td>
                            <td><?= htmlspecialchars($sev['islem_tarihi']) ?></td>
                            <td class="fw-semibold text-white"><?= htmlspecialchars($sev['firma_adi']) ?></td>
                            <td><span class="text-info"><?= htmlspecialchars($sev['cinsi'] ?? '-') ?></span></td>
                            <td><?= number_format($sev['agirlik'], 0, ',', '.') ?> kg</td>
                            <td><?= number_format($sev['kg_fiyat'], 3, ',', '.') ?> ₺</td>
                            <td><?= number_format($sev['nakliye'], 2, ',', '.') ?> ₺</td>
                            <td class="fw-bold text-success"><?= number_format($sev['tutar'], 2, ',', '.') ?> ₺</td>
                            <td><span class="badge bg-dark border border-secondary text-light"><?= htmlspecialchars($sev['plaka'] ?? '-') ?></span></td>
                            <td>
                                <?php if($sev['durum'] == 'Ödendi'): ?>
                                    <span class="badge bg-success bg-opacity-25 text-success">Ödendi</span>
                                <?php else: ?>
                                    <span class="badge bg-warning bg-opacity-25 text-warning"><?= htmlspecialchars($sev['durum'] ?? 'Açık') ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php 
                        endforeach;
                    else:
                    ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">Bu döneme ait sevkiyat kaydı bulunamadı. Üstten Excel yükleyebilirsiniz.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Sıfırlama Onay Scripti -->
<script>
function verileriSifirlaOnay() {
    let birinciOnay = confirm("⚠️ DİKKAT: Veritabanındaki tüm sevkiyatlar ve cari kayıtlar kalıcı olarak silinecektir!\n\nEmin misiniz?");
    if (birinciOnay) {
        let ikinciOnay = prompt("Silmeyi onaylamak için büyük harflerle SİL yazın:");
        if (ikinciOnay === "SİL") {
            document.getElementById('sifirlaForm').submit();
        } else {
            alert("İşlem iptal edildi.");
        }
    }
}
</script>

</body>
</html>