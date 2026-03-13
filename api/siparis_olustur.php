<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require dirname(__DIR__) . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz metod']);
    exit;
}

if (!isset($_SESSION['musteri_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Lütfen giriş yapın.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$sepet = $input['sepet'] ?? [];

if (empty($sepet)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Sepetiniz boş.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Sipariş ana kaydını oluştur
    $stmt = $pdo->prepare("INSERT INTO siparisler (musteri_id, durum) VALUES (:musteri_id, 'beklemede')");
    $stmt->execute(['musteri_id' => $_SESSION['musteri_id']]);
    $siparis_id = $pdo->lastInsertId();

    // 2. Sipariş detaylarını ekle
    $stmtDetay = $pdo->prepare("INSERT INTO siparis_detaylari (siparis_id, urun_kodu, istenen_kg, kalan_kg) VALUES (:siparis_id, :urun_kodu, :istenen_kg, :kalan_kg)");

    foreach ($sepet as $item) {
        $stmtDetay->execute([
            'siparis_id' => $siparis_id,
            'urun_kodu' => $item['urun_kodu'],
            'istenen_kg' => $item['miktar_kg'],
            'kalan_kg' => $item['miktar_kg']
        ]);
    }

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Siparişiniz başarıyla oluşturuldu.']);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Sipariş oluşturulurken hata oluştu.']);
}
?>