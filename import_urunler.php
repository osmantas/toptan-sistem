<?php
/**
 * SONUC.csv dosyasından ürünleri veritabanına aktar (detaylı rapor)
 * Sütunlar: KOD, TYPE (ürün adı), FİYAT (USD)
 */
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/config.php';

$csvFile = __DIR__ . '/SONUC.csv';
if (!file_exists($csvFile)) {
    die("❌ SONUC.csv bulunamadı!");
}

$handle = fopen($csvFile, 'r');
if (!$handle)
    die("❌ CSV dosyası açılamadı!");

// Başlık satırını atla
$header = fgetcsv($handle);

// Önce mevcut ürünleri temizle
$pdo->exec("DELETE FROM urunler");

$insert = $pdo->prepare("INSERT INTO urunler (urun_kodu, urun_adi, usd_fiyat) VALUES (:kod, :ad, :fiyat) ON DUPLICATE KEY UPDATE urun_adi = :ad2, usd_fiyat = :fiyat2");

$count = 0;
$errors = 0;
$skipped = 0;
$lineNum = 1;
$errorDetails = [];
$skipDetails = [];

while (($row = fgetcsv($handle)) !== false) {
    $lineNum++;
    if (count($row) < 3) {
        $skipped++;
        $skipDetails[] = "Satır {$lineNum}: Yetersiz sütun (" . count($row) . ")";
        continue;
    }

    $kod = trim($row[0]);
    $ad = trim($row[1]);
    $fiyat = floatval($row[2]);

    if (empty($kod)) {
        $skipped++;
        $skipDetails[] = "Satır {$lineNum}: Boş ürün kodu";
        continue;
    }
    if (empty($ad)) {
        $ad = "-"; // İsim boşsa tire koy
    }

    try {
        $insert->execute([
            'kod' => $kod,
            'ad' => $ad,
            'fiyat' => $fiyat,
            'ad2' => $ad,
            'fiyat2' => $fiyat
        ]);
        $count++;
    } catch (PDOException $e) {
        $errors++;
        $errorDetails[] = "Satır {$lineNum} (kod: {$kod}): " . $e->getMessage();
    }
}
fclose($handle);

// DB'deki toplam ürün sayısını kontrol et
$dbCount = $pdo->query("SELECT COUNT(*) FROM urunler")->fetchColumn();

echo "<h2>✅ Ürün Aktarımı Tamamlandı!</h2>";
echo "<p>CSV toplam veri satırı: <strong>" . ($lineNum - 1) . "</strong></p>";
echo "<p>Başarıyla işlenen: <strong style='color:green;'>{$count}</strong></p>";
echo "<p>Atlanan (boş/eksik): <strong style='color:orange;'>{$skipped}</strong></p>";
echo "<p>Hata: <strong style='color:red;'>{$errors}</strong></p>";
echo "<p>Veritabanındaki tekil ürün sayısı: <strong style='color:blue;'>{$dbCount}</strong></p>";

if (!empty($skipDetails)) {
    echo "<h3>Atlanan Satırlar (ilk 30):</h3><ul>";
    foreach (array_slice($skipDetails, 0, 30) as $s)
        echo "<li>{$s}</li>";
    if (count($skipDetails) > 30)
        echo "<li>... ve " . (count($skipDetails) - 30) . " satır daha</li>";
    echo "</ul>";
}
if (!empty($errorDetails)) {
    echo "<h3>Hatalar (ilk 30):</h3><ul>";
    foreach (array_slice($errorDetails, 0, 30) as $e)
        echo "<li>" . htmlspecialchars($e) . "</li>";
    if (count($errorDetails) > 30)
        echo "<li>... ve " . (count($errorDetails) - 30) . " hata daha</li>";
    echo "</ul>";
}
echo "<p style='color:red;'>⚠️ Bu dosyayı çalıştırdıktan sonra silebilirsiniz.</p>";
