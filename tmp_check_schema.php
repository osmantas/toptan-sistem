<?php
require 'config.php';
$stmt = $pdo->query('DESCRIBE siparis_detaylari');
$schema = $stmt->fetchAll(PDO::FETCH_ASSOC);
file_put_contents('tmp_schema_full.json', json_encode($schema, JSON_PRETTY_PRINT));
echo "Schema written to tmp_schema_full.json\n";
