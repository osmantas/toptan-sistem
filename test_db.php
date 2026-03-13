<?php
require 'config.php';
$stmt = $pdo->query('SHOW COLUMNS FROM siparisler');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));