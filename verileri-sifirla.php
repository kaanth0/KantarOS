<?php
include 'baglan.php';

// Güvenlik: Sadece POST isteğiyle veya onay verilerek çalıştırılsın
if (isset($_POST['onayli_sifirla']) && $_POST['onayli_sifirla'] == 'evet') {
    try {
        // Veritabanı bütünlüğünü koruyarak tablolardaki tüm verileri temizle
        // Önce bağlı tabloyu (işlemler), sonra ana tabloyu (cariler) boşaltıyoruz
        $db->exec("DELETE FROM islemler");
        $db->exec("DELETE FROM cariler");
        
        // Auto-increment (ID sayaçlarını) 1'den başlatmak için sıfırlayalım
        $db->exec("ALTER TABLE islemler AUTO_INCREMENT = 1");
        $db->exec("ALTER TABLE cariler AUTO_INCREMENT = 1");

        // Başarılı olursa ana panele yönlendir
        header("Location: index.php?durum=sifirlandi");
        exit;
    } catch (PDOException $e) {
        die("Sıfırlama hatası: " . $e->getMessage());
    }
} else {
    // Yetkisiz erişim varsa ana sayfaya at
    header("Location: index.php");
    exit;
}