# Admin Paneli Kurulum Raporu

**Tarih:** 28 Şubat 2026
**Proje:** AKSA_TOPTAN

## İkinci Aşama: Yönetici Paneli ve API Altyapısı

Bu aşama kapsamında aşağıdaki dosya ve dizinler başarılı bir şekilde oluşturulmuştur:

### 1. Frontend: Yönetici Arayüzü
- **Dosya:** `/admin/index.php`
- **Özellikleri:**
  - Modern, şık ve karanlık tema (dark mode) CSS tasarımı kodlandı.
  - Sol tarafta bir menü oluşturularak "Müşteriler", "Ürünler", "Siparişler" ve "Taksim / Teslimatlar" butonları yerleştirildi.
  - Tıklanan sekmenin sayfa yenilenmeden dinamik olarak açılması Vanilla JavaScript (DOM Manipülasyonu) ve **Fetch API** ile sağlandı.
  - Müşteri listesini/eklemesini ve Ürün listesini/eklemesini sarmalayan HTML formları ile veri tabloları eklendi.

### 2. Backend: API Dosyaları
Veritabanına (InnoDB ve UTF8MB4 formatlı) asenkron istek atabilmek için iki yeni uç nokta (endpoint) oluşturuldu:

- **Dosya:** `/api/musteri_api.php`
  - `GET` metodu ile veritabanından tüm müşterileri liste halinde çeker.
  - `POST` metodu ile gönderilen JSON verilerini (`firma_adi`, `telefon`) PDO kullanarak güvenlice `musteriler` tablosuna kaydeder.

- **Dosya:** `/api/urun_api.php`
  - `GET` metodu ile veritabanındaki kayıtlı parfümleri / ürünleri alfabetik sırayla listeler.
  - `POST` metodu ile kullanıcıdan gelen JSON verilerini (`urun_kodu`, `urun_adi`, `usd_fiyat`) alıp `urunler` tablosuna kaydeder.
  - *Kontrol Mekanizması:* Aynı `urun_kodu` girilmeye çalışılırsa MySQL 1062 (Duplicate Entry) hatasını yakalar ve kullanıcıya uyarı gösterir.

## Test Adımları
Şu andan itibaren XAMPP çalışırken tarayıcınızda `http://localhost/AKSA_TOPTAN/admin/` (veya dosyaları koyduğunuz klasör ismine göre örneğin `http://localhost/admin/`) adresine giderek:
1. Açılan ekranda Aksa Esans için birkaç müşteri ekleyebilirsiniz.
2. Sol menüden "Ürünler" sekmesine tıklayıp "A-01 Aventios" benzeri deneme ürünlerini kaydedebilirsiniz.
3. Eklenen verilerin sayfada tablolara anlık ve sorunsuz düştüğünü görebilirsiniz.

Hazırlanan altyapı ileride sipariş verme ve havuz bölümüne geçmek için hazırdır. İnceleyip test edebilirsiniz. İşlem başarıyla tamamlandı!
