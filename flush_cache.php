<?php
// OPCache varsa temizle
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPCache temizlendi.\n";
} else {
    echo "OPCache aktif değil.\n";
}

// Sonra urun_api.php'yi test et
$url = 'http://localhost/AKSA_TOPTAN/api/urun_api.php';
$json = file_get_contents($url);
$data = json_decode($json, true);
$first = $data[0] ?? [];
echo "İlk ürün: " . json_encode($first);
echo "\nusd_fiyat var mı? " . (isset($first['usd_fiyat']) ? 'EVET - HÂLÂ GÖRÜNÜYor!' : 'HAYIR - Gizlendi, başarılı!');
?>