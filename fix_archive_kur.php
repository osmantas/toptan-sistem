<?php
/**
 * Tek seferlik düzeltme scripti:
 * teslimatlar tablosundaki usd_kuru = 1 olan kayıtları 
 * güncel USD kuru ile günceller ve toplam_tl'yi yeniden hesaplar.
 */
header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/config.php';

// 1) Güncel kuru al
$kur = 0;
try {
    $json = @file_get_contents('https://finans.truncgil.com/v4/today.json');
    if ($json) {
        $data = json_decode($json, true);
        if (isset($data['USD']['Satış'])) {
            $kur = floatval(str_replace(',', '.', $data['USD']['Satış']));
        }
    }
} catch (Exception $e) {
}

if ($kur <= 0) {
    // Yedek API
    try {
        $json2 = @file_get_contents('https://api.exchangerate-api.com/v4/latest/USD');
        if ($json2) {
            $data2 = json_decode($json2, true);
            if (isset($data2['rates']['TRY'])) {
                $kur = floatval($data2['rates']['TRY']);
            }
        }
    } catch (Exception $e) {
    }
}

if ($kur <= 0) {
    die("❌ USD kuru alınamadı. Lütfen internet bağlantınızı kontrol edin.");
}

echo "<h2>USD Kuru: {$kur} ₺</h2>";

// 2) usd_kuru = 1 olan (hatalı) kayıtları bul
$stmt = $pdo->query("SELECT id, toplam_usd, usd_kuru, toplam_tl FROM teslimatlar WHERE usd_kuru <= 1");
$rows = $stmt->fetchAll();

if (empty($rows)) {
    echo "<p>✅ Güncellenecek kayıt bulunamadı. Tüm fişlerin kuru doğru.</p>";
    exit;
}

echo "<p>📝 Güncellenecek kayıt sayısı: <strong>" . count($rows) . "</strong></p>";
echo "<table border='1' cellpadding='8' style='border-collapse:collapse;'>";
echo "<tr><th>Fiş ID</th><th>Toplam USD</th><th>Eski Kur</th><th>Eski TL</th><th>Yeni Kur</th><th>Yeni TL</th></tr>";

$update = $pdo->prepare("UPDATE teslimatlar SET usd_kuru = ?, toplam_tl = ? WHERE id = ?");
$count = 0;

foreach ($rows as $r) {
    $yeniTl = round(floatval($r['toplam_usd']) * $kur, 2);
    $update->execute([$kur, $yeniTl, $r['id']]);
    $count++;

    echo "<tr>";
    echo "<td>{$r['id']}</td>";
    echo "<td>$ {$r['toplam_usd']}</td>";
    echo "<td>{$r['usd_kuru']}</td>";
    echo "<td>₺ {$r['toplam_tl']}</td>";
    echo "<td style='color:green;font-weight:bold;'>{$kur}</td>";
    echo "<td style='color:green;font-weight:bold;'>₺ {$yeniTl}</td>";
    echo "</tr>";
}

echo "</table>";
echo "<p>✅ <strong>{$count}</strong> kayıt başarıyla güncellendi!</p>";
echo "<p style='color:red;'>⚠️ Bu dosyayı çalıştırdıktan sonra silebilirsiniz.</p>";
