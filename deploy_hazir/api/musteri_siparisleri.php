<?php
session_start();
header('Content-Type: application/json');
require '../config.php';

if (!isset($_SESSION['musteri_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Oturum bulunamadı. Lütfen giriş yapın.']);
    exit;
}

$musteri_id = $_SESSION['musteri_id'];

try {
    // Ana siparişleri çek
    $stmt = $pdo->prepare("
        SELECT id, tarih, durum, 
        (SELECT SUM(istenen_kg) FROM siparis_detaylari WHERE siparis_id = siparisler.id) as toplam_istenen 
        FROM siparisler 
        WHERE musteri_id = ? 
        ORDER BY tarih DESC
    ");
    $stmt->execute([$musteri_id]);
    $siparisler = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sonuc = [];
    foreach ($siparisler as $sip) {
        // Her siparişin ürün detaylarını durum bilgisi ile birlikte çek
        $detayStmt = $pdo->prepare("
            SELECT d.urun_kodu, d.istenen_kg, d.teslim_edilen_kg, d.kalan_kg, d.durum, u.urun_adi
            FROM siparis_detaylari d
            JOIN urunler u ON d.urun_kodu = u.urun_kodu
            WHERE d.siparis_id = ?
            ORDER BY d.urun_kodu ASC
        ");
        $detayStmt->execute([$sip['id']]);
        $detaylar = $detayStmt->fetchAll(PDO::FETCH_ASSOC);

        $sip['detaylar'] = $detaylar;
        $sonuc[] = $sip;
    }

    echo json_encode($sonuc);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
}
?>