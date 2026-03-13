<?php
// config.php

// Veritabanı yapılandırma ayarları
$host = 'localhost';
$dbname = 'aksa_toptan_db'; // Gerekirse kendi veritabanı isminizle değiştirin
$username = 'root'; // Kendi kullanıcı adınız
$password = ''; // Kendi şifreniz
$charset = 'utf8mb4';

// Admin Panel Credentials
$admin_username = 'admin';
$admin_password = 'aksa2026';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Hataları exception olarak fırlat
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Verileri associative array olarak çek
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Gerçek prepared statement'lar kullan
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (\PDOException $e) {
    // Üretim ortamında hatayı loglayıp kullanıcıya jenerik bir mesaj göstermek daha güvenlidir.
    // Ancak geliştirme aşamasında doğrudan hatayı yazdırabilirsiniz.
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
