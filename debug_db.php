<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>AKSA TOPTAN - Canlı Sunucu Tanı Testi</h1>";
echo "<pre>";

// 1. PHP Sürümü ve Uzantılar
echo "--- ortam Kontrolü ---\n";
echo "PHP Sürümü: " . PHP_VERSION . "\n";
echo "PDO MySQL Uzantısı: " . (extension_loaded('pdo_mysql') ? "AKTİF ✅" : "YÜKLÜ DEĞİL ❌") . "\n";

// 2. Config Dosyası Yükleme
echo "\n--- Config Dosyası Kontrolü ---\n";
if (file_exists('config.php')) {
    echo "config.php dosyası bulundu ✅\n";
    require 'config.php';
} else {
    echo "HATA: config.php dosyası ana dizinde bulunamadı! ❌\n";
    exit;
}

// 3. Veritabanı Bağlantısı (PDO $pdo nesnesi config.php'den gelir)
echo "\n--- Veritabanı Bağlantı Testi ---\n";
if (isset($pdo)) {
    echo "Veritabanı bağlantısı başarılı! ✅\n";

    // 4. Tablo Kontrolleri
    echo "\n--- Tablo Kontrolleri ---\n";
    $tables = ['musteriler', 'urunler', 'siparisler', 'siparis_detaylari'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT 1 FROM $table LIMIT 1");
            echo "Tablo '$table': OK ✅\n";
        } catch (Exception $e) {
            echo "HATA: '$table' tablosu eksik veya erişilemez! ❌ (" . $e->getMessage() . ")\n";
        }
    }

    // 5. Kritik Sütun Kontrolü (eklenen_kg)
    echo "\n--- Sütun Kontrolü ---\n";
    try {
        $pdo->query("SELECT eklenen_kg FROM siparis_detaylari LIMIT 1");
        echo "'eklenen_kg' sütunu mevcut ✅\n";
    } catch (Exception $e) {
        echo "HATA: 'eklenen_kg' sütunu 'siparis_detaylari' tablosunda bulunamadı! ❌\n";
        echo "Lütfen şu SQL komutunu phpMyAdmin üzerinden çalıştırın:\n";
        echo "ALTER TABLE siparis_detaylari ADD COLUMN eklenen_kg DECIMAL(10,2) DEFAULT 0.00 AFTER istenen_kg;\n";
    }

} else {
    echo "HATA: $pdo nesnesi config.php'den yüklenemedi! ❌\n";
}

echo "</pre>";
?>