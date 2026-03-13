<?php
require __DIR__ . '/config.php';

try {
    // Add price snapshot columns to teslimat_detaylari
    $pdo->exec("ALTER TABLE teslimat_detaylari 
        ADD COLUMN IF NOT EXISTS birim_usd_fiyat DECIMAL(10,2) DEFAULT 0 AFTER teslim_edilen_kg,
        ADD COLUMN IF NOT EXISTS iskonto_orani DECIMAL(5,2) DEFAULT 0 AFTER birim_usd_fiyat");
    echo "OK: teslimat_detaylari columns added\n";

    // Ensure teslimatlar has olusturma_tarihi column (some versions may only have tarih)
    $cols = $pdo->query("SHOW COLUMNS FROM teslimatlar")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('olusturma_tarihi', $cols)) {
        $pdo->exec("ALTER TABLE teslimatlar ADD COLUMN olusturma_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER musteri_id");
        echo "OK: olusturma_tarihi column added to teslimatlar\n";
    }
    if (!in_array('durum', $cols)) {
        $pdo->exec("ALTER TABLE teslimatlar ADD COLUMN durum VARCHAR(20) DEFAULT 'kapali' AFTER olusturma_tarihi");
        echo "OK: durum column added to teslimatlar\n";
    }

    echo "Migration complete!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
