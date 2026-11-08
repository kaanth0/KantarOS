# 🚛 KantarOS (Logistics & Scale Management System)

> **Mevcut Sürüm:** v1.0 (Stable / Kararlı Temel Sürüm)

KantarOS, lojistik ve hafriyat sektöründeki çoklu ay/şube bazlı Excel tabanlı kantar takip sistemlerini modern, güvenli ve hızlı bir web tabanlı SaaS deneyimine dönüştüren açık kaynaklı bir finansal takip ve operasyon paneli projesidir.

---

## 🚀 v1.0 Sürüm Özellikleri (Mevcut Kararlı Yapı)

Mevcut karmaşık Excel föylerini modernize etmek ve veri yönetimini hızlandırmak amacıyla geliştirilen **v1.0** sürümünün öne çıkan yetenekleri:
- **Çoklu Sekme Excel Ayrıştırma Motoru:** Yıllık veya aylık kantar takip dosyalarındaki birden fazla çalışma sayfasını (Ocak, Şubat vb.) otomatik olarak tarar.
- **Shared & Inline Strings Desteği:** Excel XML yapısındaki tüm metin ve sayı tiplerini kayıpsız bir şekilde ayrıştırır.
- **Akıllı Filtreleme & Mükerrer Kontrolü:** Tarihsiz satırları, kasa özetlerini ve mükerrer (aynı fiş no ve tarih) kayıtları otomatik olarak filtreleyerek veri bütünlüğünü korur.
- **Modern SaaS Arayüzü:** Bootstrap 5 tabanlı özel **"Black Edition"** koyu tema ile göz yormayan, şık ve mobil uyumlu kontrol paneli.
- **Cari Yönetimi:** Müşteri/şantiye bazlı cari kartları otomatik oluşturur ve ilişkilendirir.

---

## 🛠️ Kullanılan Teknolojiler

* **Backend:** PHP (OOP & PDO)
* **Veritabanı:** MySQL / MariaDB
* **Frontend:** Bootstrap 5, HTML5, CSS3, Google Fonts (Inter)
* **Dosya İşleme:** PHP ZipArchive & SimpleXML (Harici ağır kütüphane gerektirmeyen native çözüm)

---

## ⚙️ Kurulum Rehberi

Projeyi yerel ortamınızda (XAMPP, WAMP veya MAMP) çalıştırmak için şu adımları izleyebilirsiniz:

1. Depoyu klonlayın veya ZIP olarak indirin:
   ```bash
   git clone [https://github.com/kaanth0/KantarOS.git](https://github.com/kaanth0/KantarOS.git)
