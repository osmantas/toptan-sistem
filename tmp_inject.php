<?php
$file = 'c:\\xampp\\htdocs\\AKSA_TOPTAN\\api\\siparis_api.php';
$content = file_get_contents($file);

$newEndpoints = '
    // Urun bazli rapor - hangi musteri ne kadar tuketmis
    } elseif ($action === \'raporlar_urun_bazli\') {
        try {
            $where = "WHERE 1=1";
            $params = [];
            if (!empty($_GET[\'urun_kodu\'])) {
                $where .= " AND td.urun_kodu = :urun_kodu";
                $params[\'urun_kodu\'] = $_GET[\'urun_kodu\'];
            }
            if (!empty($_GET[\'tarih_bas\'])) {
                $where .= " AND DATE(t.olusturma_tarihi) >= :tarih_bas";
                $params[\'tarih_bas\'] = $_GET[\'tarih_bas\'];
            }
            if (!empty($_GET[\'tarih_son\'])) {
                $where .= " AND DATE(t.olusturma_tarihi) <= :tarih_son";
                $params[\'tarih_son\'] = $_GET[\'tarih_son\'];
            }

            $sql = "SELECT m.id as musteri_id, m.firma_adi,
                        SUM(td.teslim_edilen_kg) as toplam_kg,
                        SUM(td.teslim_edilen_kg * CASE WHEN td.birim_usd_fiyat > 0 THEN td.birim_usd_fiyat ELSE u.usd_fiyat END) as toplam_brut,
                        SUM((td.teslim_edilen_kg * CASE WHEN td.birim_usd_fiyat > 0 THEN td.birim_usd_fiyat ELSE u.usd_fiyat END) 
                            - ((td.teslim_edilen_kg * CASE WHEN td.birim_usd_fiyat > 0 THEN td.birim_usd_fiyat ELSE u.usd_fiyat END) * COALESCE(m.iskonto_orani, 0) / 100)) as toplam_net,
                        COALESCE(m.iskonto_orani, 0) as iskonto_orani
                    FROM teslimat_detaylari td
                    JOIN teslimatlar t ON td.teslimat_id = t.id
                    JOIN musteriler m ON t.musteri_id = m.id
                    JOIN urunler u ON td.urun_kodu = u.urun_kodu
                    $where
                    GROUP BY m.id, m.firma_adi
                    ORDER BY toplam_kg DESC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([\'status\' => \'error\', \'message\' => $e->getMessage()]);
        }

    // Urunler listesi (filtre dropdown icin)
    } elseif ($action === \'urunler_listesi\') {
        try {
            $stmt = $pdo->query("SELECT urun_kodu, urun_adi FROM urunler ORDER BY urun_adi ASC");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([\'status\' => \'error\', \'message\' => $e->getMessage()]);
        }

';

// musteriler_listesi oncesine ekle
$search = '} elseif ($action === \'musteriler_listesi\')';
if (strpos($content, 'raporlar_urun_bazli') === false) {
    $content = str_replace($search, $newEndpoints . '    ' . $search, $content);
    file_put_contents($file, $content);
    echo "SUCCESS: New endpoints added.\n";
} else {
    echo "SKIP: Endpoints already exist.\n";
}
?>