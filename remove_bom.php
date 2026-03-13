<?php
function removeBOM($dir)
{
    $files = glob($dir . '/*.php');
    foreach ($files as $file) {
        if (!is_file($file))
            continue;
        $content = file_get_contents($file);
        if (substr($content, 0, 3) == pack('CCC', 0xef, 0xbb, 0xbf)) {
            echo "BOM found in $file. Removing...\n";
            $content = substr($content, 3);
            file_put_contents($file, $content);
        }
    }
}

removeBOM('c:/xampp/htdocs/AKSA_TOPTAN/api');
removeBOM('c:/xampp/htdocs/AKSA_TOPTAN/admin');
removeBOM('c:/xampp/htdocs/AKSA_TOPTAN');
echo "BOM check completed.\n";
