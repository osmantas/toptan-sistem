<?php
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/config.php';

try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    // Teslimat detayları
    $c1 = $pdo->exec("DELETE FROM teslimat_detaylari");
    // Teslimatlar (arşiv fişleri)
    $c2 = $pdo->exec("DELETE FROM teslimatlar");
    // Sipariş detayları
    $c3 = $pdo->exec("DELETE FROM siparis_detaylari");
    // Siparişler
    $c4 = $pdo->exec("DELETE FROM siparisler");

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "<h2>✅ Tüm siparişler ve teslimat arşivi silindi!</h2>";
    echo "<ul>";
    echo "<li>Teslimat detayları: <strong>{$c1}</strong> kayıt silindi</li>";
    echo "<li>Teslimatlar (arşiv): <strong>{$c2}</strong> kayıt silindi</li>";
    echo "<li>Sipariş detayları: <strong>{$c3}</strong> kayıt silindi</li>";
    echo "<li>Siparişler: <strong>{$c4}</strong> kayıt silindi</li>";
    echo "</ul>";
    echo "<p style='color:red;'>⚠️ Bu dosyayı çalıştırdıktan sonra silebilirsiniz.</p>";

} catch (PDOException $e) {
    echo "<h2>❌ Hata</h2><p>" . $e->getMessage() . "</p>";
}
