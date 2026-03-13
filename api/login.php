<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require dirname(__DIR__) . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz metod']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$kullanici_adi = $input['kullanici_adi'] ?? '';
$sifre = $input['sifre'] ?? '';

// DEBUG LOG
$log = date('Y-m-d H:i:s') . " - Login denemesi: $kullanici_adi \n";
file_put_contents(dirname(__DIR__) . '/login_debug.log', $log, FILE_APPEND);

if (empty($kullanici_adi) || empty($sifre)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Lütfen kullanıcı adı ve şifrenizi girin.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, firma_adi, sifre FROM musteriler WHERE kullanici_adi = :kullanici_adi");
    $stmt->execute(['kullanici_adi' => $kullanici_adi]);
    $user = $stmt->fetch();

    if (!$user) {
        file_put_contents(dirname(__DIR__) . '/login_debug.log', "Kullanıcı bulunamadı: $kullanici_adi \n", FILE_APPEND);
    }

    if ($user && password_verify($sifre, $user['sifre'])) {
        // Login başarılı, session oluştur
        $_SESSION['musteri_id'] = $user['id'];
        $_SESSION['firma_adi'] = $user['firma_adi'];

        echo json_encode([
            'status' => 'success',
            'message' => 'Giriş başarılı',
            'user' => [
                'id' => $user['id'],
                'firma_adi' => $user['firma_adi']
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Kullanıcı adı veya şifre hatalı.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası.']);
}
?>