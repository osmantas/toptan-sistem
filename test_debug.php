<?php
require __DIR__ . '/config.php';

echo "=== SQL MODE ===\n";
echo $pdo->query('SELECT @@sql_mode')->fetchColumn() . "\n\n";

echo "=== siparis_detaylari STRUCTURE ===\n";
$cols = $pdo->query('DESCRIBE siparis_detaylari')->fetchAll();
foreach ($cols as $c) {
    echo $c['Field'] . ' | ' . $c['Type'] . ' | ' . $c['Null'] . ' | ' . $c['Default'] . "\n";
}

echo "\n=== teslimatlar STRUCTURE ===\n";
$cols = $pdo->query('DESCRIBE teslimatlar')->fetchAll();
foreach ($cols as $c) {
    echo $c['Field'] . ' | ' . $c['Type'] . ' | ' . $c['Null'] . ' | ' . $c['Default'] . "\n";
}

echo "\n=== teslimat_detaylari STRUCTURE ===\n";
$cols = $pdo->query('DESCRIBE teslimat_detaylari')->fetchAll();
foreach ($cols as $c) {
    echo $c['Field'] . ' | ' . $c['Type'] . ' | ' . $c['Null'] . ' | ' . $c['Default'] . "\n";
}

echo "\n=== musteriler STRUCTURE ===\n";
$cols = $pdo->query('DESCRIBE musteriler')->fetchAll();
foreach ($cols as $c) {
    echo $c['Field'] . ' | ' . $c['Type'] . ' | ' . $c['Null'] . ' | ' . $c['Default'] . "\n";
}

echo "\n=== TEST teslim_edilenler QUERY ===\n";
try {
    $sql = "SELECT d.id as detay_id, d.siparis_id, d.urun_kodu, u.urun_adi,
                d.istenen_kg, d.teslim_edilen_kg, d.durum,
                s.tarih, m.id as musteri_id, m.firma_adi, m.telefon,
                u.usd_fiyat, COALESCE(m.iskonto_orani, 0) as iskonto_orani
            FROM siparis_detaylari d
            JOIN siparisler s ON d.siparis_id = s.id
            JOIN musteriler m ON s.musteri_id = m.id
            JOIN urunler u ON d.urun_kodu = u.urun_kodu
            WHERE d.durum = 'tamamlandi' AND d.teslimat_id IS NULL
            ORDER BY s.tarih DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll();
    echo "Rows: " . count($rows) . "\n";
    if (count($rows) > 0)
        echo "First: " . json_encode($rows[0]) . "\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== TEST teslimat_arsiv QUERY ===\n";
try {
    $sql = "SELECT t.id as fis_id, t.musteri_id, m.firma_adi, m.telefon,
                t.olusturma_tarihi, t.usd_kuru, t.toplam_usd, t.toplam_tl,
                COALESCE(m.iskonto_orani, 0) as iskonto_orani,
                SUM(td.teslim_edilen_kg) as toplam_kg,
                COUNT(td.id) as urun_sayisi
            FROM teslimatlar t
            JOIN musteriler m ON t.musteri_id = m.id
            LEFT JOIN teslimat_detaylari td ON td.teslimat_id = t.id
            WHERE 1=1
            GROUP BY t.id, t.musteri_id, m.firma_adi, m.telefon, t.olusturma_tarihi, t.usd_kuru, t.toplam_usd, t.toplam_tl
            ORDER BY t.olusturma_tarihi DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll();
    echo "Rows: " . count($rows) . "\n";
    if (count($rows) > 0)
        echo "First: " . json_encode($rows[0]) . "\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
