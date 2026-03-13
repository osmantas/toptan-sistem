# Toptan Parfüm Özü Sipariş ve Taksim Sistemi - Proje Kuralları

## 1. Proje Özeti
B2B toptan parfüm özü satışı için sipariş toplama, havuz oluşturma, anatoptancıdan tedarik etme ve müşterilere (kısmi teslimat dahil) taksim edip dolar kuruna göre teslimat fişi kesme sistemi.

## 2. Teknoloji Yığını
* **Frontend:** HTML5, CSS, Vanilla JavaScript (Ekstra framework kullanılmayacak).
* **Backend:** PHP (Veritabanı işlemleri için kesinlikle PDO kullanılacak).
* **Veritabanı:** MySQL (İlişkisel yapı).
* **Döviz Kuru:** TCMB XML servisinden anlık USD/TRY kuru çekilecek.

## 3. Klasör Yapısı (Kesin Kural)
Yetkisiz erişimi engellemek için yapı kesin olarak ayrılmalıdır:
* `/admin` (Yönetici arayüzü: Havuz, taksim, fiş kesme ekranları)
* `/client` (Müşteri arayüzü: Sipariş verme ekranı)
* `/api` (Tüm PHP backend işlemleri ve TCMB kur fonksiyonları)
* `/db` (Veritabanı şema ve yedek dosyaları)

## 4. Veritabanı Mimarisi (Temel İskelet)
* `musteriler`: id, firma_adi, telefon
* `urunler`: id, urun_kodu (benzersiz), urun_adi, usd_fiyat
* `siparisler`: id, musteri_id, tarih, durum
* `siparis_detaylari`: id, siparis_id, urun_kodu, istenen_kg, teslim_edilen_kg, kalan_kg
* `teslimatlar` (Fişler): id, musteri_id, tarih, usd_kuru, toplam_usd, toplam_tl
* `teslimat_detaylari`: id, teslimat_id, urun_kodu, teslim_edilen_kg

## 5. İş Akışı ve Geliştirici Kuralları
* **Havuz Mantığı:** Anatoptancı sipariş listesi, `siparis_detaylari` tablosundaki aynı `urun_kodu`na sahip `kalan_kg` değerlerinin toplanmasıyla (SUM) oluşturulmalıdır.
* **Kısmi Teslimat (Taksim):** Gelen ürünler müşterilere paylaştırıldığında `teslim_edilen_kg` güncellenmeli ve bu miktar kadar yeni bir teslimat fişi oluşturulmalıdır.
* **Kur ve Yazdırma:** Fiş kesildiği an güncel USD kuru TCMB'den çekilip `teslimatlar` tablosuna sabitlenmeli ve yazdırılabilir (print-friendly) HTML5 fiş sayfası sunulmalıdır.
* **Ajan Sınırları:** Sadece sana verilen göreve ait klasörde çalış. İşi bitirdiğinde ne yaptığını `.md` dosyası olarak raporla.
## 6. Geliştirme ve Sunucu Ortamı (Paylaşımlı Hosting)
* Proje, SSH/Terminal erişimi olmayan standart bir paylaşımlı hosting (cPanel vb.) ortamında çalışacaktır.
* Sunucuya yükleme sadece FTP üzerinden yapılacağı için, Composer gibi paket yöneticilerine bağımlı karmaşık kurulumlar veya terminal komutları gerektiren yapılar kullanılmayacaktır. Tüm sistem "Sürükle-Bırak" mantığıyla çalışmalıdır.
* Veritabanı kurulumu phpMyAdmin'den manuel yapılacaktır. Bu nedenle veritabanı tablolarını ve ilişkilerini içeren eksiksiz bir `/db/init.sql` dosyası oluşturulmalıdır.
* Veritabanı bağlantı bilgileri (Host, User, Pass, DB Name) kök dizindeki `config.php` dosyasından çekilecektir. Sayfalara doğrudan yazılmayacaktır.