<?php
header('Content-Type: application/json; charset=utf-8');
require dirname(__DIR__) . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'listele';

    // =============================================
    // TAB 1: MÜŞTERİ SİPARİŞLERİ (beklemede)
    // Durum artık siparis_detaylari.durum üzerinden
    // =============================================
    if ($action === 'musteri_siparisleri') {
        try {
            $where = "WHERE d.durum = 'beklemede' AND d.kalan_kg > 0";
            $params = [];
            if (!empty($_GET['musteri_id'])) {
                $where .= " AND s.musteri_id = :musteri_id";
                $params['musteri_id'] = $_GET['musteri_id'];
            }
            if (!empty($_GET['urun_kodu'])) {
                $where .= " AND d.urun_kodu LIKE :urun_kodu";
                $params['urun_kodu'] = '%' . $_GET['urun_kodu'] . '%';
            }

            $sql = "SELECT d.urun_kodu, u.urun_adi,
                        SUM(d.istenen_kg) as toplam_istenen,
                        SUM(d.kalan_kg) as toplam_bekleyen,
                        COUNT(DISTINCT s.musteri_id) as musteri_sayisi
                    FROM siparis_detaylari d
                    JOIN siparisler s ON d.siparis_id = s.id
                    JOIN urunler u ON d.urun_kodu = u.urun_kodu
                    $where
                    GROUP BY d.urun_kodu, u.urun_adi
                    ORDER BY u.urun_adi ASC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll());
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

    } elseif ($action === 'musteri_siparis_detay' && isset($_GET['urun_kodu'])) {
        try {
            $where = "WHERE d.urun_kodu = :urun_kodu AND d.durum = 'beklemede' AND d.kalan_kg > 0";
            $params = ['urun_kodu' => $_GET['urun_kodu']];
            if (!empty($_GET['musteri_id'])) {
                $where .= " AND s.musteri_id = :musteri_id";
                $params['musteri_id'] = $_GET['musteri_id'];
            }

            $sql = "SELECT d.id as detay_id, d.siparis_id, d.istenen_kg, d.teslim_edilen_kg, d.kalan_kg, d.eklenen_kg,
                        d.durum, s.tarih,
                        m.id as musteri_id, m.firma_adi
                    FROM siparis_detaylari d
                    JOIN siparisler s ON d.siparis_id = s.id
                    JOIN musteriler m ON s.musteri_id = m.id
                    $where
                    ORDER BY s.tarih ASC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll());
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

        // =============================================
        // TAB 2: TEDARİK LİSTESİ
        // =============================================
    } elseif ($action === 'tedarik_listesi') {
        try {
            $where = "WHERE d.durum = 'tedarik' AND d.kalan_kg > 0";
            $params = [];
            if (!empty($_GET['urun_kodu'])) {
                $where .= " AND d.urun_kodu LIKE :urun_kodu";
                $params['urun_kodu'] = '%' . $_GET['urun_kodu'] . '%';
            }

            $sql = "SELECT d.urun_kodu, u.urun_adi,
                        SUM(d.kalan_kg) as toplam_bekleyen,
                        COUNT(DISTINCT s.musteri_id) as musteri_sayisi,
                        (SELECT COALESCE(SUM(d2.istenen_kg), 0) FROM siparis_detaylari d2 WHERE d2.urun_kodu = d.urun_kodu AND d2.durum IN ('geldi', 'tamamlandi')) as toplam_gelen
                    FROM siparis_detaylari d
                    JOIN siparisler s ON d.siparis_id = s.id
                    JOIN urunler u ON d.urun_kodu = u.urun_kodu
                    $where
                    GROUP BY d.urun_kodu, u.urun_adi
                    ORDER BY u.urun_adi ASC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll());
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

    } elseif ($action === 'tedarik_musteriler' && isset($_GET['urun_kodu'])) {
        try {
            $sql = "SELECT d.siparis_id, s.tarih, m.id as musteri_id, m.firma_adi,
                        (SELECT SUM(d2.istenen_kg) FROM siparis_detaylari d2 WHERE d2.siparis_id = d.siparis_id AND d2.urun_kodu = d.urun_kodu) as toplam_istenen,
                        SUM(CASE WHEN d.durum = 'tedarik' THEN d.kalan_kg ELSE 0 END) as beklemede_kg
                    FROM siparis_detaylari d
                    JOIN siparisler s ON d.siparis_id = s.id
                    JOIN musteriler m ON s.musteri_id = m.id
                    WHERE d.urun_kodu = :urun_kodu AND d.durum IN ('tedarik', 'geldi')
                    GROUP BY d.siparis_id, s.tarih, m.id, m.firma_adi
                    HAVING beklemede_kg > 0
                    ORDER BY s.tarih ASC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute(['urun_kodu' => $_GET['urun_kodu']]);
            echo json_encode($stmt->fetchAll());
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

        // =============================================
        // TAB 3: TESLİMAT (geldi)
        // =============================================
    } elseif ($action === 'teslimat_bekleyen') {
        try {
            $where = "WHERE d.durum = 'geldi' AND d.kalan_kg > 0";
            $params = [];
            if (!empty($_GET['musteri_id'])) {
                $where .= " AND s.musteri_id = :musteri_id";
                $params['musteri_id'] = $_GET['musteri_id'];
            }
            if (!empty($_GET['urun_kodu'])) {
                $where .= " AND d.urun_kodu LIKE :urun_kodu";
                $params['urun_kodu'] = '%' . $_GET['urun_kodu'] . '%';
            }

            $sql = "SELECT d.urun_kodu, u.urun_adi,
                        SUM(d.istenen_kg) as toplam_istenen,
                        SUM(d.kalan_kg) as toplam_bekleyen,
                        COUNT(DISTINCT s.musteri_id) as musteri_sayisi
                    FROM siparis_detaylari d
                    JOIN siparisler s ON d.siparis_id = s.id
                    JOIN urunler u ON d.urun_kodu = u.urun_kodu
                    $where
                    GROUP BY d.urun_kodu, u.urun_adi
                    ORDER BY u.urun_adi ASC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll());
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

    } elseif ($action === 'teslimat_musteriler' && isset($_GET['urun_kodu'])) {
        try {
            $where = "WHERE d.urun_kodu = :urun_kodu AND d.durum = 'geldi' AND d.kalan_kg > 0";
            $params = ['urun_kodu' => $_GET['urun_kodu']];
            if (!empty($_GET['musteri_id'])) {
                $where .= " AND s.musteri_id = :musteri_id";
                $params['musteri_id'] = $_GET['musteri_id'];
            }

            $sql = "SELECT d.id as detay_id, d.siparis_id, d.istenen_kg, d.teslim_edilen_kg, d.kalan_kg,
                        d.durum, s.tarih,
                        m.id as musteri_id, m.firma_adi, m.telefon,
                        u.usd_fiyat, COALESCE(m.iskonto_orani, 0) as iskonto_orani,
                        (SELECT SUM(d2.istenen_kg) FROM siparis_detaylari d2 WHERE d2.siparis_id = d.siparis_id AND d2.urun_kodu = d.urun_kodu) as toplam_istenen,
                        (SELECT SUM(d2.teslim_edilen_kg) FROM siparis_detaylari d2 WHERE d2.siparis_id = d.siparis_id AND d2.urun_kodu = d.urun_kodu) as kumulatif_teslimat
                    FROM siparis_detaylari d
                    JOIN siparisler s ON d.siparis_id = s.id
                    JOIN musteriler m ON s.musteri_id = m.id
                    JOIN urunler u ON d.urun_kodu = u.urun_kodu
                    $where
                    ORDER BY s.tarih ASC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll());
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

    } elseif ($action === 'teslim_edilenler') {
        try {
            // Sadece henzü fişe bağlanmamış (teslimat_id IS NULL) tamamlananlar
            $where = "WHERE d.durum = 'tamamlandi' AND d.teslimat_id IS NULL";
            $params = [];
            if (!empty($_GET['musteri_id'])) {
                $where .= " AND s.musteri_id = :musteri_id";
                $params['musteri_id'] = $_GET['musteri_id'];
            }

            $sql = "SELECT d.id as detay_id, d.siparis_id, d.urun_kodu, u.urun_adi,
                        d.istenen_kg, d.teslim_edilen_kg, d.durum,
                        s.tarih, m.id as musteri_id, m.firma_adi, m.telefon,
                        u.usd_fiyat, COALESCE(m.iskonto_orani, 0) as iskonto_orani
                    FROM siparis_detaylari d
                    JOIN siparisler s ON d.siparis_id = s.id
                    JOIN musteriler m ON s.musteri_id = m.id
                    JOIN urunler u ON d.urun_kodu = u.urun_kodu
                    $where
                    ORDER BY s.tarih DESC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll());
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

        // =============================================
        // TESLIMAT ARŞİVİ — Kapatılmış fişler listesi
        // =============================================
    } elseif ($action === 'teslimat_arsiv') {
        try {
            $where = "WHERE 1=1";
            $params = [];
            if (!empty($_GET['musteri_id'])) {
                $where .= " AND t.musteri_id = :musteri_id";
                $params['musteri_id'] = $_GET['musteri_id'];
            }
            if (!empty($_GET['tarih_bas'])) {
                $where .= " AND DATE(t.olusturma_tarihi) >= :tarih_bas";
                $params['tarih_bas'] = $_GET['tarih_bas'];
            }
            if (!empty($_GET['tarih_son'])) {
                $where .= " AND DATE(t.olusturma_tarihi) <= :tarih_son";
                $params['tarih_son'] = $_GET['tarih_son'];
            }
            if (!empty($_GET['urun_kodu'])) {
                $where .= " AND EXISTS (SELECT 1 FROM teslimat_detaylari td WHERE td.teslimat_id = t.id AND td.urun_kodu LIKE :urun_kodu)";
                $params['urun_kodu'] = '%' . $_GET['urun_kodu'] . '%';
            }

            // Sayfalama parametreleri
            $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 20;
            $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
            if ($limit <= 0)
                $limit = 20;
            if ($page <= 0)
                $page = 1;
            $offset = ($page - 1) * $limit;

            // Toplam kayıt sayısını bul (sayfalama için)
            $countSql = "SELECT COUNT(DISTINCT t.id) FROM teslimatlar t 
                         JOIN musteriler m ON t.musteri_id = m.id 
                         $where";
            $countStmt = $pdo->prepare($countSql);
            $countStmt->execute($params);
            $totalCount = (int) $countStmt->fetchColumn();
            $totalPages = ceil($totalCount / $limit);

            $sql = "SELECT t.id as fis_id, t.musteri_id, m.firma_adi, m.telefon,
                        t.olusturma_tarihi, t.usd_kuru, t.toplam_usd, t.toplam_tl,
                        COALESCE(m.iskonto_orani, 0) as iskonto_orani,
                        SUM(td.teslim_edilen_kg) as toplam_kg,
                        COUNT(td.id) as urun_sayisi
                    FROM teslimatlar t
                    JOIN musteriler m ON t.musteri_id = m.id
                    LEFT JOIN teslimat_detaylari td ON td.teslimat_id = t.id
                    $where
                    GROUP BY t.id, t.musteri_id, m.firma_adi, m.telefon, t.olusturma_tarihi, t.usd_kuru, t.toplam_usd, t.toplam_tl
                    ORDER BY t.olusturma_tarihi DESC
                    LIMIT $limit OFFSET $offset";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            echo json_encode([
                'status' => 'success',
                'data' => $stmt->fetchAll(),
                'total_count' => $totalCount,
                'total_pages' => $totalPages,
                'current_page' => $page,
                'limit' => $limit
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

        // Tek fişin tüm kalemlerini getir (yeniden yazdırma için)
    } elseif ($action === 'teslimat_fis_detay' && isset($_GET['fis_id'])) {
        try {
            $stmt = $pdo->prepare("
                SELECT
                    t.id as fis_id, m.firma_adi, m.telefon, t.usd_kuru,
                    COALESCE(td.iskonto_orani, m.iskonto_orani, 0) as iskonto_orani,
                    t.olusturma_tarihi, t.toplam_usd, t.toplam_tl,
                    td.id as td_id, td.urun_kodu, u.urun_adi,
                    CASE WHEN td.birim_usd_fiyat > 0 THEN td.birim_usd_fiyat ELSE u.usd_fiyat END as usd_fiyat,
                    td.teslim_edilen_kg
                FROM teslimatlar t
                JOIN musteriler m ON t.musteri_id = m.id
                JOIN teslimat_detaylari td ON td.teslimat_id = t.id
                JOIN urunler u ON u.urun_kodu = td.urun_kodu
                WHERE t.id = ?
                ORDER BY td.id ASC
            ");
            $stmt->execute([$_GET['fis_id']]);
            echo json_encode($stmt->fetchAll());
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

    } elseif ($action === 'tum_detay_idler' && isset($_GET['durum'])) {
        try {
            $durum = $_GET['durum'];
            $stmt = $pdo->prepare("SELECT d.id FROM siparis_detaylari d JOIN siparisler s ON d.siparis_id = s.id WHERE d.durum = ? AND d.kalan_kg > 0 ORDER BY d.id");
            $stmt->execute([$durum]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_COLUMN));
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

    } elseif ($action === 'raporlar') {
        try {
            $where = "WHERE 1=1";
            $params = [];
            if (!empty($_GET['musteri_id'])) {
                $where .= " AND t.musteri_id = :musteri_id";
                $params['musteri_id'] = $_GET['musteri_id'];
            }
            if (!empty($_GET['tarih_bas'])) {
                $where .= " AND DATE(t.olusturma_tarihi) >= :tarih_bas";
                $params['tarih_bas'] = $_GET['tarih_bas'];
            }
            if (!empty($_GET['tarih_son'])) {
                $where .= " AND DATE(t.olusturma_tarihi) <= :tarih_son";
                $params['tarih_son'] = $_GET['tarih_son'];
            }

            $sql = "SELECT td.urun_kodu, u.urun_adi,
                        SUM(td.teslim_edilen_kg) as toplam_kg,
                        SUM(td.teslim_edilen_kg * u.usd_fiyat) as toplam_brut,
                        SUM((td.teslim_edilen_kg * u.usd_fiyat) - ((td.teslim_edilen_kg * u.usd_fiyat) * COALESCE(m.iskonto_orani, 0) / 100)) as toplam_net
                    FROM teslimat_detaylari td
                    JOIN teslimatlar t ON td.teslimat_id = t.id
                    JOIN musteriler m ON t.musteri_id = m.id
                    JOIN urunler u ON td.urun_kodu = u.urun_kodu
                    $where
                    GROUP BY td.urun_kodu, u.urun_adi
                    ORDER BY toplam_net DESC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }


        // Ürün bazlı rapor — hangi müşteri ne kadar tüketmiş
    } elseif ($action === 'raporlar_urun_bazli') {
        try {
            $where = "WHERE 1=1";
            $params = [];
            if (!empty($_GET['urun_kodu'])) {
                $where .= " AND td.urun_kodu = :urun_kodu";
                $params['urun_kodu'] = $_GET['urun_kodu'];
            }
            if (!empty($_GET['tarih_bas'])) {
                $where .= " AND DATE(t.olusturma_tarihi) >= :tarih_bas";
                $params['tarih_bas'] = $_GET['tarih_bas'];
            }
            if (!empty($_GET['tarih_son'])) {
                $where .= " AND DATE(t.olusturma_tarihi) <= :tarih_son";
                $params['tarih_son'] = $_GET['tarih_son'];
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
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

        // Ürünler listesi (filtre dropdown için)
    } elseif ($action === 'urunler_listesi') {
        try {
            $stmt = $pdo->query("SELECT urun_kodu, urun_adi FROM urunler ORDER BY urun_adi ASC");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }


    } elseif ($action === 'musteriler_listesi') {
        try {
            $stmt = $pdo->query("SELECT id, firma_adi FROM musteriler ORDER BY firma_adi ASC");
            echo json_encode($stmt->fetchAll());
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

    } elseif ($action === 'listele') {
        try {
            $stmt = $pdo->query("SELECT s.id, s.tarih, s.durum, s.guncellendi, m.firma_adi, 
                (SELECT SUM(istenen_kg) FROM siparis_detaylari WHERE siparis_id = s.id) as toplam_istened
                FROM siparisler s 
                JOIN musteriler m ON s.musteri_id = m.id 
                ORDER BY s.id DESC");
            echo json_encode($stmt->fetchAll());
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası']);
        }
    } elseif ($action === 'detay' && isset($_GET['id'])) {
        try {
            $stmt = $pdo->prepare("SELECT d.urun_kodu, d.istenen_kg, d.teslim_edilen_kg, d.kalan_kg, d.durum,
                u.urun_adi, u.usd_fiyat, COALESCE(m.iskonto_orani, 0) as iskonto_orani 
                FROM siparis_detaylari d 
                JOIN urunler u ON d.urun_kodu = u.urun_kodu 
                JOIN siparisler s ON d.siparis_id = s.id
                JOIN musteriler m ON s.musteri_id = m.id
                WHERE d.siparis_id = :siparis_id");
            $stmt->execute(['siparis_id' => $_GET['id']]);
            echo json_encode($stmt->fetchAll());
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası']);
        }
    }

} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    // =============================================
    // TOPLU DURUM GÜNCELLEME - detay bazında
    // =============================================
    if (isset($input['action']) && $input['action'] === 'toplu_durum_guncelle') {
        $detay_ids = $input['detay_ids'] ?? [];
        $yeni_durum = $input['yeni_durum'] ?? '';
        $valid_statuses = ['beklemede', 'tedarik', 'geldi', 'tamamlandi', 'iptal'];

        if (empty($detay_ids) || !in_array($yeni_durum, $valid_statuses)) {
            echo json_encode(['status' => 'error', 'message' => 'Eksik veya geçersiz veri']);
            exit;
        }

        try {
            $pdo->beginTransaction();

            // siparis_detaylari.durum güncelle (detay bazında)
            $placeholders = implode(',', array_fill(0, count($detay_ids), '?'));
            if ($yeni_durum === 'tamamlandi') {
                $update = $pdo->prepare("UPDATE siparis_detaylari SET durum = ?, teslim_edilen_kg = istenen_kg WHERE id IN ($placeholders)");
            } else {
                $update = $pdo->prepare("UPDATE siparis_detaylari SET durum = ? WHERE id IN ($placeholders)");
            }
            $params = array_merge([$yeni_durum], array_values($detay_ids));
            $update->execute($params);

            // siparisler.durum'u da senkronize et (genel durum için)
            // Her sipariş için: tüm detaylar aynı duruma geçtiyse sipariş durumunu da güncelle
            $stmt = $pdo->prepare("SELECT DISTINCT siparis_id FROM siparis_detaylari WHERE id IN ($placeholders)");
            $stmt->execute(array_values($detay_ids));
            $siparis_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($siparis_ids as $siparis_id) {
                // Bu siparişteki tüm detayların durumunu al
                $cStmt = $pdo->prepare("SELECT DISTINCT durum FROM siparis_detaylari WHERE siparis_id = ?");
                $cStmt->execute([$siparis_id]);
                $durumlar = $cStmt->fetchAll(PDO::FETCH_COLUMN);

                // Hepsi aynı durumdaysa sipariş durumunu güncelle
                if (count($durumlar) === 1) {
                    $pdo->prepare("UPDATE siparisler SET durum = ?, guncellendi = 0 WHERE id = ?")
                        ->execute([$durumlar[0], $siparis_id]);
                } else {
                    // Karışık durum: en ileri durumu sipariş durumu yap
                    $oncelik = ['iptal' => 0, 'beklemede' => 1, 'tedarik' => 2, 'geldi' => 3, 'tamamlandi' => 4];
                    $maxDurum = collect_max_durum($durumlar, $oncelik);
                    $pdo->prepare("UPDATE siparisler SET durum = ?, guncellendi = 0 WHERE id = ?")
                        ->execute([$maxDurum, $siparis_id]);
                }
            }

            $pdo->commit();
            echo json_encode(['status' => 'success', 'updated' => count($detay_ids)]);
        } catch (PDOException $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

        // TEK DETAY DURUM GÜNCELLEME
    } elseif (isset($input['action']) && $input['action'] === 'detay_durum_guncelle') {
        try {
            $detay_id = $input['detay_id'];
            $yeni_durum = $input['yeni_durum'];
            $valid_statuses = ['beklemede', 'tedarik', 'geldi', 'tamamlandi', 'iptal'];
            if (!in_array($yeni_durum, $valid_statuses)) {
                echo json_encode(['status' => 'error', 'message' => 'Geçersiz durum']);
                exit;
            }

            $pdo->beginTransaction();

            // Detayı güncelle — tamamlandi ise teslim_edilen_kg de güncelle
            if ($yeni_durum === 'tamamlandi') {
                $stmt = $pdo->prepare("UPDATE siparis_detaylari SET durum = ?, teslim_edilen_kg = istenen_kg WHERE id = ?");
            } else {
                $stmt = $pdo->prepare("UPDATE siparis_detaylari SET durum = ? WHERE id = ?");
            }
            $stmt->execute([$yeni_durum, $detay_id]);

            // İlgili sipariş id'sini bul
            $stmt2 = $pdo->prepare("SELECT siparis_id FROM siparis_detaylari WHERE id = ?");
            $stmt2->execute([$detay_id]);
            $row = $stmt2->fetch();

            if ($row) {
                // Bu siparişteki tüm detayların durumunu al
                $cStmt = $pdo->prepare("SELECT DISTINCT durum FROM siparis_detaylari WHERE siparis_id = ?");
                $cStmt->execute([$row['siparis_id']]);
                $durumlar = $cStmt->fetchAll(PDO::FETCH_COLUMN);

                $oncelik = ['iptal' => 0, 'beklemede' => 1, 'tedarik' => 2, 'geldi' => 3, 'tamamlandi' => 4];
                $maxDurum = array_reduce($durumlar, function ($carry, $d) use ($oncelik) {
                    return ($oncelik[$d] ?? 0) > ($oncelik[$carry] ?? 0) ? $d : $carry;
                }, 'beklemede');

                $pdo->prepare("UPDATE siparisler SET durum = ?, guncellendi = 0 WHERE id = ?")
                    ->execute([$maxDurum, $row['siparis_id']]);
            }

            $pdo->commit();
            echo json_encode(['status' => 'success']);
        } catch (PDOException $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

    } elseif (isset($input['action']) && $input['action'] === 'teslim_et') {
        try {
            $detay_id = $input['detay_id'];
            $istenenMiktar = floatval($input['miktar'] ?? 0);
            if ($istenenMiktar <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'Geçersiz miktar']);
                exit;
            }

            // Önce tıklanan referans detayı al
            $check = $pdo->prepare("SELECT * FROM siparis_detaylari WHERE id = ?");
            $check->execute([$detay_id]);
            $ilkRow = $check->fetch();
            if (!$ilkRow) {
                echo json_encode(['status' => 'error', 'message' => 'Sipariş detayı bulunamadı']);
                exit;
            }

            $siparis_id = $ilkRow['siparis_id'];
            $urun_kodu = $ilkRow['urun_kodu'];

            // O siparişe ve ürüne ait BÜTÜN teslim edilmemiş satırları getir (Waterfall havuzu)
            $stmt = $pdo->prepare("SELECT id, kalan_kg, teslim_edilen_kg, durum FROM siparis_detaylari 
                WHERE siparis_id = ? AND urun_kodu = ? AND kalan_kg > 0
                ORDER BY CASE durum WHEN 'geldi' THEN 1 WHEN 'tedarik' THEN 2 ELSE 3 END, id ASC");
            $stmt->execute([$siparis_id, $urun_kodu]);
            $satirlar = $stmt->fetchAll();

            $toplamMevcut = array_sum(array_column($satirlar, 'kalan_kg'));
            if ($istenenMiktar > $toplamMevcut + 0.01) {
                echo json_encode(['status' => 'error', 'message' => "Teslim miktarı ({$istenenMiktar} KG) sistemdeki bekleyen ürün miktarını ({$toplamMevcut} KG) aşamaz."]);
                exit;
            }

            $pdo->beginTransaction();

            $kalanMiktar = $istenenMiktar;
            foreach ($satirlar as $r) {
                if ($kalanMiktar <= 0.009) break;

                $rKalan = floatval($r['kalan_kg']);
                $dusulecek = min($kalanMiktar, $rKalan);
                
                $yeniKalan = round($rKalan - $dusulecek, 2);
                $yeniTeslim = floatval($r['teslim_edilen_kg']) + $dusulecek;
                
                // Dacă complet teslimat, schimbă starea
                $yDurum = ($yeniKalan <= 0.01) ? 'tamamlandi' : $r['durum'];

                $upd = $pdo->prepare("UPDATE siparis_detaylari SET teslim_edilen_kg = ?, kalan_kg = ?, durum = ? WHERE id = ?");
                $upd->execute([$yeniTeslim, $yeniKalan, $yDurum, $r['id']]);

                // Dacă s-a făcut livrare parțială "Tamamlandı" ama bir kısmı yeni bir split satıra gidecek mi? 
                // Hayır, zaten "geldi" veya "tedarik" bekleyen havuzundaydılar. Sadece mevcudu güncelledik. Zaten DB'de var.
                // İstenen_kg'ye dokunmuyoruz çünkü fiş faturası vb. için. Sadece kalan/teslim edilen matematiğini update ediyoruz!
                
                $kalanMiktar = round($kalanMiktar - $dusulecek, 2);
            }

            // Siparişin genel durumunu senkronize et (hepsi tamamlandı mı vs.)
            $cStmt = $pdo->prepare("SELECT DISTINCT durum FROM siparis_detaylari WHERE siparis_id = ?");
            $cStmt->execute([$siparis_id]);
            $durumlar = $cStmt->fetchAll(PDO::FETCH_COLUMN);

            $oncelik = ['iptal' => 0, 'beklemede' => 1, 'tedarik' => 2, 'geldi' => 3, 'tamamlandi' => 4];
            $maxDurum = array_reduce($durumlar, function ($carry, $d) use ($oncelik) {
                return ($oncelik[$d] ?? 0) > ($oncelik[$carry] ?? 0) ? $d : $carry;
            }, 'beklemede');

            $pdo->prepare("UPDATE siparisler SET durum = ?, guncellendi = 0 WHERE id = ?")->execute([$maxDurum, $siparis_id]);

            $pdo->commit();
            echo json_encode([
                'status' => 'success',
                'mesaj' => "{$istenenMiktar} KG başarıyla müşteriye teslim edildi."
            ]);
        } catch (PDOException $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

        // Tedarik sekmesi — ÜRÜN BAZLI Geldi işlemi (FIFO dağıtım)
        // Parametre: urun_kodu + miktar (toplam gelen KG)
        // Müşteri satırlarına FIFO sırasıyla (küçük sipariş önce) dağıtır.
        // Kısmi kalan için yeni tedarik satırı açılır (split mantığı).
    } elseif (isset($input['action']) && $input['action'] === 'tedarik_geldi_urun') {
        try {
            $urun_kodu = trim($input['urun_kodu'] ?? '');
            $kalanPool = floatval($input['miktar'] ?? 0);

            if (!$urun_kodu || $kalanPool <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'Geçersiz ürün kodu veya miktar']);
                exit;
            }

            // Bu ürünün tedarik durumundaki tüm detay satırlarını al (küçük sipariş önce — FIFO)
            $stmt = $pdo->prepare("SELECT d.id as detay_id, d.siparis_id, d.kalan_kg
                FROM siparis_detaylari d
                WHERE d.urun_kodu = ? AND d.durum = 'tedarik' AND d.kalan_kg > 0
                ORDER BY d.kalan_kg ASC, d.id ASC");
            $stmt->execute([$urun_kodu]);
            $detaylar = $stmt->fetchAll();

            if (empty($detaylar)) {
                echo json_encode(['status' => 'error', 'message' => 'Bu ürün için tedarik kaydı bulunamadı']);
                exit;
            }

            $toplamTedarik = array_sum(array_column($detaylar, 'kalan_kg'));
            if ($kalanPool > $toplamTedarik + 0.01) {
                echo json_encode(['status' => 'error', 'message' => "Girilen miktar ({$kalanPool} KG) toplam tedarik miktarından ({$toplamTedarik} KG) fazla olamaz."]);
                exit;
            }

            $pdo->beginTransaction();

            $islenenSipIds = [];
            foreach ($detaylar as $d) {
                if ($kalanPool <= 0.009)
                    break;

                $satirKalan = floatval($d['kalan_kg']);
                $gelenMiktar = min($kalanPool, $satirKalan);
                $satirKalanSonra = round($satirKalan - $gelenMiktar, 2);

                // Orijinal satırı: gelen miktar → geldi
                $updStmt = $pdo->prepare("UPDATE siparis_detaylari
                    SET istenen_kg = ?, kalan_kg = ?, durum = 'geldi'
                    WHERE id = ?");
                $updStmt->execute([$gelenMiktar, $gelenMiktar, $d['detay_id']]);

                // Kalan varsa → yeni tedarik satırı
                if ($satirKalanSonra > 0.009) {
                    $insStmt = $pdo->prepare("INSERT INTO siparis_detaylari
                        (siparis_id, urun_kodu, istenen_kg, teslim_edilen_kg, kalan_kg, durum)
                        VALUES (?, ?, ?, 0, ?, 'tedarik')");
                    $insStmt->execute([$d['siparis_id'], $urun_kodu, $satirKalanSonra, $satirKalanSonra]);
                }

                $kalanPool = round($kalanPool - $gelenMiktar, 2);
                $islenenSipIds[] = $d['siparis_id'];
            }

            // Sipariş genel durumlarını senkronize et
            $oncelik = ['iptal' => 0, 'beklemede' => 1, 'tedarik' => 2, 'geldi' => 3, 'tamamlandi' => 4];
            foreach (array_unique($islenenSipIds) as $siparis_id) {
                $cStmt = $pdo->prepare("SELECT DISTINCT durum FROM siparis_detaylari WHERE siparis_id = ?");
                $cStmt->execute([$siparis_id]);
                $durumlar = $cStmt->fetchAll(PDO::FETCH_COLUMN);
                $maxDurum = array_reduce($durumlar, function ($carry, $d) use ($oncelik) {
                    return ($oncelik[$d] ?? 0) > ($oncelik[$carry] ?? 0) ? $d : $carry;
                }, 'beklemede');
                $pdo->prepare("UPDATE siparisler SET durum = ?, guncellendi = 0 WHERE id = ?")
                    ->execute([$maxDurum, $siparis_id]);
            }

            $pdo->commit();
            echo json_encode([
                'status' => 'success',
                'mesaj' => floatval($input['miktar']) >= $toplamTedarik - 0.01
                    ? "Tüm {$toplamTedarik} KG geldi olarak işaretlendi."
                    : "{$input['miktar']} KG geldi olarak işaretlendi. Kalan " . round($toplamTedarik - floatval($input['miktar']), 2) . " KG tedarik listesinde bekliyor."
            ]);
        } catch (PDOException $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

        // Tedarik sekmesi kısmi "Geldi" işlemi — SPLIT MANTIĞI (tekil satır için, eski uyumluluk)
    } elseif (isset($input['action']) && $input['action'] === 'tedarik_geldi_kismi') {
        try {
            $detay_id = $input['detay_id'];
            $miktar = floatval($input['miktar'] ?? 0);
            if ($miktar <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'Geçersiz miktar']);
                exit;
            }

            // Mevcut satır bilgilerini al
            $check = $pdo->prepare("SELECT kalan_kg, istenen_kg, siparis_id, urun_kodu, teslim_edilen_kg FROM siparis_detaylari WHERE id = ?");
            $check->execute([$detay_id]);
            $row = $check->fetch();
            if (!$row) {
                echo json_encode(['status' => 'error', 'message' => 'Sipariş detayı bulunamadı']);
                exit;
            }
            $kalan = floatval($row['kalan_kg']);
            $siparis_id = $row['siparis_id'];
            $urun_kodu = $row['urun_kodu'];

            if ($miktar > $kalan + 0.01) {
                echo json_encode(['status' => 'error', 'message' => "Miktar ({$miktar} KG) kalan miktardan ({$kalan} KG) fazla olamaz."]);
                exit;
            }

            $pdo->beginTransaction();

            $kalanSonra = round($kalan - $miktar, 2);
            if ($kalanSonra < 0)
                $kalanSonra = 0;

            // --- Orijinal satırı: sadece gelen miktar kadar güncelle → geldi yap ---
            // istenen_kg ve kalan_kg → gelen miktar olarak set edilir
            $stmt = $pdo->prepare("UPDATE siparis_detaylari
                SET istenen_kg = ?,
                    kalan_kg   = ?,
                    durum      = 'geldi'
                WHERE id = ?");
            $stmt->execute([$miktar, $miktar, $detay_id]);

            // --- Kalan miktar varsa yeni tedarik satırı oluştur ---
            if ($kalanSonra > 0.009) {
                $ins = $pdo->prepare("INSERT INTO siparis_detaylari
                    (siparis_id, urun_kodu, istenen_kg, teslim_edilen_kg, kalan_kg, durum)
                    VALUES (?, ?, ?, 0, ?, 'tedarik')");
                $ins->execute([$siparis_id, $urun_kodu, $kalanSonra, $kalanSonra]);
            }

            // Sipariş genel durumunu senkronize et
            $cStmt = $pdo->prepare("SELECT DISTINCT durum FROM siparis_detaylari WHERE siparis_id = ?");
            $cStmt->execute([$siparis_id]);
            $durumlar = $cStmt->fetchAll(PDO::FETCH_COLUMN);
            $oncelik = ['iptal' => 0, 'beklemede' => 1, 'tedarik' => 2, 'geldi' => 3, 'tamamlandi' => 4];
            $maxDurum = array_reduce($durumlar, function ($carry, $d) use ($oncelik) {
                return ($oncelik[$d] ?? 0) > ($oncelik[$carry] ?? 0) ? $d : $carry;
            }, 'beklemede');
            $pdo->prepare("UPDATE siparisler SET durum = ?, guncellendi = 0 WHERE id = ?")
                ->execute([$maxDurum, $siparis_id]);

            $pdo->commit();
            echo json_encode([
                'status' => 'success',
                'geldi_miktar' => $miktar,
                'kalan_miktar' => $kalanSonra,
                'mesaj' => $kalanSonra > 0.009
                    ? "{$miktar} KG geldi olarak işaretlendi. Kalan {$kalanSonra} KG tedarik listesinde bekliyor."
                    : "{$miktar} KG tamamen geldi olarak işaretlendi."
            ]);
        } catch (PDOException $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

        // Eski uyumluluk
    } elseif (isset($input['action']) && $input['action'] === 'durum_guncelle' && isset($input['siparis_id'])) {
        try {
            $yeni_durum = $input['yeni_durum'];
            $valid_statuses = ['beklemede', 'tedarik', 'geldi', 'tamamlandi', 'iptal'];
            if (!in_array($yeni_durum, $valid_statuses)) {
                echo json_encode(['status' => 'error', 'message' => 'Geçersiz durum']);
                exit;
            }
            $pdo->beginTransaction();
            $pdo->prepare("UPDATE siparisler SET durum = ?, guncellendi = 0 WHERE id = ?")
                ->execute([$yeni_durum, $input['siparis_id']]);
            $pdo->prepare("UPDATE siparis_detaylari SET durum = ? WHERE siparis_id = ?")
                ->execute([$yeni_durum, $input['siparis_id']]);
            $pdo->commit();
            echo json_encode(['status' => 'success']);
        } catch (PDOException $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        // =============================================
        // TESLİMAT FİŞİ OLUŞTUR (Hesabı Kapat & Arşivle)
        // Parametre: musteri_id
        // =============================================
    } elseif (isset($input['action']) && $input['action'] === 'teslimat_fis_olustur') {
        try {
            $musteri_id = intval($input['musteri_id'] ?? 0);
            $usd_kuru = floatval($input['usd_kuru'] ?? 0);
            if (!$musteri_id) {
                echo json_encode(['status' => 'error', 'message' => 'Geçersiz müşteri ID']);
                exit;
            }
            if ($usd_kuru <= 0) {
                $usd_kuru = 1; // fallback
            }

            // Müşteri iskonto oranını al
            $mStmt = $pdo->prepare("SELECT COALESCE(iskonto_orani, 0) as iskonto_orani FROM musteriler WHERE id = ?");
            $mStmt->execute([$musteri_id]);
            $musteri = $mStmt->fetch();
            $iskonto_orani = floatval($musteri['iskonto_orani'] ?? 0);

            // Bu müşterinin fişe bağlanmamış tamamlanmış kalemlerini al (fiyat bilgisiyle)
            $stmt = $pdo->prepare("
                SELECT d.id as detay_id, d.urun_kodu, d.teslim_edilen_kg, u.usd_fiyat
                FROM siparis_detaylari d
                JOIN siparisler s ON d.siparis_id = s.id
                JOIN urunler u ON d.urun_kodu = u.urun_kodu
                WHERE s.musteri_id = ?
                  AND d.durum = 'tamamlandi'
                  AND d.teslimat_id IS NULL
                ORDER BY d.id ASC
            ");
            $stmt->execute([$musteri_id]);
            $kalemler = $stmt->fetchAll();

            if (empty($kalemler)) {
                echo json_encode(['status' => 'error', 'message' => 'Bu müşteri için fişe bağlanacak teslim edilmiş kalem bulunamadı']);
                exit;
            }

            $pdo->beginTransaction();

            // Fiyat hesapla
            $toplam_brut = 0;
            foreach ($kalemler as $k) {
                $toplam_brut += floatval($k['teslim_edilen_kg']) * floatval($k['usd_fiyat']);
            }
            $toplam_iskonto = $toplam_brut * ($iskonto_orani / 100);
            $toplam_usd = round($toplam_brut - $toplam_iskonto, 2);
            $toplam_tl = round($toplam_usd * $usd_kuru, 2);

            // teslimatlar tablosuna yeni fiş kaydı aç
            $insT = $pdo->prepare("INSERT INTO teslimatlar (musteri_id, olusturma_tarihi, durum, usd_kuru, toplam_usd, toplam_tl)
                VALUES (?, NOW(), 'kapali', ?, ?, ?)");
            $insT->execute([$musteri_id, $usd_kuru, $toplam_usd, $toplam_tl]);
            $fis_id = $pdo->lastInsertId();

            // Her kalemi teslimat_detaylari'na ekle (fiyat snapshot ile) ve siparis_detaylari.teslimat_id'yi güncelle
            $insTD = $pdo->prepare("INSERT INTO teslimat_detaylari (teslimat_id, urun_kodu, teslim_edilen_kg, birim_usd_fiyat, iskonto_orani) VALUES (?, ?, ?, ?, ?)");
            $updSD = $pdo->prepare("UPDATE siparis_detaylari SET teslimat_id = ? WHERE id = ?");

            foreach ($kalemler as $k) {
                $insTD->execute([$fis_id, $k['urun_kodu'], $k['teslim_edilen_kg'], $k['usd_fiyat'], $iskonto_orani]);
                $updSD->execute([$fis_id, $k['detay_id']]);
            }

            $toplam_kg = array_sum(array_column($kalemler, 'teslim_edilen_kg'));
            $urun_sayisi = count($kalemler);

            $pdo->commit();
            echo json_encode([
                'status' => 'success',
                'fis_id' => $fis_id,
                'toplam_kg' => $toplam_kg,
                'toplam_usd' => $toplam_usd,
                'toplam_tl' => $toplam_tl,
                'usd_kuru' => $usd_kuru,
                'urun_sayisi' => $urun_sayisi,
                'mesaj' => "Fiş #{$fis_id} oluşturuldu. {$urun_sayisi} kalem ({$toplam_kg} KG) — Net: \${$toplam_usd} / ₺{$toplam_tl}"
            ]);
        } catch (PDOException $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}

function collect_max_durum($durumlar, $oncelik)
{
    return array_reduce($durumlar, function ($carry, $d) use ($oncelik) {
        return ($oncelik[$d] ?? 0) > ($oncelik[$carry] ?? 0) ? $d : $carry;
    }, 'beklemede');
}
?>