<?php
require 'config.php';
try {
    $pdo->exec("ALTER TABLE siparisler ADD COLUMN guncellendi TINYINT(1) DEFAULT 0;");
    echo "Sutun eklendi.\n";
} catch (PDOException $e) {
    if ($e->getCode() == '42S21') { // Duplicate column
        echo "Sutun zaten var.\n";
    } else {
        echo "Hata: " . $e->getMessage() . "\n";
    }
}
?>