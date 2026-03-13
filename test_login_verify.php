<?php
// Test login verify manually
$pass = "123456"; // Varsayılan şifre ne koyulmuştu hatırlayalım
$hash = '$2y$10$ALQXqwUMx1Wv7OEo8kWUIeumTDYetyIKGZMH1EJoeS05ZzTIe3NQW'; // mega

if (password_verify($pass, $hash)) {
    echo "Mega login OK\n";
} else {
    echo "Mega login FAILED\n";
}

// star için de deneyelim (muhtemelen aynı şifre)
$hash_star = '$2y$10$uL2RQWsCjlTjOn3HB11di.yoUjn4GZCSp2ksyITAUY3dScpAlSbdm';
if (password_verify($pass, $hash_star)) {
    echo "Star login OK\n";
} else {
    echo "Star login FAILED\n";
}
?>