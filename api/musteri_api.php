<?php
header('Content-Type: application/json; charset=utf-8');

// Veritabanı bağlantısı
require dirname(__DIR__) . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Müşterileri listele veya tek müşteriyi getir
    $id = $_GET['id'] ?? null;
    try {
        if ($id) {
            $stmt = $pdo->prepare("SELECT id, firma_adi, kullanici_adi, telefon, COALESCE(iskonto_orani, 0) as iskonto_orani FROM musteriler WHERE id = ?");
            $stmt->execute([$id]);
            $musteri = $stmt->fetch();
            echo json_encode($musteri);
        } else {
            $stmt = $pdo->query("SELECT id, firma_adi, kullanici_adi, telefon, COALESCE(iskonto_orani, 0) as iskonto_orani FROM musteriler ORDER BY id DESC");
            $musteriler = $stmt->fetchAll();
            echo json_encode($musteriler);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası']);
    }
} elseif ($method === 'POST') {
    // Yeni müşteri ekle
    $input = json_decode(file_get_contents('php://input'), true);

    $firma_adi = $input['firma_adi'] ?? '';
    $kullanici_adi = $input['kullanici_adi'] ?? '';
    $sifre = $input['sifre'] ?? '';
    $telefon = $input['telefon'] ?? '';
    $iskonto_orani = isset($input['iskonto_orani']) ? (float) $input['iskonto_orani'] : 0.00;

    if (empty($firma_adi) || empty($kullanici_adi) || empty($sifre)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Lütfen tüm alanları doldurun.']);
        exit;
    }

    try {
        $hashed_sifre = password_hash($sifre, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO musteriler (firma_adi, kullanici_adi, sifre, telefon, iskonto_orani) VALUES (:firma_adi, :kullanici_adi, :sifre, :telefon, :iskonto_orani)");
        $stmt->execute([
            'firma_adi' => $firma_adi,
            'kullanici_adi' => $kullanici_adi,
            'sifre' => $hashed_sifre,
            'telefon' => $telefon,
            'iskonto_orani' => $iskonto_orani
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Müşteri eklendi']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Kayıt sırasında hata oluştu.']);
    }
} elseif ($method === 'PUT') {
    // Müşteri güncelle
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'ID belirtilmedi.']);
        exit;
    }

    $firma_adi = $input['firma_adi'] ?? '';
    $kullanici_adi = $input['kullanici_adi'] ?? '';
    $sifre = $input['sifre'] ?? '';
    $telefon = $input['telefon'] ?? '';
    $iskonto_orani = isset($input['iskonto_orani']) ? (float) $input['iskonto_orani'] : 0.00;

    try {
        if (!empty($sifre)) {
            $hashed_sifre = password_hash($sifre, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE musteriler SET firma_adi = ?, kullanici_adi = ?, sifre = ?, telefon = ?, iskonto_orani = ? WHERE id = ?");
            $stmt->execute([$firma_adi, $kullanici_adi, $hashed_sifre, $telefon, $iskonto_orani, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE musteriler SET firma_adi = ?, kullanici_adi = ?, telefon = ?, iskonto_orani = ? WHERE id = ?");
            $stmt->execute([$firma_adi, $kullanici_adi, $telefon, $iskonto_orani, $id]);
        }
        echo json_encode(['status' => 'success', 'message' => 'Müşteri güncellendi']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Güncelleme sırasında hata oluştu.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz metod']);
}
?>