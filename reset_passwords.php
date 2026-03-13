<?php
require 'config.php';

$new_pass = password_hash("123456", PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE musteriler SET sifre = ? WHERE kullanici_adi IS NOT NULL AND kullanici_adi != ''");
$stmt->execute([$new_pass]);

echo "Şifreler '123456' olarak güncellendi.\n";

$stmt = $pdo->query("SELECT kullanici_adi, sifre FROM musteriler WHERE kullanici_adi IS NOT NULL");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>