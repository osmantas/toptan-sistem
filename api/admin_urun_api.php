<?php
header('Content-Type: application/json; charset=utf-8');
require dirname(__DIR__) . '/config.php';

// Admin endpoint: fiyatlar dahil tüm bilgiler
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 50;
        $offset = ($page - 1) * $limit;

        $search = $_GET['search'] ?? '';
        $whereSQL = "";
        $bindParams = [];

        if ($search !== '') {
            $search = trim($search);
            $search = preg_replace('/\s+/', ' ', $search);
            $keywords = explode(' ', $search);

            $conditions = [];
            foreach ($keywords as $keyword) {
                $conditions[] = "(urun_kodu LIKE ? OR urun_adi LIKE ?)";
                $bindParams[] = '%' . $keyword . '%';
                $bindParams[] = '%' . $keyword . '%';
            }
            if (!empty($conditions)) {
                $whereSQL = "WHERE " . implode(' AND ', $conditions);
            }
        }

        // Toplam kayıt sayısını bul
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM urunler $whereSQL");
        $countStmt->execute($bindParams);
        $totalCount = $countStmt->fetchColumn();
        $totalPages = ceil($totalCount / $limit);

        // Verileri getir
        $stmt = $pdo->prepare("SELECT id, urun_kodu, urun_adi, usd_fiyat FROM urunler $whereSQL ORDER BY urun_kodu ASC LIMIT ? OFFSET ?");
        $allParams = array_merge($bindParams, [$limit, $offset]);
        $stmt->execute($allParams);

        $data = $stmt->fetchAll();

        echo json_encode([
            'status' => 'success',
            'data' => $data,
            'total_count' => $totalCount,
            'total_pages' => $totalPages,
            'current_page' => $page,
            'limit' => $limit
        ]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
    }
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $urun_kodu = $input['urun_kodu'] ?? '';
    $urun_adi = $input['urun_adi'] ?? '';
    $usd_fiyat = $input['usd_fiyat'] ?? 0;

    if (empty($urun_kodu) || empty($urun_adi) || !is_numeric($usd_fiyat)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Lütfen tüm bilgileri doğru formatta girin.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO urunler (urun_kodu, urun_adi, usd_fiyat) VALUES (:urun_kodu, :urun_adi, :usd_fiyat)");
        $stmt->execute(['urun_kodu' => $urun_kodu, 'urun_adi' => $urun_adi, 'usd_fiyat' => $usd_fiyat]);
        echo json_encode(['status' => 'success', 'message' => 'Ürün eklendi']);
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Bu ürün kodu zaten mevcut.']);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Kayıt sırasında hata oluştu.']);
        }
    }
}
?>