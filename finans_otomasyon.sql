-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 08 Kas 2026, 21:48:04
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `finans_otomasyon`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `cariler`
--

CREATE TABLE `cariler` (
  `id` int(11) NOT NULL,
  `firma_adi` varchar(150) NOT NULL,
  `telefon` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `cariler`
--

INSERT INTO `cariler` (`id`, `firma_adi`, `telefon`, `created_at`) VALUES
(1, 'Arpacı İnşaat', NULL, '2026-11-08 20:44:17'),
(2, 'Güçar İnş.', NULL, '2026-11-08 20:44:17'),
(3, 'Kastamonu Belediyesi', NULL, '2026-11-08 20:44:17'),
(4, 'D.S.İ', NULL, '2026-11-08 20:44:17'),
(5, 'Celepciler Parke', NULL, '2026-11-08 20:44:17');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `islemler`
--

CREATE TABLE `islemler` (
  `id` int(11) NOT NULL,
  `cari_id` int(11) DEFAULT NULL,
  `tutar` decimal(10,2) NOT NULL,
  `islem_tipi` enum('Gelir','Gider') NOT NULL,
  `aciklama` text DEFAULT NULL,
  `islem_tarihi` date DEFAULT NULL,
  `fis_no` varchar(50) DEFAULT NULL,
  `cinsi` varchar(100) DEFAULT NULL,
  `agirlik` decimal(10,2) DEFAULT 0.00,
  `kg_fiyat` decimal(10,3) DEFAULT 0.000,
  `nakliye` decimal(10,2) DEFAULT 0.00,
  `durum` varchar(50) DEFAULT NULL,
  `plaka` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `islemler`
--

INSERT INTO `islemler` (`id`, `cari_id`, `tutar`, `islem_tipi`, `aciklama`, `islem_tarihi`, `fis_no`, `cinsi`, `agirlik`, `kg_fiyat`, `nakliye`, `durum`, `plaka`) VALUES
(1, 1, 17509.44, 'Gelir', NULL, '2026-01-01', '1', '05Kum', 18239.00, 0.960, 0.00, 'Açık', '37AEH287'),
(2, 2, 17785.41, 'Gelir', NULL, '2026-01-03', '2', '2-5 Tuzlama', 20443.00, 0.870, 0.00, 'Fatura Kesildi', '37ADF846'),
(3, 3, 13468.34, 'Gelir', NULL, '2026-01-05', '3', '05Kum', 20102.00, 0.670, 0.00, 'Fatura Kesildi', '37AEH287'),
(4, 1, 17346.96, 'Gelir', NULL, '2026-01-07', '4', 'Mıcır', 21416.00, 0.810, 0.00, 'Açık', '37ADF846'),
(5, 4, 29330.73, 'Gelir', NULL, '2026-01-09', '5', '05Kum', 29627.00, 0.990, 0.00, 'Açık', '37AEG873'),
(6, 2, 22508.10, 'Gelir', NULL, '2026-01-11', '6', 'Mıcır', 25290.00, 0.890, 0.00, 'Ödendi', '37AEH287'),
(7, 1, 12816.06, 'Gelir', NULL, '2026-01-13', '7', '05Kum', 18574.00, 0.690, 0.00, 'Ödendi', '37ADF846'),
(8, 3, 31827.12, 'Gelir', NULL, '2026-01-15', '8', 'Beton Kumu', 31512.00, 1.010, 0.00, 'Açık', '37AEG873'),
(9, 1, 16923.40, 'Gelir', NULL, '2026-01-17', '9', '05Kum', 26036.00, 0.650, 0.00, 'Ödendi', '37ADF846'),
(10, 5, 20102.84, 'Gelir', NULL, '2026-01-19', '10', '05Kum', 27166.00, 0.740, 0.00, 'Fatura Kesildi', '37AEH287'),
(11, 1, 31494.87, 'Gelir', NULL, '2026-02-01', '11', '2-5 Tuzlama', 31813.00, 0.990, 0.00, 'Ödendi', '37AEG873'),
(12, 3, 11745.55, 'Gelir', NULL, '2026-02-03', '12', '05Kum', 19255.00, 0.610, 0.00, 'Ödendi', '37ADF846'),
(13, 4, 27174.16, 'Gelir', NULL, '2026-02-05', '13', '2-5 Tuzlama', 25636.00, 1.060, 0.00, 'Ödendi', '37ADF846'),
(14, 1, 17133.93, 'Gelir', NULL, '2026-02-07', '14', '05Kum', 21153.00, 0.810, 0.00, 'Ödendi', '37AEH287'),
(15, 2, 18841.83, 'Gelir', NULL, '2026-02-09', '15', '2-5 Tuzlama', 27307.00, 0.690, 0.00, 'Fatura Kesildi', '37ADF846'),
(16, 1, 18620.00, 'Gelir', NULL, '2026-02-11', '16', '05Kum', 26600.00, 0.700, 0.00, 'Fatura Kesildi', '37AEH287'),
(17, 5, 29606.32, 'Gelir', NULL, '2026-02-13', '17', 'Mıcır', 28744.00, 1.030, 0.00, 'Açık', '37AEG873'),
(18, 3, 15472.04, 'Gelir', NULL, '2026-02-15', '18', '05Kum', 22753.00, 0.680, 0.00, 'Fatura Kesildi', '37AEG873'),
(19, 2, 19156.50, 'Gelir', NULL, '2026-02-17', '19', '2-5 Tuzlama', 19350.00, 0.990, 0.00, 'Ödendi', '37AEH287'),
(20, 3, 26015.64, 'Gelir', NULL, '2026-02-19', '20', 'Mıcır', 30971.00, 0.840, 0.00, 'Ödendi', '37AEH287');

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `cariler`
--
ALTER TABLE `cariler`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `islemler`
--
ALTER TABLE `islemler`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cari_id` (`cari_id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `cariler`
--
ALTER TABLE `cariler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Tablo için AUTO_INCREMENT değeri `islemler`
--
ALTER TABLE `islemler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `islemler`
--
ALTER TABLE `islemler`
  ADD CONSTRAINT `islemler_ibfk_1` FOREIGN KEY (`cari_id`) REFERENCES `cariler` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
