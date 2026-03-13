<?php
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/config.php';

try {
    // Önce mevcut ürün sayısını göster
    $count = $pdo->query("SELECT COUNT(*) FROM urunler")->fetchColumn();

    if ($count == 0) {
        echo "<h2>✅ Ürün tablosu zaten boş.</h2>";
        exit;
    }

    // Tüm ürünleri sil
    $pdo->exec("DELETE FROM urunler");

    echo "<h2>✅ Tüm ürünler silindi!</h2>";
    echo "<p><strong>{$count}</strong> adet ürün başarıyla kaldırıldı.</p>";
    echo "<p>Artık admin panelinden yeni ürünleri yükleyebilirsiniz.</p>";
    echo "<p style='color:red;'>⚠️ Bu dosyayı çalıştırdıktan sonra silebilirsiniz.</p>";

} catch (PDOException $e) {
    echo "<h2>❌ Hata oluştu</h2>";
    echo "<p>" . $e->getMessage() . "</p>";

    // Eğer foreign key hatası varsa alternatif çözüm öner
    if (strpos($e->getMessage(), 'foreign key') !== false || strpos($e->getMessage(), 'FOREIGN KEY') !== false) {
        echo "<p>Sipariş detaylarında bu ürünlere referans var. Zorla silmek isterseniz sayfayı <code>?force=1</code> ile açın.</p>";

        if (isset($_GET['force'])) {
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
            $pdo->exec("DELETE FROM urunler");
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            echo "<h2>✅ Tüm ürünler zorla silindi!</h2>";
        }
    }
}
