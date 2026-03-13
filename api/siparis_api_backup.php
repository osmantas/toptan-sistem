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

            $sql = "SELECT d.id as detay_id, d.siparis_id, d.istenen_kg, d.teslim_edilen_kg, d.kalan_kg,
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

    } elseif ($action === 'tedarik_musteriler' && isset($_GET['urun_kodu'])) {
        try {
            $sql = "SELECT d.id as detay_id, d.siparis_id, d.istenen_kg, d.teslim_edilen_kg, d.kalan_kg,
                        d.durum, s.tarih,
                        m.id as musteri_id, m.firma_adi
                    FROM siparis_detaylari d
                    JOIN siparisler s ON d.siparis_id = s.id
                    JOIN musteriler m ON s.musteri_id = m.id
                    WHERE d.urun_kodu = :urun_kodu AND d.durum = 'tedarik' AND d.kalan_kg > 0
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
                        u.usd_fiyat, COALESCE(m.iskonto_orani, 0) as iskonto_orani
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

            $sql = "SELECT t.id as fis_id, t.musteri_id, m.firma_adi, m.telefon,
                        t.olusturma_tarihi,
                        SUM(td.teslim_edilen_kg) as toplam_kg,
                        COUNT(td.id) as urun_sayisi
                    FROM teslimatlar t
                    JOIN musteriler m ON t.musteri_id = m.id
                    LEFT JOIN teslimat_detaylari td ON td.teslimat_id = t.id
                    $where
                    GROUP BY t.id, t.musteri_id, m.firma_adi, m.telefon, t.olusturma_tarihi
                    ORDER BY t.olusturma_tarihi DESC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode($stmt->fetchAll());
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

        // Tek fişin tüm kalemlerini getir (yeniden yazdırma için)
    } elseif ($action === 'teslimat_fis_detay' && isset($_GET['fis_id'])) {
        try {
            $stmt = $pdo->prepare("
                SELECT
                    t.id as fis_id, m.firma_adi, m.telefon, t.olusturma_tarihi,
                    td.id as td_id, td.urun_kodu, u.urun_adi, td.teslim_edilen_kg
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

        // Tüm detay ID'lerini durum bazında getir (toplu işlemler için)
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
                (SELECT SUM(istened_kg) FROM siparis_detaylari WHERE siparis_id = s.id) as toplam_istened
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

        // Teslim et (kısmi/tam teslimat — kalan_kg'ye göre durum belirlenir)
    } elseif (isset($input['action']) && $input['action'] === 'teslim_et') {
        try {
            $detay_id = $input['detay_id'];
            $miktar = floatval($input['miktar'] ?? 0);
            if ($miktar <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'Geçersiz miktar']);
                exit;
            }

            // Önce mevcut kalan_kg kontrolü
            $check = $pdo->prepare("SELECT kalan_kg, siparis_id FROM siparis_detaylari WHERE id = ?");
            $check->execute([$detay_id]);
            $row = $check->fetch();
            if (!$row) {
                echo json_encode(['status' => 'error', 'message' => 'Sipariş detayı bulunamadı']);
                exit;
            }
            $kalan = floatval($row['kalan_kg']);
            if ($miktar > $kalan + 0.01) { // 0.01 tolerans
                echo json_encode(['status' => 'error', 'message' => "Teslim miktarı ({$miktar} KG) kalan miktardan ({$kalan} KG) fazla olamaz."]);
                exit;
            }

            $pdo->beginTransaction();

            $yeniKalan = round($kalan - $miktar, 2);
            // kalan_kg 0 veya altına düşerse tamamlandi, değilse geldi kalır
            $yeniDurum = ($yeniKalan <= 0.01) ? 'tamamlandi' : 'geldi';
            if ($yeniKalan < 0)
                $yeniKalan = 0;

            $stmt = $pdo->prepare("UPDATE siparis_detaylari 
                SET teslim_edilen_kg = teslim_edilen_kg + ?,
                    kalan_kg = ?,
                    durum = ?
                WHERE id = ?");
            $stmt->execute([$miktar, $yeniKalan, $yeniDurum, $detay_id]);

            // Sipariş genel durumunu senkronize et
            $siparis_id = $row['siparis_id'];
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
                'yeni_kalan' => $yeniKalan,
                'yeni_durum' => $yeniDurum,
                'mesaj' => $yeniDurum === 'tamamlandi'
                    ? "Sipariş tamamen teslim edildi ({$miktar} KG)."
                    : "Kısmi teslim yapıldı: {$miktar} KG teslim edildi, {$yeniKalan} KG kaldı."
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
            // istened_kg ve kalan_kg → gelen miktar olarak set edilir
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
            if (!$musteri_id) {
                echo json_encode(['status' => 'error', 'message' => 'Geçersiz müşteri ID']);
                exit;
            }

            // Bu müşterinin fişe bağlanmamış tamamlanmış kalemlerini al
            $stmt = $pdo->prepare("
                SELECT d.id as detay_id, d.urun_kodu, d.teslim_edilen_kg
                FROM siparis_detaylari d
                JOIN siparisler s ON d.siparis_id = s.id
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

            // teslimatlar tablosuna yeni fiş kaydı aç
            $insT = $pdo->prepare("INSERT INTO teslimatlar (musteri_id, olusturma_tarihi, durum, usd_kuru, toplam_usd, toplam_tl)
                VALUES (?, NOW(), 'kapali', 1, 0, 0)");
            $insT->execute([$musteri_id]);
            $fis_id = $pdo->lastInsertId();

            // Her kalemi teslimat_detaylari'na ekle ve siparis_detaylari.teslimat_id'yi güncelle
            $insTD = $pdo->prepare("INSERT INTO teslimat_detaylari (teslimat_id, urun_kodu, teslim_edilen_kg) VALUES (?, ?, ?)");
            $updSD = $pdo->prepare("UPDATE siparis_detaylari SET teslimat_id = ? WHERE id = ?");

            foreach ($kalemler as $k) {
                $insTD->execute([$fis_id, $k['urun_kodu'], $k['teslim_edilen_kg']]);
                $updSD->execute([$fis_id, $k['detay_id']]);
            }

            $toplam_kg = array_sum(array_column($kalemler, 'teslim_edilen_kg'));
            $urun_sayisi = count($kalemler);

            $pdo->commit();
            echo json_encode([
                'status' => 'success',
                'fis_id' => $fis_id,
                'toplam_kg' => $toplam_kg,
                'urun_sayisi' => $urun_sayisi,
                'mesaj' => "Fiş #{$fis_id} oluşturuldu. {$urun_sayisi} kalem ({$toplam_kg} KG) arşivlendi."
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