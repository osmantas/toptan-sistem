<?php
session_start();
header('Content-Type: application/json');

require dirname(__DIR__) . '/config.php';

if (!isset($_SESSION['musteri_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Oturum bulunamadı. Lütfen giriş yapın.']);
    exit;
}

$musteri_id = $_SESSION['musteri_id'];
$input = json_decode(file_get_contents('php://input'), true);

$siparis_id = $input['siparis_id'] ?? null;

if (!$siparis_id) {
    echo json_encode(['status' => 'error', 'message' => 'Sipariş ID boş olamaz.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Siparişin bu müşteriye ait olduğunu kontrol et
    $stmt = $pdo->prepare("SELECT durum FROM siparisler WHERE id = ? AND musteri_id = ?");
    $stmt->execute([$siparis_id, $musteri_id]);
    $siparis = $stmt->fetch();

    if (!$siparis) {
        throw new Exception("Sipariş bulunamadı veya size ait değil.");
    }
    if ($siparis['durum'] !== 'beklemede') {
        throw new Exception("Sadece 'beklemede' olan siparişler iptal edilebilir.");
    }

    // Tüm detay satırlarını da iptal et
    $detayStmt = $pdo->prepare("UPDATE siparis_detaylari SET durum = 'iptal' WHERE siparis_id = ? AND durum = 'beklemede'");
    $detayStmt->execute([$siparis_id]);

    // Ana siparişi iptal et
    $cancelStmt = $pdo->prepare("UPDATE siparisler SET durum = 'iptal', guncellendi = 1 WHERE id = ?");
    $cancelStmt->execute([$siparis_id]);

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Sipariş başarıyla iptal edildi.']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>