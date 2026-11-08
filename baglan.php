<?php
$host = 'localhost';
$dbname = 'finans_otomasyon';
$username = 'root';
$password = ''; // Kendi şifren neyse

try {
    // CHARSET kısmını ekledik ki veritabanı baştan anlaşın
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_general_ci"
    ]);
} catch (PDOException $e) {
    die("Bağlantı hatası: " . $e->getMessage());
}
?>