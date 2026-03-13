<?php
// Mock a login request to api/login.php
$_SERVER['REQUEST_METHOD'] = 'POST';
$input = json_encode(['kullanici_adi' => 'mega', 'sifre' => '123456']);

// We need to mock php://input
// In a standalone script we can't easily mock php://input for another file's include, 
// but we can test the logic by including it with a custom input reader or just trusting the password_verify test we did.

// Let's do a more direct test of login.php logic
require 'config.php';
$kullanici_adi = 'mega';
$sifre = '123456';

$stmt = $pdo->prepare("SELECT id, firma_adi, sifre FROM musteriler WHERE kullanici_adi = :kullanici_adi");
$stmt->execute(['kullanici_adi' => $kullanici_adi]);
$user = $stmt->fetch();

if ($user && password_verify($sifre, $user['sifre'])) {
    echo "Login logic works for mega\n";
} else {
    echo "Login logic FAILS for mega\n";
}
?>