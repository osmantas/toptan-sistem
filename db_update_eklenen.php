<?php
require 'config.php';
try {
    $pdo->exec("ALTER TABLE siparis_detaylari ADD COLUMN eklenen_kg DECIMAL(10,2) DEFAULT 0.00 AFTER istenen_kg");
    echo "SUCCESS";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "SUCCESS"; // Already exists
    } else {
        echo "ERROR: " . $e->getMessage();
    }
}
?>