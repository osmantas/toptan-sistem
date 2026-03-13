<?php
$html = file_get_contents('admin/index.php');
$lines = explode("\n", $html);
$inScript = false;
foreach($lines as $k => $line) {
    if(strpos($line, '<script') !== false && strpos($line, '</script>') === false) {
        $inScript = true;
        // Keep tag line empty to preserve line count but remove syntax error
        $lines[$k] = '';
        continue;
    }
    if(strpos($line, '</script>') !== false && $inScript) {
        $inScript = false;
        $lines[$k] = '';
        continue;
    }
    if(strpos($line, '<script') !== false && strpos($line, '</script>') !== false) {
        // inline script, simplify by just removing it or keeping it if needed.
        // usually inline scripts are small and not the issue.
        $lines[$k] = '';
        continue;
    }
    
    if(!$inScript) {
        $lines[$k] = '';
    } else {
        // Quick sanitize for PHP echo tags inside JS
        $lines[$k] = preg_replace('/<\?php.*?\?>/i', 'null', $lines[$k]);
        $lines[$k] = preg_replace('/<\?=.*?\?>/i', 'null', $lines[$k]);
    }
}
file_put_contents('test_syntax.js', implode("\n", $lines));
echo "Extraction done.\n";
