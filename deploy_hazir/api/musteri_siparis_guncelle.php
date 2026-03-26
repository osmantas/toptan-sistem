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
$sepet = $input['sepet'] ?? [];

if (!$siparis_id || empty($sepet)) {
    echo json_encode(['status' => 'error', 'message' => 'Sipariş ID veya sepet boş olamaz.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Siparişin bu müşteriye ait olup olmadığını ve durumunun 'beklemede' olup olmadığını kontrol et
    $stmt = $pdo->prepare("SELECT durum FROM siparisler WHERE id = ? AND musteri_id = ? FOR UPDATE");
    $stmt->execute([$siparis_id, $musteri_id]);
    $siparis = $stmt->fetch();

    if (!$siparis) {
        throw new Exception("Sipariş bulunamadı veya size ait değil.");
    }
    if ($siparis['durum'] !== 'beklemede') {
        throw new Exception("Sadece 'beklemede' olan siparişler güncellenebilir.");
    }

    // Eski detayları silmeden önce mevcut (orijinal ve eklenmiş miktar) değerleri al 
    $eskiStmt = $pdo->prepare("SELECT urun_kodu, istenen_kg, eklenen_kg FROM siparis_detaylari WHERE siparis_id = ?");
    $eskiStmt->execute([$siparis_id]);
    $eskiDetaylar = $eskiStmt->fetchAll(PDO::FETCH_ASSOC);
    $eskiMap = [];
    foreach ($eskiDetaylar as $ed) {
        $eskiMap[$ed['urun_kodu']] = [
            'istenen_kg' => floatval($ed['istenen_kg']),
            'eklenen_kg' => floatval($ed['eklenen_kg'])
        ];
    }

    // Eski detayları sil
    $delStmt = $pdo->prepare("DELETE FROM siparis_detaylari WHERE siparis_id = ?");
    $delStmt->execute([$siparis_id]);

    // Yeni sepeti yükle (eklenen_kg değerini eskiye göre hesapla)
    $insStmt = $pdo->prepare("INSERT INTO siparis_detaylari (siparis_id, urun_kodu, istenen_kg, kalan_kg, eklenen_kg) VALUES (?, ?, ?, ?, ?)");
    foreach ($sepet as $item) {
        $urunKodu = $item['urun_kodu'];
        $yeniMiktar = floatval($item['miktar_kg']);

        $yeniEklenen = 0.00;
        if (isset($eskiMap[$urunKodu])) {
            $eskiIstenen = floatval($eskiMap[$urunKodu]['istenen_kg']);
            $eskiEklenen = floatval($eskiMap[$urunKodu]['eklenen_kg']);
            $orijinalSiparis = $eskiIstenen - $eskiEklenen;

            if ($yeniMiktar > $orijinalSiparis) {
                $yeniEklenen = $yeniMiktar - $orijinalSiparis;
            } else {
                $yeniEklenen = 0.00;
            }
        } else {
            // Sonradan sepete yepyeni eklenen bir ürün ise tamamı 'eklenen' olarak hesaplanır
            $yeniEklenen = $yeniMiktar;
        }

        $insStmt->execute([
            $siparis_id,
            $urunKodu,
            $yeniMiktar,
            $yeniMiktar, // Başlangıçta hepsi kalan_kg
            $yeniEklenen
        ]);
    }

    // Siparişin son güncellenme tarihini / 'guncellendi' bayrağını işaretle
    $updStmt = $pdo->prepare("UPDATE siparisler SET guncellendi = 1, tarih = CURRENT_TIMESTAMP WHERE id = ?");
    $updStmt->execute([$siparis_id]);

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Sipariş başarıyla güncellendi.']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>