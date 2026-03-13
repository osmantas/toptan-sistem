<?php
/**
 * SONUC.xlsx dosyasını oku ve içeriğini göster
 * (Excel COM gerektirmez - doğrudan ZIP/XML okuma)
 */
header('Content-Type: text/html; charset=utf-8');

$file = __DIR__ . '/SONUC.xlsx';
if (!file_exists($file)) {
    die("❌ SONUC.xlsx bulunamadı!");
}

$zip = new ZipArchive();
if ($zip->open($file) !== true) {
    die("❌ Dosya açılamadı!");
}

// Shared strings (hücrelerdeki metin değerleri burada saklanır)
$sharedStrings = [];
$ssXml = $zip->getFromName('xl/sharedStrings.xml');
if ($ssXml) {
    $ss = new SimpleXMLElement($ssXml);
    foreach ($ss->si as $si) {
        // Bazı hücrelerde <r> (rich text) alt elemanları olabiliyor
        $text = '';
        if (isset($si->t)) {
            $text = (string) $si->t;
        } elseif (isset($si->r)) {
            foreach ($si->r as $r) {
                $text .= (string) $r->t;
            }
        }
        $sharedStrings[] = $text;
    }
}

// Sheet1 verisini oku
$sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
if (!$sheetXml) {
    $zip->close();
    die("❌ Sheet1 bulunamadı!");
}

$sheet = new SimpleXMLElement($sheetXml);
$rows = [];

foreach ($sheet->sheetData->row as $row) {
    $rowData = [];
    foreach ($row->c as $cell) {
        $value = '';
        $type = (string) $cell['t']; // s = shared string, n = number, vs.

        if ($type === 's') {
            $idx = intval((string) $cell->v);
            $value = $sharedStrings[$idx] ?? '';
        } elseif (isset($cell->v)) {
            $value = (string) $cell->v;
        }
        $rowData[] = $value;
    }
    $rows[] = $rowData;
}
$zip->close();

// Göster
echo "<h2>📊 SONUC.xlsx İçeriği (" . count($rows) . " satır)</h2>";
echo "<table border='1' cellpadding='6' style='border-collapse:collapse; font-family:monospace;'>";
foreach ($rows as $i => $row) {
    $tag = $i === 0 ? 'th' : 'td';
    $bg = $i === 0 ? " style='background:#333;color:#fff;'" : '';
    echo "<tr{$bg}>";
    foreach ($row as $cell) {
        echo "<{$tag}>" . htmlspecialchars($cell) . "</{$tag}>";
    }
    echo "</tr>";
}
echo "</table>";
