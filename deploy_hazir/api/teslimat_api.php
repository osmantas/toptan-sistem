<?php
header('Content-Type: application/json; charset=utf-8');
require dirname(__DIR__) . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? '';

    if ($action === 'bekleyen_urunler') {
        // Hangi üründen toplam ne kadar bekleyen sipariş var?
        try {
            $stmt = $pdo->query("SELECT d.urun_kodu, u.urun_adi, SUM(d.kalan_kg) as toplam_bekleyen_kg
                FROM siparis_detaylari d
                JOIN urunler u ON d.urun_kodu = u.urun_kodu
                WHERE d.kalan_kg > 0
                GROUP BY d.urun_kodu, u.urun_adi
                ORDER BY u.urun_adi ASC");
            echo json_encode($stmt->fetchAll());
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    } elseif ($action === 'bekleyen_musteriler' && isset($_GET['urun_kodu'])) {
        // Seçili ürünü bekleyen siparişler/müşteriler
        try {
            $stmt = $pdo->prepare("SELECT d.id as detay_id, s.id as siparis_id, s.tarih, m.id as musteri_id, m.firma_adi, d.kalan_kg, d.istenen_kg, d.teslim_edilen_kg
                FROM siparis_detaylari d
                JOIN siparisler s ON d.siparis_id = s.id
                JOIN musteriler m ON s.musteri_id = m.id
                WHERE d.urun_kodu = :urun_kodu AND d.kalan_kg > 0
                ORDER BY s.tarih ASC");
            $stmt->execute(['urun_kodu' => $_GET['urun_kodu']]);
            echo json_encode($stmt->fetchAll());
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    } elseif ($action === 'liste') {
        // Tüm teslimatları (fişleri) listele
        try {
            $stmt = $pdo->query("SELECT t.*, m.firma_adi 
                FROM teslimatlar t 
                JOIN musteriler m ON t.musteri_id = m.id 
                ORDER BY t.tarih DESC");
            echo json_encode($stmt->fetchAll());
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    } elseif ($action === 'detay' && isset($_GET['id'])) {
        // Belirli bir teslimatın (fişin) içeriği
        try {
            $stmt = $pdo->prepare("SELECT d.*, u.urun_adi, u.usd_fiyat 
                FROM teslimat_detaylari d 
                JOIN urunler u ON d.urun_kodu = u.urun_kodu 
                WHERE d.teslimat_id = ?");
            $stmt->execute([$_GET['id']]);
            $detaylar = $stmt->fetchAll();

            $stmt_t = $pdo->prepare("SELECT t.*, m.firma_adi, m.telefon FROM teslimatlar t JOIN musteriler m ON t.musteri_id = m.id WHERE t.id = ?");
            $stmt_t->execute([$_GET['id']]);
            $teslimat = $stmt_t->fetch();

            echo json_encode(['teslimat' => $teslimat, 'detaylar' => $detaylar]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
} elseif ($method === 'POST') {
    // Dağıtımı kaydet
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['action']) && $input['action'] === 'dagitim_yap') {
        $urun_kodu = $input['urun_kodu'] ?? '';
        $dagitimlar = $input['dagitimlar'] ?? []; // [{detay_id, musteri_id, miktar}, ...]

        if (empty($urun_kodu) || empty($dagitimlar)) {
            echo json_encode(['status' => 'error', 'message' => 'Eksik veri']);
            exit;
        }

        try {
            $pdo->beginTransaction();

            $toplamusd_stmt = $pdo->prepare("SELECT usd_fiyat FROM urunler WHERE urun_kodu = ?");
            $toplamusd_stmt->execute([$urun_kodu]);
            $urun = $toplamusd_stmt->fetch();
            $usd_fiyat = $urun ? $urun['usd_fiyat'] : 0;

            foreach ($dagitimlar as $d) {
                $detay_id = $d['detay_id'];
                $musteri_id = $d['musteri_id'];
                $siparis_id = $d['siparis_id'];
                $miktar = (float) $d['miktar'];

                if ($miktar <= 0)
                    continue;

                // 1. siparis_detaylari güncelle
                $update_detay = $pdo->prepare("UPDATE siparis_detaylari 
                    SET teslim_edilen_kg = teslim_edilen_kg + :miktar,
                        kalan_kg = kalan_kg - :miktar
                    WHERE id = :detay_id");
                $update_detay->execute([
                    'miktar' => $miktar,
                    'detay_id' => $detay_id
                ]);

                // 2. Siparişin tamamlanıp tamamlanmadığını kontrol et
                $check_siparis = $pdo->prepare("SELECT SUM(kalan_kg) as toplam_kalan FROM siparis_detaylari WHERE siparis_id = :siparis_id");
                $check_siparis->execute(['siparis_id' => $siparis_id]);
                $siparis_durum = $check_siparis->fetch();

                if ($siparis_durum && $siparis_durum['toplam_kalan'] <= 0) {
                    $update_siparis = $pdo->prepare("UPDATE siparisler SET durum = 'tamamlandi' WHERE id = :siparis_id");
                    $update_siparis->execute(['siparis_id' => $siparis_id]);
                }

                // 3. Teslimat kaydı oluştur
                $insert_teslimat = $pdo->prepare("INSERT INTO teslimatlar (musteri_id, usd_kuru, toplam_usd, toplam_tl) VALUES (:musteri_id, :usd_kuru, :toplam_usd, :toplam_tl)");
                // Not: Şimdilik usd_kuru = 1 (veya varsayılan) kullanıyoruz. Gelecekte kur güncellenebilir.
                $kuru = 1;
                $toplam_usd = $miktar * $usd_fiyat;
                $toplam_tl = $toplam_usd * $kuru;

                $insert_teslimat->execute([
                    'musteri_id' => $musteri_id,
                    'usd_kuru' => $kuru,
                    'toplam_usd' => $toplam_usd,
                    'toplam_tl' => $toplam_tl
                ]);
                $teslimat_id = $pdo->lastInsertId();

                $insert_detay = $pdo->prepare("INSERT INTO teslimat_detaylari (teslimat_id, urun_kodu, teslim_edilen_kg) VALUES (:teslimat_id, :urun_kodu, :miktar)");
                $insert_detay->execute([
                    'teslimat_id' => $teslimat_id,
                    'urun_kodu' => $urun_kodu,
                    'miktar' => $miktar
                ]);
            }

            $pdo->commit();
            echo json_encode(['status' => 'success']);

        } catch (PDOException $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
        }
    }
}
?>