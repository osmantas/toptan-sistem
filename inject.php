<?php
$indexFile = 'admin/index.php';
$jsFile = 'extracted_functions_utf8.js';

$content = file_get_contents($indexFile);
$newJs = file_get_contents($jsFile);

// Bulunacak başlangıç ve bitiş noktaları
$startMarker = '        // =====================================';
$startMarker2 = '        // TAB 4: TESLİMAT FİŞLERİ';
$endMarker = '        // --- MÜŞTERİ YÖNETİMİ ---';

$startPos = strpos($content, $startMarker2);
if ($startPos !== false) {
    // Geriye giderek ilk markörü bul
    $startPos = strrpos(substr($content, 0, $startPos), $startMarker);
}

$endPos = strpos($content, $endMarker, $startPos);

if ($startPos !== false && $endPos !== false) {
    $length = $endPos - $startPos;

    // Eski bloğu yeni blokla değiştir
    $content = substr_replace($content, "\n" . $newJs . "\n\n", $startPos, $length);
    file_put_contents($indexFile, $content);
    echo "Successfully replaced the TAB 4 section.";
} else {
    echo "Markers not found! startPos: " . ($startPos !== false ? $startPos : 'false') . ", endPos: " . ($endPos !== false ? $endPos : 'false');
}
