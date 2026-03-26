<?php
session_start();

// Eğer giriş yapılmamışsa login sayfasına yönlendir
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetici Paneli - AKSA Toptan</title>
    <style>
        :root {
            --primary-bg: #1e1e2d;
            --sidebar-bg: #151521;
            --text-main: #b5b5c3;
            --text-light: #ffffff;
            --accent: #3699ff;
            --accent-hover: #1b84ff;
            --danger: #f64e60;
            --success: #1bc5bd;
            --card-bg: #1e1e2d;
            --border-color: #2b2b40;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #151521;
            color: var(--text-main);
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 260px;
            background-color: var(--sidebar-bg);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
        }

        .logo-container {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
        }

        .logo-container h2 {
            color: var(--text-light);
            font-size: 1.5rem;
            letter-spacing: 1px;
        }

        .nav-menu {
            list-style: none;
            padding: 20px 0;
            flex: 1;
        }

        .nav-item {
            padding: 12px 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            font-size: 1.1rem;
            color: var(--text-main);
        }

        .nav-item:hover,
        .nav-item.active {
            background-color: #1b1b29;
            color: var(--accent);
            border-left: 4px solid var(--accent);
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            background-color: var(--primary-bg);
            overflow-y: auto;
        }

        .header {
            padding: 20px 30px;
            background-color: var(--sidebar-bg);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            color: var(--text-light);
            font-size: 1.4rem;
        }

        .content-area {
            padding: 30px;
            flex: 1;
        }

        /* Card Form & Table Styles */
        .card {
            background-color: var(--sidebar-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .card-title {
            color: var(--text-light);
            font-size: 1.2rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: var(--text-light);
            font-size: 0.9rem;
        }

        input[type="text"],
        input[type="number"],
        input[type="tel"] {
            width: 100%;
            padding: 10px 15px;
            background-color: #1b1b29;
            border: 1px solid var(--border-color);
            color: var(--text-light);
            border-radius: 6px;
            outline: none;
            transition: border 0.3s;
        }

        input:focus {
            border-color: var(--accent);
        }

        button {
            padding: 10px 20px;
            background-color: var(--accent);
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.3s;
        }

        button:hover {
            background-color: var(--accent-hover);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        th {
            color: var(--text-light);
            background-color: rgba(255, 255, 255, 0.02);
            font-weight: 500;
        }

        td {
            color: var(--text-main);
        }

        tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.02);
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            background-color: var(--success);
            color: white;
            border-radius: 6px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            display: none;
            z-index: 1000;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1050;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
        }

        .modal-content {
            background-color: var(--sidebar-bg);
            margin: 10% auto;
            padding: 25px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            position: relative;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
        }

        .close-btn {
            color: var(--text-main);
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s;
            line-height: 1;
        }

        .close-btn:hover {
            color: var(--danger);
        }

        /* === FILTER BAR === */
        .filter-bar {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            padding: 16px 20px;
            background: linear-gradient(135deg, rgba(54, 153, 255, 0.08), rgba(27, 197, 189, 0.06));
            border: 1px solid var(--border-color);
            border-radius: 10px;
            margin-bottom: 24px;
        }

        .filter-bar label {
            font-size: 0.85rem;
            color: var(--text-main);
            margin-bottom: 0;
            white-space: nowrap;
        }

        .day-btn {
            padding: 7px 14px;
            font-size: 0.82rem;
            border: 1px solid var(--border-color);
            background: rgba(255, 255, 255, 0.04);
            color: var(--text-main);
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.25s;
        }

        .day-btn:hover {
            background: rgba(54, 153, 255, 0.15);
            color: var(--accent);
            border-color: var(--accent);
        }

        .day-btn.active {
            background: var(--accent);
            color: #fff;
            border-color: var(--accent);
            box-shadow: 0 2px 8px rgba(54, 153, 255, 0.35);
        }

        .filter-select {
            padding: 7px 14px;
            background: rgba(0, 0, 0, 0.25);
            border: 1px solid var(--border-color);
            color: var(--text-light);
            border-radius: 6px;
            font-size: 0.85rem;
            outline: none;
            min-width: 180px;
        }

        .filter-select:focus {
            border-color: var(--accent);
        }

        /* === ACCORDION PRODUCT CARDS === */
        .product-accordion {
            background: var(--sidebar-bg);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            margin-bottom: 12px;
            overflow: hidden;
            transition: all 0.3s;
        }

        .product-accordion:hover {
            border-color: rgba(54, 153, 255, 0.4);
        }

        .accordion-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            cursor: pointer;
            transition: background 0.2s;
            user-select: none;
        }

        .accordion-header:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        .accordion-header .product-code {
            font-weight: 700;
            font-size: 1.05rem;
            color: var(--accent);
            min-width: 120px;
        }

        .accordion-header .product-name {
            color: var(--text-light);
            flex: 1;
            margin-left: 16px;
            font-size: 0.95rem;
        }

        .accordion-header .product-stats {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .stat-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .stat-badge.kg {
            background: rgba(246, 78, 96, 0.12);
            color: var(--danger);
        }

        .stat-badge.customers {
            background: rgba(54, 153, 255, 0.12);
            color: var(--accent);
        }

        .accordion-chevron {
            font-size: 1.2rem;
            color: var(--text-main);
            transition: transform 0.3s;
            margin-left: 16px;
        }

        .product-accordion.open .accordion-chevron {
            transform: rotate(180deg);
        }

        .accordion-body {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease;
            border-top: 0 solid var(--border-color);
        }

        .product-accordion.open .accordion-body {
            max-height: 2000px;
            border-top-width: 1px;
        }

        .accordion-body table {
            margin: 0;
        }

        .accordion-body th {
            font-size: 0.82rem;
            padding: 10px 15px;
        }

        .accordion-body td {
            font-size: 0.88rem;
            padding: 10px 15px;
        }

        /* === STATUS BADGES === */
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        .status-badge.beklemede {
            background: rgba(255, 184, 34, 0.15);
            color: #ffb822;
        }

        .status-badge.tedarik {
            background: rgba(102, 16, 242, 0.15);
            color: #a855f7;
        }

        .status-badge.geldi {
            background: rgba(253, 126, 20, 0.15);
            color: #fd7e14;
        }

        .status-badge.tamamlandi {
            background: rgba(27, 197, 189, 0.15);
            color: var(--success);
        }

        .status-badge.iptal {
            background: rgba(246, 78, 96, 0.15);
            color: var(--danger);
        }

        /* === WORKFLOW BUTTONS === */
        .btn-sm {
            padding: 4px 10px;
            font-size: 0.78rem;
            border-radius: 5px;
            margin-right: 4px;
            cursor: pointer;
            border: none;
            color: #fff;
            transition: all 0.2s;
        }

        .btn-tedarik {
            background: #6610f2;
        }

        .btn-tedarik:hover {
            background: #5a0dd6;
        }

        .btn-geldi {
            background: #fd7e14;
        }

        .btn-geldi:hover {
            background: #e66d08;
        }

        .btn-teslim {
            background: var(--success);
        }

        .btn-teslim:hover {
            background: #17a89f;
        }

        .btn-iptal {
            background: var(--danger);
        }

        .btn-iptal:hover {
            background: #d43b4c;
        }

        /* === SECTION DIVIDER === */
        .section-title {
            color: var(--text-light);
            font-size: 1.1rem;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title::before {
            content: '';
            width: 4px;
            height: 20px;
            background: var(--accent);
            border-radius: 2px;
        }

        .section-divider {
            border: none;
            border-top: 1px solid var(--border-color);
            margin: 30px 0;
        }

        /* === SUB TABS === */
        .sub-tabs {
            display: flex;
            gap: 0;
            margin-bottom: 24px;
            border-bottom: 2px solid var(--border-color);
        }

        .sub-tab-btn {
            padding: 12px 24px;
            background: transparent;
            border: none;
            border-bottom: 3px solid transparent;
            color: var(--text-main);
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.25s;
            margin-bottom: -2px;
            border-radius: 0;
        }

        .sub-tab-btn:hover {
            color: var(--accent);
            background: rgba(54, 153, 255, 0.06);
        }

        .sub-tab-btn.active {
            color: var(--accent);
            border-bottom-color: var(--accent);
            font-weight: 600;
            background: rgba(54, 153, 255, 0.08);
        }

        /* === SEARCH BAR === */
        .search-bar-wrap {
            margin-bottom: 16px;
            position: relative;
        }

        .search-bar-wrap input {
            width: 100%;
            max-width: 320px;
            padding: 9px 16px 9px 38px;
            background: rgba(0, 0, 0, 0.25);
            border: 1px solid var(--border-color);
            border-radius: 30px;
            color: var(--text-light);
            font-size: 0.88rem;
            outline: none;
            transition: border 0.3s;
        }

        .search-bar-wrap input:focus {
            border-color: var(--accent);
        }

        .search-bar-wrap .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-main);
            font-size: 0.9rem;
            pointer-events: none;
        }

        /* === BULK ACTION BAR === */
        .bulk-bar {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 16px;
            padding: 10px 16px;
            background: rgba(54, 153, 255, 0.05);
            border: 1px dashed rgba(54, 153, 255, 0.3);
            border-radius: 8px;
        }

        .bulk-bar span {
            font-size: 0.85rem;
            color: var(--text-main);
        }

        .btn-bulk {
            padding: 6px 14px;
            font-size: 0.82rem;
            border-radius: 6px;
            cursor: pointer;
            border: none;
            color: #fff;
            transition: all 0.2s;
        }

        .btn-bulk-primary {
            background: var(--accent);
        }

        .btn-bulk-primary:hover {
            background: var(--accent-hover);
        }

        .btn-bulk-danger {
            background: var(--danger);
        }

        .btn-bulk-danger:hover {
            background: #d43b4c;
        }

        .btn-bulk-success {
            background: var(--success);
        }

        .btn-bulk-success:hover {
            background: #17a89f;
        }

        .btn-bulk-secondary {
            background: #6c757d;
        }

        .btn-bulk-secondary:hover {
            background: #5a6268;
        }

        /* === CHECKBOXES === */
        .row-check {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: var(--accent);
        }

        /* === TESLIMAT BADGE === */
        .status-badge.geldi {
            background: rgba(253, 126, 20, 0.15);
            color: #fd7e14;
        }

        .status-badge.tamamlandi {
            background: rgba(27, 197, 189, 0.15);
            color: var(--success);
        }

        /* === DATE FILTER CHIPS === */
        .date-chip {
            padding: 6px 14px;
            border-radius: 20px;
            border: 1px solid var(--border-color);
            background: rgba(255, 255, 255, 0.04);
            color: var(--text-main);
            cursor: pointer;
            font-size: 0.82rem;
            font-weight: 500;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .date-chip:hover {
            background: rgba(54, 153, 255, 0.12);
            border-color: var(--accent);
            color: var(--text-light);
        }

        .date-chip.active {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
            box-shadow: 0 2px 8px rgba(54, 153, 255, 0.3);
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <div class="logo-container">
            <h2>AKSA TOPTAN</h2>
        </div>
        <ul class="nav-menu">
            <li class="nav-item" onclick="loadPage('dashboard', this)">Dashboard</li>
            <li class="nav-item" onclick="loadPage('musteriler', this)">Müşteriler</li>
            <li class="nav-item" onclick="loadPage('urunler', this)">Ürünler</li>
            <li class="nav-item active" onclick="loadPage('siparisler', this)">Siparişler</li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1 id="page-title">Sipariş Yönetimi</h1>
            <div style="display:flex; align-items:center; gap: 20px;">
                <div id="usd-rate"
                    style="color:var(--success); font-weight:bold; font-size:1.1rem; background:rgba(27,197,189,0.1); padding:5px 15px; border-radius:5px;">
                    USD Kuru: Yükleniyor...</div>
                <div>Hoş Geldiniz, Admin</div>
            </div>
        </div>
        <div class="content-area" id="content-area">
            <!-- Dynamic Content Load Here -->
        </div>
    </div>

    <div id="notification" class="notification">İşlem başarılı!</div>

    <script>
        let currentUsdRate = 0;
        let lastOpenTeslimatUrun = null;
        let nextFocusId = null;

        // Sayfa yüklendiğinde varsayılan sekmeyi aç
        document.addEventListener('DOMContentLoaded', () => {
            fetchUsdRate();
            loadSiparisler();
        });

        async function fetchUsdRate() {
            try {
                const response = await fetch('../api/kur_api.php');
                const data = await response.json();
                if (data.status === 'success') {
                    currentUsdRate = parseFloat(data.usd_kuru);
                    document.getElementById('usd-rate').innerText = `1 USD = ${currentUsdRate.toFixed(2)} ₺`;
                }
            } catch (err) {
                console.error('Kur alınırken hata:', err);
                document.getElementById('usd-rate').innerText = 'Kur alınamadı';
            }
        }

        // Menü geçiş sistemi
        function loadPage(page, el) {
            // Aktif menü öğesi stili güncelle
            document.querySelectorAll('.nav-item').forEach(item => item.classList.remove('active'));
            if (el) el.classList.add('active');

            const contentArea = document.getElementById('content-area');
            const pageTitle = document.getElementById('page-title');

            contentArea.innerHTML = '<div style="text-align:center; padding:50px;">Yükleniyor...</div>';

            if (page === 'dashboard') {
                pageTitle.textContent = "Dashboard";
                loadDashboard();
            } else if (page === 'musteriler') {
                pageTitle.textContent = "Müşteri Yönetimi";
                loadMusteriler();
            } else if (page === 'urunler') {
                pageTitle.textContent = "Ürün Yönetimi";
                loadUrunler();
            } else if (page === 'siparisler') {
                pageTitle.textContent = "Sipariş Yönetimi";
                loadSiparisler();
            } else if (page === 'teslimatlar') {
                pageTitle.textContent = "Taksim / Teslimatlar";
                loadTeslimatlar();
            }
        }

        // --- DASHBOARD ---
        function loadDashboard() {
            const html = `
                <div class="card">
                    <div class="card-title">Dashboard</div>
                    <div style="font-size: 1.1rem; line-height: 1.6; color: var(--text-main);">
                        <h3>Hoş Geldiniz!</h3>
                        <p style="margin-top: 10px;">Sol menüdeki sekmeleri kullanarak sistemdeki bilgilerinizi yönetebilirsiniz.</p>
                        <ul style="margin-top: 15px; margin-left: 20px;">
                            <li style="margin-bottom: 8px;"><strong>Müşteriler:</strong> Sisteme yeni bayi/müşteri ekleyebilir ve listeleyebilirsiniz.</li>
                            <li style="margin-bottom: 8px;"><strong>Ürünler:</strong> Satışı yapılan ürünleri ve fiyatlarını görebilirsiniz.</li>
                            <li style="margin-bottom: 8px;"><strong>Siparişler:</strong> Gelen sipariş taleplerini inceleyip onaylayabilir veya iptal edebilirsiniz.</li>
                            <li><strong>Taksim / Teslimatlar:</strong> Teslim edilecek hazır ürünlerin müşterilere dağıtımını yapabilirsiniz.</li>
                        </ul>
                    </div>
                </div>
            `;
            document.getElementById('content-area').innerHTML = html;
        }

        // --- MÜŞTERİ YÖNETİMİ ---
        function loadMusteriler() {
            const html = `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2 style="color: var(--text-light); font-size: 1.2rem;">Müşteri Listesi</h2>
                    <button onclick="openMusteriModal()" style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 1.2rem;">+</span> Yeni Müşteri Ekle
                    </button>
                </div>
                
                <div class="card" style="margin-top: 0;">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Firma Adı</th>
                                <th>Kullanıcı Adı</th>
                                <th>Telefon</th>
                                <th>İskonto</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody id="musteriTableBody">
                            <tr><td colspan="6" style="text-align:center;">Yükleniyor...</td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- Yeni Müşteri Ekle Modal -->
                <div id="yeniMusteriModal" class="modal">
                    <div class="modal-content">
                        <span class="close-btn" onclick="closeMusteriModal()">&times;</span>
                        <div class="card-title" style="margin-top: 10px; border-bottom: none; margin-bottom: 25px;">Yeni Müşteri Ekle</div>
                        <form id="musteriForm" onsubmit="saveMusteri(event)">
                            <div class="form-group">
                                <label>Firma Adı</label>
                                <input type="text" id="m_firma_adi" required placeholder="Firma adını giriniz">
                            </div>
                            <div class="form-group">
                                <label>Kullanıcı Adı</label>
                                <input type="text" id="m_kullanici_adi" required placeholder="Giriş için kullanıcı adı">
                            </div>
                            <div class="form-group">
                                <label>Şifre</label>
                                <input type="text" id="m_sifre" required placeholder="Giriş için şifre">
                            </div>
                            <div class="form-group">
                                <label>Telefon Numarası</label>
                                <input type="tel" id="m_telefon" required placeholder="0500 000 00 00">
                            </div>
                            <div class="form-group">
                                <label>İskonto Oranı (%)</label>
                                <input type="number" step="0.01" id="m_iskonto" value="0.00" onfocus="this.select()" required placeholder="Örn: 10.5">
                            </div>
                            <button type="submit" style="width: 100%; margin-top: 15px; padding: 12px;">Müşteriyi Kaydet</button>
                        </form>
                    </div>
                </div>

                <!-- Müşteri Düzenle Modal -->
                <div id="editMusteriModal" class="modal">
                    <div class="modal-content">
                        <span class="close-btn" onclick="closeEditMusteriModal()">&times;</span>
                        <div class="card-title" style="margin-top: 10px; border-bottom: none; margin-bottom: 25px;">Müşteri Bilgilerini Güncelle</div>
                        <form id="editMusteriForm" onsubmit="updateMusteri(event)">
                            <input type="hidden" id="edit_m_id">
                            <div class="form-group">
                                <label>Firma Adı</label>
                                <input type="text" id="edit_m_firma_adi" required>
                            </div>
                            <div class="form-group">
                                <label>Kullanıcı Adı</label>
                                <input type="text" id="edit_m_kullanici_adi" required>
                            </div>
                            <div class="form-group">
                                <label>Şifre (Değiştirmek istemiyorsanız boş bırakın)</label>
                                <input type="text" id="edit_m_sifre" placeholder="Yeni şifre">
                            </div>
                            <div class="form-group">
                                <label>Telefon Numarası</label>
                                <input type="tel" id="edit_m_telefon" required>
                            </div>
                            <div class="form-group">
                                <label>İskonto Oranı (%)</label>
                                <input type="number" step="0.01" id="edit_m_iskonto" onfocus="this.select()" required>
                            </div>
                            <button type="submit" style="width: 100%; margin-top: 15px; padding: 12px; background-color: var(--success);">Değişiklikleri Kaydet</button>
                        </form>
                    </div>
                </div>
            `;
            document.getElementById('content-area').innerHTML = html;
            fetchMusteriler();
        }

        function openMusteriModal() {
            document.getElementById('yeniMusteriModal').style.display = 'block';
        }

        function closeMusteriModal() {
            document.getElementById('yeniMusteriModal').style.display = 'none';
        }

        async function editMusteri(id) {
            try {
                const response = await fetch('../api/musteri_api.php?id=' + id);
                const m = await response.json();

                document.getElementById('edit_m_id').value = m.id;
                document.getElementById('edit_m_firma_adi').value = m.firma_adi;
                document.getElementById('edit_m_kullanici_adi').value = m.kullanici_adi;
                document.getElementById('edit_m_sifre').value = ''; // Şifreyi boş bırak
                document.getElementById('edit_m_telefon').value = m.telefon;
                document.getElementById('edit_m_iskonto').value = m.iskonto_orani;

                openEditMusteriModal();
            } catch (err) {
                alert('Müşteri bilgileri alınırken hata oluştu.');
            }
        }

        function openEditMusteriModal() {
            document.getElementById('editMusteriModal').style.display = 'block';
        }

        function closeEditMusteriModal() {
            document.getElementById('editMusteriModal').style.display = 'none';
        }

        async function updateQuickDiscount(id, val) {
            try {
                // Sadece iskontoyu güncellemek için yine PUT kullanıyoruz. 
                // Diğer alanları mevcut değerleriyle göndermek yerine API'yi 
                // sadece gelen alanları güncelleyecek şekilde de yapabilirdik ama 
                // mevcut PUT yapımızı bozmadan tekil fetch yapıp güncellemek daha güvenli.

                // Önce mevcut veriyi çekelim ki diğer alanlar silinmesin
                const getRes = await fetch('../api/musteri_api.php?id=' + id);
                const m = await getRes.json();

                const payload = {
                    id: id,
                    firma_adi: m.firma_adi,
                    kullanici_adi: m.kullanici_adi,
                    telefon: m.telefon,
                    iskonto_orani: val
                };

                const response = await fetch('../api/musteri_api.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();
                if (result.status === 'success') {
                    showNotification('İskonto oranı anlık güncellendi!');
                } else {
                    alert('Hata: ' + result.message);
                }
            } catch (err) {
                alert('İskonto güncellenirken hata oluştu.');
            }
        }

        async function updateMusteri(e) {
            e.preventDefault();
            const payload = {
                id: document.getElementById('edit_m_id').value,
                firma_adi: document.getElementById('edit_m_firma_adi').value,
                kullanici_adi: document.getElementById('edit_m_kullanici_adi').value,
                sifre: document.getElementById('edit_m_sifre').value,
                telefon: document.getElementById('edit_m_telefon').value,
                iskonto_orani: document.getElementById('edit_m_iskonto').value
            };

            try {
                const response = await fetch('../api/musteri_api.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();
                if (result.status === 'success') {
                    showNotification('Müşteri başarıyla güncellendi!');
                    fetchMusteriler();
                    closeEditMusteriModal();
                } else {
                    alert('Hata: ' + result.message);
                }
            } catch (err) {
                alert('Sistemsel hata oluştu.');
            }
        }

        async function fetchMusteriler() {
            try {
                const response = await fetch('../api/musteri_api.php');
                const data = await response.json();
                const tbody = document.getElementById('musteriTableBody');
                tbody.innerHTML = '';

                if (!Array.isArray(data) || data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;">Kayıtlı müşteri bulunamadı.</td></tr>';
                    return;
                }

                data.forEach(m => {
                    tbody.innerHTML += `
                        <tr>
                            <td>#${m.id}</td>
                            <td>${m.firma_adi}</td>
                            <td>${m.kullanici_adi}</td>
                            <td>${m.telefon}</td>
                            <td>
                                % <input type="number" step="0.01" value="${m.iskonto_orani}" 
                                    style="width: 70px; padding: 4px; border: 1px solid rgba(27,197,189,0.3); border-radius: 4px; font-weight: bold; color: var(--success); background: rgba(27,197,189,0.05);"
                                    onchange="updateQuickDiscount(${m.id}, this.value)">
                            </td>
                            <td>
                                <button onclick="editMusteri(${m.id})" style="padding: 5px 10px; background-color: var(--primary); font-size: 0.85rem;">Düzenle</button>
                            </td>
                        </tr>
                    `;
                });
            } catch (err) {
                console.error('Müşteriler çekilirken hata oluştu:', err);
            }
        }

        async function saveMusteri(e) {
            e.preventDefault();
            const payload = {
                firma_adi: document.getElementById('m_firma_adi').value,
                kullanici_adi: document.getElementById('m_kullanici_adi').value,
                sifre: document.getElementById('m_sifre').value,
                telefon: document.getElementById('m_telefon').value,
                iskonto_orani: document.getElementById('m_iskonto').value
            };

            try {
                const response = await fetch('../api/musteri_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();
                if (result.status === 'success') {
                    showNotification('Müşteri başarıyla eklendi!');
                    document.getElementById('musteriForm').reset();
                    fetchMusteriler(); // listeyi güncelle
                    closeMusteriModal();
                } else {
                    alert('Hata: ' + result.message);
                }
            } catch (err) {
                console.error(err);
                alert('Sistemsel bir hata oluştu');
            }
        }

        // --- ÜRÜN YÖNETİMİ ---
        function loadUrunler() {
            const html = `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2 style="color: var(--text-light); font-size: 1.2rem;">Ürün Listesi</h2>
                    <button onclick="openUrunModal()" style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 1.2rem;">+</span> Yeni Ürün Ekle
                    </button>
                </div>

                <div class="card" style="margin-top: 0; padding-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
                        <div class="search-bar-wrap" style="margin: 0; min-width: 250px;">
                            <span class="search-icon">🔍</span>
                            <input type="text" id="adminUrunArama" placeholder="Ürün kodu veya adı ile ara..." oninput="filterAdminUrunler()" style="margin: 0;">
                        </div>
                        <div>
                            <select id="adminUrunLimit" onchange="changeAdminUrunLimit()" style="padding: 8px; border-radius: 4px; border: 1px solid var(--border); background: #1a1a24; color: var(--text-main);">
                                <option value="10">10 Kayıt</option>
                                <option value="20">20 Kayıt</option>
                                <option value="50">50 Kayıt</option>
                                <option value="100">100 Kayıt</option>
                                <option value="5000">Tümü</option>
                            </select>
                        </div>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Kodu</th>
                                <th>Adı</th>
                                <th>Fiyat (USD/KG)</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody id="urunTableBody">
                            <tr><td colspan="4" style="text-align:center;">Yükleniyor...</td></tr>
                        </tbody>
                    </table>
                    <div id="adminUrunPagination" style="margin-top: 15px; display: flex; justify-content: center; gap: 5px; flex-wrap: wrap;"></div>
                </div>

                <!-- Yeni Ürün Ekle Modal -->
                <div id="yeniUrunModal" class="modal">
                    <div class="modal-content">
                        <span class="close-btn" onclick="closeUrunModal()">&times;</span>
                        <div class="card-title" style="margin-top: 10px; border-bottom: none; margin-bottom: 25px;">Yeni Ürün (Esans) Ekle</div>
                        <form id="urunForm" onsubmit="saveUrun(event)">
                            <div class="form-group">
                                <label>Ürün Kodu</label>
                                <input type="text" id="u_urun_kodu" required placeholder="Benzersiz ürün kodu (Örn: A-01)">
                            </div>
                            <div class="form-group">
                                <label>Ürün Adı</label>
                                <input type="text" id="u_urun_adi" required placeholder="Ürün adı (Örn: Aventios)">
                            </div>
                            <div class="form-group">
                                <label>Birim USD Fiyatı (KG)</label>
                                <input type="number" step="0.01" id="u_usd_fiyat" onfocus="this.select()" required placeholder="0.00">
                            </div>
                            <button type="submit" style="width: 100%; margin-top: 15px; padding: 12px;">Ürünü Kaydet</button>
                        </form>
                    </div>
                </div>

                <!-- Ürün Düzenle Modal -->
                <div id="editUrunModal" class="modal">
                    <div class="modal-content">
                        <span class="close-btn" onclick="closeEditUrunModal()">&times;</span>
                        <div class="card-title" style="margin-top: 10px; border-bottom: none; margin-bottom: 25px;">Ürün Bilgilerini Güncelle</div>
                        <form id="editUrunForm" onsubmit="updateUrun(event)">
                            <input type="hidden" id="edit_u_id">
                            <div class="form-group">
                                <label>Ürün Kodu</label>
                                <input type="text" id="edit_u_urun_kodu" required>
                            </div>
                            <div class="form-group">
                                <label>Ürün Adı</label>
                                <input type="text" id="edit_u_urun_adi" required>
                            </div>
                            <div class="form-group">
                                <label>Birim USD Fiyatı (KG)</label>
                                <input type="number" step="0.01" id="edit_u_usd_fiyat" onfocus="this.select()" required>
                            </div>
                            <button type="submit" style="width: 100%; margin-top: 15px; padding: 12px; background-color: var(--success);">Değişiklikleri Kaydet</button>
                        </form>
                    </div>
                </div>
            `;
            document.getElementById('content-area').innerHTML = html;
            fetchUrunler();
        }

        function openUrunModal() {
            document.getElementById('yeniUrunModal').style.display = 'block';
        }

        function closeUrunModal() {
            document.getElementById('yeniUrunModal').style.display = 'none';
        }

        // Modal dışına tıklandığında kapatma
        window.addEventListener('click', function (event) {
            const urunModal = document.getElementById('yeniUrunModal');
            if (event.target === urunModal) {
                closeUrunModal();
            }
            const musteriModal = document.getElementById('yeniMusteriModal');
            if (event.target === musteriModal) {
                closeMusteriModal();
            }
            const editMusteriModal = document.getElementById('editMusteriModal');
            if (event.target === editMusteriModal) {
                closeEditMusteriModal();
            }
        });

        let adminUrunPage = 1;

        function changeAdminUrunLimit() {
            adminUrunPage = 1;
            fetchUrunler();
        }

        let adminSearchTimeout = null;
        function filterAdminUrunler() {
            if (adminSearchTimeout) clearTimeout(adminSearchTimeout);
            adminSearchTimeout = setTimeout(() => {
                adminUrunPage = 1;
                fetchUrunler();
            }, 300);
        }

        function updateAdminUrunPage(p) {
            adminUrunPage = p;
            fetchUrunler();
        }

        function renderAdminUrunPagination(totalPages) {
            const container = document.getElementById('adminUrunPagination');
            if (totalPages <= 1) {
                container.innerHTML = '';
                return;
            }

            let html = '';
            const baseStyle = "padding: 5px 12px; border-radius: 6px; font-size: 0.9rem; cursor: pointer; transition: all 0.2s;";
            
            const prevDisabled = adminUrunPage === 1;
            html += `<button style="${baseStyle} border: 1px solid var(--border); background: transparent; color: var(--text-main); ${prevDisabled ? 'opacity:0.5; cursor:default;' : ''}" 
                        onclick="${!prevDisabled ? `updateAdminUrunPage(${adminUrunPage - 1})` : ''}" ${prevDisabled ? 'disabled' : ''}>◀ Önceki</button>`;

            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= adminUrunPage - 2 && i <= adminUrunPage + 2)) {
                    const isActive = (i === adminUrunPage);
                    html += `<button style="${baseStyle} background: ${isActive ? 'var(--accent)' : 'transparent'}; border: 1px solid ${isActive ? 'var(--accent)' : 'var(--border)'}; color: ${isActive ? '#fff' : 'var(--text-main)'}; font-weight: ${isActive ? 'bold' : 'normal'};" onclick="updateAdminUrunPage(${i})">${i}</button>`;
                } else if (i === adminUrunPage - 3 || i === adminUrunPage + 3) {
                    html += `<span style="color:var(--text-main); padding:0 5px; align-self: center;">...</span>`;
                }
            }

            const nextDisabled = adminUrunPage === totalPages;
            html += `<button style="${baseStyle} border: 1px solid var(--border); background: transparent; color: var(--text-main); ${nextDisabled ? 'opacity:0.5; cursor:default;' : ''}" 
                        onclick="${!nextDisabled ? `updateAdminUrunPage(${adminUrunPage + 1})` : ''}" ${nextDisabled ? 'disabled' : ''}>Sonraki ▶</button>`;

            container.innerHTML = html;
        }

        async function fetchUrunler() {
            try {
                const limitSelect = document.getElementById('adminUrunLimit');
                const adminLimit = limitSelect ? limitSelect.value : 5000;
                
                const searchInput = document.getElementById('adminUrunArama');
                const adminSearch = searchInput ? searchInput.value : '';

                const response = await fetch(`../api/admin_urun_api.php?page=${adminUrunPage}&limit=${adminLimit}&search=${encodeURIComponent(adminSearch)}`);
                const data = await response.json();
                const tbody = document.getElementById('urunTableBody');
                tbody.innerHTML = '';

                const results = (data.status === 'success') ? (data.data || []) : [];
                
                if (results.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">Kayıtlı ürün bulunamadı.</td></tr>';
                    renderAdminUrunPagination(0);
                    return;
                }

                results.forEach(u => {
                    // Escape single quotes in names so it doesn't break the onclick string
                    const safeName = u.urun_adi ? u.urun_adi.replace(/'/g, "\\'") : '';
                    tbody.innerHTML += `
                        <tr>
                            <td><strong>${u.urun_kodu}</strong></td>
                            <td>${u.urun_adi}</td>
                            <td>$ ${u.usd_fiyat}</td>
                            <td>
                                <button onclick="editUrun(${u.id}, '${u.urun_kodu}', '${safeName}', ${u.usd_fiyat})" style="padding: 5px 10px; background-color: var(--primary); font-size: 0.85rem;">Düzenle</button>
                            </td>
                        </tr>
                    `;
                });
                
                if (data.status === 'success') {
                    renderAdminUrunPagination(data.total_pages);
                }

            } catch (err) {
                console.error('Ürünler çekilirken hata oluştu:', err);
            }
        }

        function editUrun(id, kodu, adi, fiyat) {
            document.getElementById('edit_u_id').value = id;
            document.getElementById('edit_u_urun_kodu').value = kodu;
            document.getElementById('edit_u_urun_adi').value = adi;
            document.getElementById('edit_u_usd_fiyat').value = fiyat;
            openEditUrunModal();
        }

        function openEditUrunModal() {
            document.getElementById('editUrunModal').style.display = 'block';
        }

        function closeEditUrunModal() {
            document.getElementById('editUrunModal').style.display = 'none';
        }

        async function updateUrun(e) {
            e.preventDefault();
            const payload = {
                id: document.getElementById('edit_u_id').value,
                urun_kodu: document.getElementById('edit_u_urun_kodu').value,
                urun_adi: document.getElementById('edit_u_urun_adi').value,
                usd_fiyat: document.getElementById('edit_u_usd_fiyat').value
            };

            try {
                const response = await fetch('../api/admin_urun_api.php', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();
                if (result.status === 'success') {
                    showNotification('Ürün başarıyla güncellendi!');
                    fetchUrunler();
                    closeEditUrunModal();
                } else {
                    alert('Hata: ' + result.message);
                }
            } catch (err) {
                alert('Sistemsel hata oluştu.');
            }
        }

        async function saveUrun(e) {
            e.preventDefault();
            const payload = {
                urun_kodu: document.getElementById('u_urun_kodu').value,
                urun_adi: document.getElementById('u_urun_adi').value,
                usd_fiyat: document.getElementById('u_usd_fiyat').value
            };

            try {
                const response = await fetch('../api/admin_urun_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();
                if (result.status === 'success') {
                    showNotification('Ürün başarıyla eklendi!');
                    document.getElementById('urunForm').reset();
                    fetchUrunler();
                    closeUrunModal();
                } else {
                    alert('Hata: ' + result.message);
                }
            } catch (err) {
                console.error(err);
                alert('Sistemsel bir hata oluştu');
            }
        }

        // --- SİPARİŞ YÖNETİMİ (3 SEKMELİ) ---
        let currentFilter = { gun: '', musteri_id: '' };
        let aktifSipTab = 'musteri'; // musteri | tedarik | teslimat

        function loadSiparisler() {
            aktifSipTab = 'musteri';
            const html = `
                <!-- 3 ALT SEKME -->
                <div class="sub-tabs">
                    <button class="sub-tab-btn active" id="stab-musteri" onclick="switchSipTab('musteri')">📋 Müşteri Siparişleri</button>
                    <button class="sub-tab-btn" id="stab-tedarik" onclick="switchSipTab('tedarik')">📦 Tedarik Listesi</button>
                    <button class="sub-tab-btn" id="stab-teslimat" onclick="switchSipTab('teslimat')">🚚 Teslimat</button>
                    <button class="sub-tab-btn" id="stab-teslimatfis" onclick="switchSipTab('teslimatfis')">🧾 Teslimat Fişleri</button>
                </div>

                <!-- TAB 1: MÜŞTERİ SİPARİŞLERİ -->
                <div id="sipTab-musteri">
                    <div class="search-bar-wrap">
                        <span class="search-icon">🔍</span>
                        <input type="text" id="musteriArama" placeholder="Ürün kodu ile ara..." oninput="filterMusteriSip()">
                    </div>
                    <div class="bulk-bar">
                        <span id="seciliMusteri">Seçili: 0</span>
                        <button class="btn-bulk btn-bulk-primary" onclick="topluTedarikle()">➕ Seçilenleri Listeye Ekle</button>
                        <button class="btn-bulk btn-bulk-secondary" onclick="tumunuSecMusteri()">Tümünü Seç</button>
                        <button class="btn-bulk btn-bulk-danger" onclick="secimKaldir('musteri')">Seçimi Kaldır</button>
                    </div>
                    <div id="musteriSipAccordion">
                        <div style="text-align:center; padding:40px; color:var(--text-main);">Yükleniyor...</div>
                    </div>
                </div>

                <!-- TAB 2: TEDARİK LİSTESİ -->
                <div id="sipTab-tedarik" style="display:none;">
                    <div style="display:flex; gap:10px; align-items:center; margin-bottom:12px; flex-wrap:wrap;">
                        <div class="search-bar-wrap" style="flex:1; min-width:180px; margin-bottom:0;">
                            <span class="search-icon">🔍</span>
                            <input type="text" id="tedarikArama" placeholder="Ürün kodu veya adı ile ara..." oninput="filterTedarikSip()">
                        </div>
                        <button class="btn-bulk btn-bulk-primary" style="white-space:nowrap;" onclick="printTedarikSiparisFisi()">🖨️ Sipariş Fişi Yazdır</button>
                    </div>
                    <div class="bulk-bar">
                        <span id="seciliTedarik">Seçili: 0</span>
                        <button class="btn-bulk btn-bulk-success" onclick="topluGeldi()">✅ Seçilenleri Geldi Yap</button>
                        <button class="btn-bulk btn-bulk-danger" onclick="topluListedenCikar()">❌ Listeden Çıkar</button>
                        <button class="btn-bulk btn-bulk-secondary" onclick="tumunuSecTedarik()">Tümünü Seç</button>
                        <button class="btn-bulk btn-bulk-secondary" onclick="secimKaldir('tedarik')">Seçimi Kaldır</button>
                    </div>
                    <div id="tedarikListeContainer">
                        <div style="text-align:center; padding:40px; color:var(--text-main);">Yükleniyor...</div>
                    </div>
                </div>

                <!-- TAB 3: TESLİMAT -->
                <div id="sipTab-teslimat" style="display:none;">
                    <div class="filter-bar" style="margin-bottom:20px;">
                        <label>👤 Müşteri:</label>
                        <select id="teslimatMusteriFilter" class="filter-select" onchange="refreshTeslimatTab()">
                            <option value="">Tüm Müşteriler</option>
                        </select>
                    </div>
                    <div class="section-title">📥 Teslim Bekleyenler (Geldi)</div>
                    <div id="teslimatBekleyenAccordion">
                        <div style="text-align:center; padding:40px; color:var(--text-main);">Yükleniyor...</div>
                    </div>
                    <hr class="section-divider">
                    <div class="section-title">✅ Teslim Edilenler (Müşteri Bazlı)</div>
                    <div id="teslimEdilenlerContainer">
                        <div style="text-align:center; padding:40px; color:var(--text-main);">Yükleniyor...</div>
                    </div>
                </div>

                <!-- TAB 4: TESLİMAT FİŞLERİ -->
                <div id="sipTab-teslimatfis" style="display:none;">
                    <div class="filter-bar" style="margin-bottom:12px; flex-wrap:wrap;">
                        <label>👤 Müşteri:</label>
                        <select id="teslimatFisMusteriFilter" class="filter-select" onchange="loadTeslimatFisleri()">
                            <option value="">Tüm Müşteriler</option>
                        </select>
                    </div>
                    <div style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:16px; align-items:center;">
                        <span style="color:var(--text-main); font-size:0.85rem; margin-right:4px;">📅 Dönem:</span>
                        <button class="date-chip active" data-period="all" onclick="setTeslimatFisTarih('all', this)">Tümü</button>
                        <button class="date-chip" data-period="today" onclick="setTeslimatFisTarih('today', this)">Bugün</button>
                        <button class="date-chip" data-period="yesterday" onclick="setTeslimatFisTarih('yesterday', this)">Dün</button>
                        <button class="date-chip" data-period="week" onclick="setTeslimatFisTarih('week', this)">Bu Hafta</button>
                        <button class="date-chip" data-period="month" onclick="setTeslimatFisTarih('month', this)">Bu Ay</button>
                        <button class="date-chip" data-period="7days" onclick="setTeslimatFisTarih('7days', this)">Son 7 Gün</button>
                        <button class="date-chip" data-period="30days" onclick="setTeslimatFisTarih('30days', this)">Son 30 Gün</button>
                        <span style="border-left:1px solid var(--border-color); height:24px; margin:0 4px;"></span>
                        <button class="date-chip" data-period="custom" onclick="setTeslimatFisTarih('custom', this)">📆 Özel Aralık</button>
                    </div>
                    <div id="customDateRange" style="display:none; margin-bottom:16px; gap:12px; align-items:center; flex-wrap:wrap;">
                        <label style="font-size:0.85rem; color:var(--text-main);">Başlangıç:</label>
                        <input type="date" id="teslimatFisTarihBas" class="filter-select" style="width:160px;" onchange="loadTeslimatFisleri()">
                        <label style="font-size:0.85rem; color:var(--text-main);">Bitiş:</label>
                        <input type="date" id="teslimatFisTarihSon" class="filter-select" style="width:160px;" onchange="loadTeslimatFisleri()">
                    </div>
                    <div class="section-title">🧾 Müşteri Bazlı Teslimat Fişleri</div>
                    <div id="teslimatFisiAccordionContainer">
                        <div style="text-align:center; padding:40px; color:var(--text-main);">Sekmeye giriş yapıldığında yüklenecek...</div>
                    </div>
                </div>
            `;
            document.getElementById('content-area').innerHTML = html;
            loadTeslimatMusteriFilter();
            fetchMusteriSiparisleri();
        }

        // Müşteri Filtresi (Teslimat tabı için)
        async function loadTeslimatMusteriFilter() {
            try {
                const res = await fetch('../api/siparis_api.php?action=musteriler_listesi');
                const data = await res.json();
                const sel = document.getElementById('teslimatMusteriFilter');
                if (sel && Array.isArray(data)) {
                    data.forEach(m => {
                        sel.innerHTML += `<option value="${m.id}">${m.firma_adi}</option>`;
                    });
                }
            } catch (e) { console.error(e); }
        }

        function switchSipTab(tab) {
            aktifSipTab = tab;
            ['musteri', 'tedarik', 'teslimat', 'teslimatfis'].forEach(t => {
                const tabEl = document.getElementById('sipTab-' + t);
                const btnEl = document.getElementById('stab-' + t);
                if (tabEl) tabEl.style.display = t === tab ? '' : 'none';
                if (btnEl) btnEl.classList.toggle('active', t === tab);
            });
            if (tab === 'musteri') fetchMusteriSiparisleri();
            else if (tab === 'tedarik') fetchTedarikListesi();
            else if (tab === 'teslimat') refreshTeslimatTab();
            else if (tab === 'teslimatfis') initTeslimatFisleri();
        }


        // =====================================
        // TAB 1: MÜŞTERİ SİPARİŞLERİ
        // =====================================
        let musteriSipData = [];
        let secilenMusteriDetaylar = new Set();

        async function fetchMusteriSiparisleri() {
            try {
                const res = await fetch('../api/siparis_api.php?action=musteri_siparisleri&_t=' + Date.now(), { cache: 'no-store' });
                musteriSipData = await res.json();
                renderMusteriSipAccordion(musteriSipData);
            } catch (err) {
                console.error('Müşteri siparişleri çekilirken hata:', err);
            }
        }

        function filterMusteriSip() {
            const q = (document.getElementById('musteriArama')?.value || '').toLowerCase();
            if (!q) { renderMusteriSipAccordion(musteriSipData); return; }
            renderMusteriSipAccordion(musteriSipData.filter(u => u.urun_kodu.toLowerCase().includes(q) || u.urun_adi.toLowerCase().includes(q)));
        }

        function renderMusteriSipAccordion(data) {
            const container = document.getElementById('musteriSipAccordion');
            if (!data || !container) return;
            if (data.length === 0) {
                container.innerHTML = '<div class="card" style="text-align:center; padding:30px;">Bekleyen sipariş bulunamadı.</div>';
                return;
            }
            container.innerHTML = data.map((u, idx) => `
                <div class="product-accordion" id="msacc-${idx}">
                    <div class="accordion-header" onclick="toggleMusteriAccordion(${idx}, '${u.urun_kodu}')">
                        <span class="product-code">${u.urun_kodu}</span>
                        <span class="product-name">${u.urun_adi}</span>
                        <div class="product-stats">
                            <span class="stat-badge kg">${parseFloat(u.toplam_bekleyen).toFixed(1)} KG bekliyor</span>
                            <span class="stat-badge customers">${u.musteri_sayisi} müşteri</span>
                            <button class="btn-sm btn-tedarik" onclick="event.stopPropagation(); hizliTedarikEkle('${u.urun_kodu}', this)" style="margin-left:6px; font-size:0.78rem; padding:4px 10px;" title="Tüm müşterilerin siparişini listeye ekle">➕ Listeye Ekle</button>
                        </div>
                        <span class="accordion-chevron">▼</span>
                    </div>
                    <div class="accordion-body" id="msacc-body-${idx}">
                        <div style="padding:20px; text-align:center; color:var(--text-main);">Yükleniyor...</div>
                    </div>
                </div>
            `).join('');
        }

        async function hizliTedarikEkle(urunKodu, btn) {
            if (btn) { btn.disabled = true; btn.textContent = '⏳ Ekleniyor...'; }
            try {
                // Önce bu ürünün tüm beklemede detay ID'lerini çek
                const res = await fetch('../api/siparis_api.php?action=musteri_siparis_detay&urun_kodu=' + encodeURIComponent(urunKodu));
                const data = await res.json();
                if (!data || data.length === 0) {
                    showNotification('Bu ürün için bekleyen sipariş bulunamadı.', 'error');
                    if (btn) { btn.disabled = false; btn.textContent = '➕ Listeye Ekle'; }
                    return;
                }
                const detayIds = data.map(d => d.detay_id);
                const updateRes = await fetch('../api/siparis_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'toplu_durum_guncelle', detay_ids: detayIds, yeni_durum: 'tedarik' })
                });
                const result = await updateRes.json();
                if (result.status === 'success') {
                    showNotification(`${urunKodu} — ${detayIds.length} sipariş tedarik listesine eklendi!`);
                    fetchMusteriSiparisleri();
                } else {
                    alert('Hata: ' + result.message);
                    if (btn) { btn.disabled = false; btn.textContent = '➕ Listeye Ekle'; }
                }
            } catch (e) {
                console.error(e);
                alert('Sistemsel hata oluştu.');
                if (btn) { btn.disabled = false; btn.textContent = '➕ Listeye Ekle'; }
            }
        }

        async function toggleMusteriAccordion(idx, urunKodu) {
            const el = document.getElementById('msacc-' + idx);
            const body = document.getElementById('msacc-body-' + idx);
            if (el.classList.contains('open')) { el.classList.remove('open'); return; }
            document.querySelectorAll('#musteriSipAccordion .product-accordion.open').forEach(a => a.classList.remove('open'));
            el.classList.add('open');
            try {
                const res = await fetch('../api/siparis_api.php?action=musteri_siparis_detay&urun_kodu=' + encodeURIComponent(urunKodu));
                const data = await res.json();
                if (data.length === 0) { body.innerHTML = '<div style="padding:20px; text-align:center;">Detay bulunamadı.</div>'; return; }
                let html = `<table><thead><tr>
                    <th><input type="checkbox" class="row-check" onchange="selectAllInGroup('${urunKodu}', this.checked)"></th>
                    <th>Firma Adı</th><th>İstenen (KG)</th><th>Kalan</th><th>Tarih</th><th>İşlemler</th>
                </tr></thead><tbody>`;
                data.forEach(d => {
                    const tarih = d.tarih ? new Date(d.tarih).toLocaleDateString('tr-TR') : '-';
                    html += `<tr>
                        <td><input type="checkbox" class="row-check ms-detay-check" data-detay="${d.detay_id}" onchange="updateMusteriSecim()"></td>
                        <td><strong>${d.firma_adi}</strong></td>
                        <td>${parseFloat(d.istened_kg || d.istenen_kg).toFixed(1)} KG</td>
                        <td style="color:var(--danger);font-weight:600;">${parseFloat(d.kalan_kg).toFixed(1)} KG</td>
                        <td>${tarih}</td>
                        <td>
                            <button class="btn-sm btn-tedarik" onclick="tekListeyeEkle(${d.detay_id})">➕ Listeye Ekle</button>
                            <button class="btn-sm btn-iptal" onclick="updateDetayDurum(${d.detay_id}, 'iptal')">İptal</button>
                        </td>
                    </tr>`;
                });
                html += '</tbody></table>';
                body.innerHTML = html;
            } catch (e) { body.innerHTML = '<div style="padding:20px; color:var(--danger);">Hata oluştu.</div>'; }
        }

        function selectAllInGroup(urunKodu, checked) {
            document.querySelectorAll('.ms-detay-check').forEach(cb => {
                const row = cb.closest('tr');
                if (row) { cb.checked = checked; }
            });
            updateMusteriSecim();
        }

        function updateMusteriSecim() {
            secilenMusteriDetaylar.clear();
            document.querySelectorAll('.ms-detay-check:checked').forEach(cb => secilenMusteriDetaylar.add(parseInt(cb.dataset.detay)));
            const el = document.getElementById('seciliMusteri');
            if (el) el.textContent = 'Seçili: ' + secilenMusteriDetaylar.size;
        }

        async function tumunuSecMusteri() {
            // Görünür checkbox varsa onları seç
            const boxes = document.querySelectorAll('.ms-detay-check');
            if (boxes.length > 0) {
                boxes.forEach(cb => { cb.checked = true; });
                updateMusteriSecim();
            }
            // Ayrıca API'den TÜM beklemede detay ID'lerini çek
            try {
                const res = await fetch('../api/siparis_api.php?action=tum_detay_idler&durum=beklemede');
                const ids = await res.json();
                if (Array.isArray(ids)) {
                    ids.forEach(id => secilenMusteriDetaylar.add(id));
                    const el = document.getElementById('seciliMusteri');
                    if (el) el.textContent = 'Seçili: ' + secilenMusteriDetaylar.size;
                }
            } catch (e) { console.error(e); }
        }

        function secimKaldir(tab) {
            if (tab === 'musteri') {
                document.querySelectorAll('.ms-detay-check').forEach(cb => { cb.checked = false; });
                secilenMusteriDetaylar.clear();
                const el = document.getElementById('seciliMusteri');
                if (el) el.textContent = 'Seçili: 0';
            } else if (tab === 'tedarik') {
                document.querySelectorAll('.td-detay-check').forEach(cb => { cb.checked = false; });
                document.querySelectorAll('.td-urun-check').forEach(cb => { cb.checked = false; });
                const headCb = document.getElementById('tedarikTumSec');
                if (headCb) headCb.checked = false;
                secilenTedarikDetaylar.clear();
                secilenTedarikUrunler.clear();
                const el = document.getElementById('seciliTedarik');
                if (el) el.textContent = 'Seçili: 0';
            }
        }

        async function tekListeyeEkle(detayId) {
            await updateDetayDurum(detayId, 'tedarik', false);
            fetchMusteriSiparisleri();
        }

        async function topluTedarikle() {
            // Eğer seçili yoksa ve checkbox de yoksa, tüm beklemede olanları al
            if (secilenMusteriDetaylar.size === 0) {
                try {
                    const res = await fetch('../api/siparis_api.php?action=tum_detay_idler&durum=beklemede');
                    const ids = await res.json();
                    if (Array.isArray(ids) && ids.length > 0) {
                        ids.forEach(id => secilenMusteriDetaylar.add(id));
                    }
                } catch (e) { }
            }
            if (secilenMusteriDetaylar.size === 0) { showNotification('Listeye eklenecek sipariş bulunamadı.', 'error'); return; }
            try {
                const res = await fetch('../api/siparis_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'toplu_durum_guncelle', detay_ids: [...secilenMusteriDetaylar], yeni_durum: 'tedarik' })
                });
                const r = await res.json();
                if (r.status === 'success') { showNotification('Siparişler tedarik listesine eklendi!'); secilenMusteriDetaylar.clear(); fetchMusteriSiparisleri(); }
                else alert('Hata: ' + r.message);
            } catch (e) { alert('Sistemsel hata oluştu.'); }
        }

        // =====================================
        // TAB 2: TEDARİK LİSTESİ (Düz Liste + Kısmi Teslimat + Sipariş Fişi)
        // =====================================
        let tedarikData = [];          // Ürün grubu özeti
        let tedarikDetayData = {};     // Ürün kodu → detay satırları
        let secilenTedarikDetaylar = new Set(); // Checkbox seçimleri (detay_id)
        let secilenTedarikUrunler = new Set(); // Sipariş fişi için ürün kodu seçimleri

        async function fetchTedarikListesi() {
            try {
                // Özet listeyi çek
                const res = await fetch('../api/siparis_api.php?action=tedarik_listesi&_t=' + Date.now());
                tedarikData = await res.json();

                // Her ürün için detayları paralel çek
                tedarikDetayData = {};
                await Promise.all(tedarikData.map(async u => {
                    try {
                        const dr = await fetch('../api/siparis_api.php?action=tedarik_musteriler&urun_kodu=' + encodeURIComponent(u.urun_kodu) + '&_t=' + Date.now());
                        tedarikDetayData[u.urun_kodu] = await dr.json();
                    } catch (e) { tedarikDetayData[u.urun_kodu] = []; }
                }));

                renderTedarikListe(tedarikData);
            } catch (err) { console.error(err); }
        }

        function filterTedarikSip() {
            const q = (document.getElementById('tedarikArama')?.value || '').toLowerCase();
            if (!q) { renderTedarikListe(tedarikData); return; }
            renderTedarikListe(tedarikData.filter(u => u.urun_kodu.toLowerCase().includes(q) || u.urun_adi.toLowerCase().includes(q)));
        }

        function renderTedarikListe(data) {
            const container = document.getElementById('tedarikListeContainer');
            if (!data || !container) return;
            if (data.length === 0) {
                container.innerHTML = '<div class="card" style="text-align:center; padding:30px;">Tedarik listesi boş. Müşteri siparişlerinden ürün ekleyin.</div>';
                return;
            }

            // Toplu seçim başlık checkboxı
            let html = `
            <div style="background:var(--card-bg); border:1px solid var(--border-color); border-radius:12px; overflow:hidden; margin-bottom:8px;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:rgba(255,255,255,0.04); font-size:0.78rem; color:var(--text-main); text-transform:uppercase; letter-spacing:0.04em;">
                            <th style="padding:10px 14px; text-align:left; width:36px;"><input type="checkbox" id="tedarikTumSec" onchange="tedarikTumunuToggle(this.checked)" title="Tümünü seç"></th>
                            <th style="padding:10px 14px; text-align:left;">Ürün Kodu</th>
                            <th style="padding:10px 14px; text-align:left;">Ürün Adı</th>
                            <th style="padding:10px 14px; text-align:center;">Toplam KG<br><span style="font-size:0.65rem; color:#888; text-transform:none;">(Şimdiye Kadar Gelen)</span></th>
                            <th style="padding:10px 14px; text-align:center;">Müşteri</th>
                            <th style="padding:10px 14px; text-align:center;">Miktar (Geldi)</th>
                            <th style="padding:10px 14px; text-align:center;">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>`;

            data.forEach((u, idx) => {
                const detaylar = tedarikDetayData[u.urun_kodu] || [];
                const toplamBekleyen = parseFloat(u.toplam_bekleyen); // Tedarik edilecek kalan miktar
                const toplamGelen = parseFloat(u.toplam_gelen || 0);   // Şimdiye kadar gelen miktar
                const musteri = u.musteri_sayisi;

                // Her ürün için detay satırlarının toplam istenen_kg'si (sipariş fişi için)
                html += `
                    <tr class="tedarik-urun-row" style="border-top:1px solid var(--border-color); cursor:default;" id="td-urun-row-${idx}">
                        <td style="padding:10px 14px; vertical-align:top;">
                            <input type="checkbox" class="td-urun-check" data-urun="${u.urun_kodu}"
                                data-urun-adi="${u.urun_adi.replace(/"/g, '&quot;')}"
                                data-toplam="${toplamBekleyen.toFixed(1)}"
                                onchange="updateTedarikUrunSecim()">
                        </td>
                        <td style="padding:10px 14px; vertical-align:top;">
                            <span style="font-family:monospace; font-weight:700; font-size:0.95rem; color:var(--accent);">${u.urun_kodu}</span>
                        </td>
                        <td style="padding:10px 14px; vertical-align:top;">
                            <span style="font-weight:600; color:var(--text-light);">${u.urun_adi}</span>
                            ${detaylar.length > 0 ? `<div style="margin-top:6px;">${detaylar.map(d => `<span style="font-size:0.75rem; color:var(--text-main); display:block;">└ ${d.firma_adi}: ${parseFloat(d.toplam_istenen).toFixed(1)} KG</span>`).join('')}</div>` : ''}
                        </td>
                        <td style="padding:10px 14px; text-align:center; vertical-align:top;">
                            <span class="stat-badge kg" style="background-color:rgba(26, 107, 60, 0.2); color:#4ade80; border-color:rgba(74, 222, 128, 0.3);" title="Şimdiye kadar gelen toplam miktar">${toplamGelen.toFixed(1)} KG</span>
                        </td>
                        <td style="padding:10px 14px; text-align:center; vertical-align:top;">
                            <span class="stat-badge customers">${musteri}</span>
                        </td>
                        <td style="padding:10px 14px; text-align:center; vertical-align:top;">
                            <div style="display:flex; flex-direction:column; align-items:center; gap:4px;">
                                <div style="display:flex; align-items:center; gap:5px;">
                                    <input type="number" step="0.1" min="0.1" max="${toplamBekleyen.toFixed(1)}"
                                        value="${toplamBekleyen.toFixed(1)}"
                                        id="td-urun-miktar-${u.urun_kodu.replace(/[^a-zA-Z0-9]/g, '_')}"
                                        onfocus="this.select()"
                                        onkeydown="if(event.key==='Enter') tedarikUrunGeldi('${u.urun_kodu}')"
                                        style="width:85px; padding:4px 8px; background:rgba(0,0,0,0.3); border:1px solid var(--border-color); color:var(--text-light); border-radius:5px; font-size:0.88rem; text-align:center;"
                                        title="Gelen miktar (Kalan Toplam Bekleyen: ${toplamBekleyen.toFixed(1)} KG)">
                                    <span style="font-size:0.78rem; color:var(--text-main);">KG</span>
                                </div>
                                <span style="font-size:0.7rem; color:rgba(255,184,34,0.7);">Kalan Bekleyen: ${toplamBekleyen.toFixed(1)} KG</span>
                            </div>
                        </td>
                        <td style="padding:10px 14px; text-align:center; vertical-align:top;">
                            <div style="display:flex; flex-direction:column; gap:4px; align-items:center;">
                                <button class="btn-sm btn-geldi" style="font-size:0.75rem; white-space:nowrap;" onclick="tedarikUrunGeldi('${u.urun_kodu}')" title="Girilen miktarı Geldi olarak işaretle">✅ Geldi</button>
                            </div>
                        </td>
                    </tr>`;
            });

            html += `</tbody></table></div>`;
            container.innerHTML = html;

            // Restore focus if needed
            if (nextFocusId) {
                const nextEl = document.getElementById(nextFocusId);
                if (nextEl) {
                    nextEl.focus();
                    if (typeof nextEl.select === 'function') nextEl.select();
                }
                nextFocusId = null;
            }
        }

        // Ürün satırı checkbox'larını güncelle (sipariş fişi için)
        function updateTedarikUrunSecim() {
            secilenTedarikUrunler.clear();
            document.querySelectorAll('.td-urun-check:checked').forEach(cb => {
                secilenTedarikUrunler.add({
                    urun_kodu: cb.dataset.urun,
                    urun_adi: cb.dataset.urunAdi,
                    toplam_kg: cb.dataset.toplam
                });
            });
            const el = document.getElementById('seciliTedarik');
            if (el) el.textContent = 'Seçili: ' + secilenTedarikUrunler.size + ' ürün';
        }

        // Detay seçimini güncelle (artık checkbox yok, compat için boş bırakıldı)
        function updateTedarikSecim() {
            secilenTedarikDetaylar.clear();
        }

        // Tümünü seç (ürün checkboxları için - sipariş fişi seçimi)
        function tumunuSecTedarik() {
            document.querySelectorAll('.td-urun-check').forEach(cb => { cb.checked = true; });
            updateTedarikUrunSecim();
            const headCb = document.getElementById('tedarikTumSec');
            if (headCb) headCb.checked = true;
        }

        function tedarikTumunuToggle(checked) {
            document.querySelectorAll('.td-urun-check').forEach(cb => { cb.checked = checked; });
            updateTedarikUrunSecim();
        }

        // Tek satır için kısmi/tam geldi işlemi (split mantığı)
        async function tedarikDetaySatirGeldi(detayId, maxKalan) {
            const input = document.getElementById('td-miktar-' + detayId);
            if (!input) { alert('Miktar alanı bulunamadı.'); return; }
            const miktar = parseFloat(input.value);
            if (isNaN(miktar) || miktar <= 0) { alert('Geçerli bir miktar giriniz.'); return; }
            if (miktar > parseFloat(maxKalan) + 0.01) {
                alert(`Miktar kalan miktarı (${maxKalan} KG) aşamaz.`);
                input.value = parseFloat(maxKalan).toFixed(1);
                return;
            }
            try {
                const res = await fetch('../api/siparis_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'tedarik_geldi_kismi', detay_id: detayId, miktar: miktar })
                });
                const r = await res.json();
                if (r.status === 'success') {
                    showNotification(r.mesaj || 'Geldi olarak işaretlendi!');
                    fetchTedarikListesi();
                } else {
                    alert('Hata: ' + (r.message || 'Bilinmeyen hata'));
                }
            } catch (e) {
                console.error(e);
                alert('Sistemsel hata oluştu.');
            }
        }

        // Tek ürün için Geldi işlemi (ürün bazlı toplam miktar → API FIFO dağıtır)
        async function tedarikUrunGeldi(urunKodu) {
            const inputId = 'td-urun-miktar-' + urunKodu.replace(/[^a-zA-Z0-9]/g, '_');
            const input = document.getElementById(inputId);
            if (!input) { showNotification('Miktar alanı bulunamadı.', 'error'); return; }
            const miktar = parseFloat(input.value);
            const toplamMax = parseFloat(input.max);
            if (isNaN(miktar) || miktar <= 0) { alert('Geçerli bir miktar giriniz.'); return; }
            if (miktar > toplamMax + 0.01) { alert(`Miktar toplam tedarik miktarını (${toplamMax} KG) aşamaz.`); input.value = toplamMax.toFixed(1); return; }

            try {
                const res = await fetch('../api/siparis_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'tedarik_geldi_urun', urun_kodu: urunKodu, miktar: miktar })
                });
                const r = await res.json();
                if (r.status === 'success') {
                    showNotification(r.mesaj || `${urunKodu} — Geldi olarak işaretlendi!`);
                    
                    // Identify next input for focus
                    const currentInp = document.getElementById('td-urun-miktar-' + urunKodu.replace(/[^a-zA-Z0-9]/g, '_'));
                    if (currentInp) {
                        const allInputs = Array.from(document.querySelectorAll('[id^="td-urun-miktar-"]'));
                        const idx = allInputs.indexOf(currentInp);
                        if (idx !== -1 && idx < allInputs.length - 1) {
                            nextFocusId = allInputs[idx + 1].id;
                        }
                    }

                    fetchTedarikListesi();
                } else {
                    alert('Hata: ' + (r.message || 'Bilinmeyen hata'));
                }
            } catch (e) {
                console.error(e);
                alert('Sistemsel hata oluştu.');
            }
        }


        // Tüm tedarik satırlarını (ürün bazlı) Geldi yap
        async function topluGeldi() {
            const inputs = document.querySelectorAll('[id^="td-urun-miktar-"]');
            if (inputs.length === 0) { alert('Görünür tedarik ürünü bulunamadı.'); return; }
            if (!confirm(`Listedeki tüm ${inputs.length} ürünü mevcut miktarlarda Geldi olarak işaretlemek istiyor musunuz?`)) return;

            let yapılanlar = 0;
            for (const input of inputs) {
                const urunKodu = input.dataset.urunKodu;
                const miktar = parseFloat(input.value);
                if (!urunKodu || isNaN(miktar) || miktar <= 0) continue;
                try {
                    const res = await fetch('../api/siparis_api.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'tedarik_geldi_urun', urun_kodu: urunKodu, miktar: miktar })
                    });
                    const r = await res.json();
                    if (r.status === 'success') yapılanlar++;
                    else console.error('Ürün hatası:', r.message);
                } catch (e) { console.error(e); }
            }
            showNotification(`${yapılanlar} ürün Geldi olarak işaretlendi!`);
            fetchTedarikListesi();
        }

        async function topluListedenCikar() {
            // Tüm tedarik verilerinden detay ID'lerini al
            const tumIds = [];
            for (const [urunKodu, detaylar] of Object.entries(tedarikDetayData)) {
                detaylar.forEach(d => tumIds.push(d.detay_id));
            }
            if (tumIds.length === 0) { alert('Tedarik listesi boş.'); return; }
            if (!confirm(`Tüm ${tumIds.length} satırı tedarik listesinden çıkarmak istiyor musunuz?`)) return;
            try {
                const res = await fetch('../api/siparis_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'toplu_durum_guncelle', detay_ids: tumIds, yeni_durum: 'beklemede' })
                });
                const r = await res.json();
                if (r.status === 'success') { showNotification('Tüm siparişler tedarik listesinden çıkarıldı!'); fetchTedarikListesi(); }
                else alert('Hata: ' + r.message);
            } catch (e) { alert('Sistemsel hata oluştu.'); }
        }

        // =====================================
        // TEDARİK FİRMASI SİPARİŞ FİŞİ
        // =====================================
        function printTedarikSiparisFisi() {
            // Seçili ürünler varsa onları al, yoksa TÜM tedarik listesini kullan
            let liste = [];
            const seciliCbs = document.querySelectorAll('.td-urun-check:checked');
            if (seciliCbs.length > 0) {
                seciliCbs.forEach(cb => {
                    liste.push({
                        urun_kodu: cb.dataset.urun,
                        urun_adi: cb.dataset.urunAdi,
                        toplam_kg: parseFloat(cb.dataset.toplam)
                    });
                });
            } else {
                // Tüm görünür tedarik verisini kullan
                liste = tedarikData.map(u => ({
                    urun_kodu: u.urun_kodu,
                    urun_adi: u.urun_adi,
                    toplam_kg: parseFloat(u.toplam_bekleyen)
                }));
            }

            if (!liste.length) { showNotification('Tedarik listesi boş.', 'error'); return; }

            const bugun = new Date().toLocaleDateString('tr-TR', { year: 'numeric', month: 'long', day: 'numeric' });
            const saat = new Date().toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' });
            const toplamKg = liste.reduce((s, u) => s + u.toplam_kg, 0);

            let satirlar = '';
            liste.forEach((u, i) => {
                satirlar += `<tr>
                    <td style="padding:8px 12px; border:1px solid #ddd; text-align:center; font-size:13px; color:#555;">${i + 1}</td>
                    <td style="padding:8px 12px; border:1px solid #ddd; font-weight:700; font-family:monospace; font-size:13px; color:#222;">${u.urun_kodu}</td>
                    <td style="padding:8px 12px; border:1px solid #ddd; font-size:13px; color:#222;">${u.urun_adi}</td>
                    <td style="padding:8px 12px; border:1px solid #ddd; text-align:right; font-weight:700; font-size:14px; color:#1a6b3c;">${u.toplam_kg.toFixed(1)} KG</td>
                </tr>`;
            });

            const printHTML = `<html><head><title>Tedarik Sipariş Fişi — ${bugun}</title>
            <style>
                * { box-sizing: border-box; }
                body { font-family: 'Segoe UI', Arial, sans-serif; padding: 30px; color: #222; margin: 0; background: #fff; }
                .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #222; padding-bottom: 16px; margin-bottom: 24px; }
                .header-left h1 { margin: 0 0 4px; font-size: 24px; letter-spacing: -0.5px; }
                .header-left p { margin: 0; font-size: 13px; color: #777; }
                .header-right { text-align: right; font-size: 13px; color: #555; line-height: 1.8; }
                .badge { display: inline-block; background: #f0f0f0; border: 1px solid #ddd; border-radius: 4px; padding: 2px 8px; font-size: 12px; }
                table { width: 100%; border-collapse: collapse; margin-bottom:20px; }
                thead tr { background: #2d2d2d; color: #fff; }
                th { padding: 10px 12px; text-align: left; font-size: 12px; letter-spacing: 0.04em; text-transform: uppercase; }
                th:last-child { text-align: right; }
                tbody tr:nth-child(even) { background: #f9f9f9; }
                .total-row { background: #2d2d2d !important; color: #fff; }
                .total-row td { padding: 10px 12px; font-weight: 700; font-size:14px; }
                .footer { margin-top: 40px; display: flex; justify-content: space-between; }
                .sign-col { width: 200px; text-align: center; }
                .sign-line { border-top: 1.5px solid #333; margin-top: 60px; padding-top: 6px; font-size: 12px; color: #555; }
                .info-box { background: #f5f5f5; border-radius: 6px; padding: 10px 14px; font-size: 12px; color: #555; margin-bottom: 20px; }
                @media print { body { padding: 15px; } button { display: none; } }
            </style></head><body>
            <div class="header">
                <div class="header-left">
                    <h1>📦 AKSA TOPTAN</h1>
                    <p>Tedarik Firması Sipariş Fişi</p>
                </div>
                <div class="header-right">
                    <strong>Tarih:</strong> ${bugun}<br>
                    <strong>Saat:</strong> ${saat}<br>
                    <span class="badge">${liste.length} ürün çeşidi</span>
                </div>
            </div>
            <div class="info-box">
                ℹ️ Bu fiş tedarik firmasına iletilmek üzere hazırlanmıştır. Aşağıdaki ürünler için sipariş verilecektir.
            </div>
            <table>
                <thead><tr>
                    <th style="width:40px;">#</th>
                    <th style="width:130px;">Ürün Kodu</th>
                    <th>Ürün Adı</th>
                    <th style="width:120px; text-align:right;">Miktar (KG)</th>
                </tr></thead>
                <tbody>
                    ${satirlar}
                    <tr class="total-row">
                        <td colspan="3" style="text-align:right;">TOPLAM SİPARİŞ:</td>
                        <td style="text-align:right; color:#4ade80;">${toplamKg.toFixed(1)} KG</td>
                    </tr>
                </tbody>
            </table>
            <div class="footer">
                <div class="sign-col"><div class="sign-line">Hazırlayan</div></div>
                <div class="sign-col"><div class="sign-line">Onaylayan</div></div>
                <div class="sign-col"><div class="sign-line">Tedarik Firması</div></div>
            </div>
            <p style="font-size:10px; color:#aaa; text-align:center; margin-top:30px;">AKSA TOPTAN — Sistem tarafından otomatik oluşturulmuştur.</p>
            </body></html>`;

            const blob = new Blob([printHTML], { type: 'text/html; charset=utf-8' });
            const blobUrl = URL.createObjectURL(blob);
            const w = window.open(blobUrl, '_blank');
            if (w) {
                w.onload = () => { setTimeout(() => { w.print(); URL.revokeObjectURL(blobUrl); }, 300); };
            } else {
                const a = document.createElement('a'); a.href = blobUrl; a.target = '_blank'; a.click();
            }
        }

        // =====================================
        // TAB 3: TESLİMAT
        // =====================================
        async function refreshTeslimatTab() {
            await fetchTeslimatBekleyen();
            await fetchTeslimEdilenler();
        }

        async function fetchTeslimatBekleyen() {
            const musteriId = document.getElementById('teslimatMusteriFilter')?.value || '';
            let url = '../api/siparis_api.php?action=teslimat_bekleyen&_t=' + Date.now();
            if (musteriId) url += '&musteri_id=' + musteriId;
            try {
                const res = await fetch(url, { cache: 'no-store' });
                const data = await res.json();
                const container = document.getElementById('teslimatBekleyenAccordion');
                if (!container) return;
                if (data.length === 0) {
                    container.innerHTML = '<div class="card" style="text-align:center; padding:30px;">Teslim bekleyen ürün yok.</div>';
                    return;
                }
                container.innerHTML = data.map((u, idx) => `
                    <div class="product-accordion" id="tsacc-${idx}" data-urun-kodu="${u.urun_kodu}">
                        <div class="accordion-header" onclick="toggleTeslimatAccordion(${idx}, '${u.urun_kodu}')">
                            <span class="product-code">${u.urun_kodu}</span>
                            <span class="product-name">${u.urun_adi}</span>
                            <div class="product-stats">
                                <span class="stat-badge geldi">${parseFloat(u.toplam_istenen || u.toplam_bekleyen).toFixed(1)} KG</span>
                                <span class="stat-badge customers">${u.musteri_sayisi} müşteri</span>
                            </div>
                            <span class="accordion-chevron">▼</span>
                        </div>
                        <div class="accordion-body" id="tsacc-body-${idx}">
                            <div style="padding:20px; text-align:center; color:var(--text-main);">Yükleniyor...</div>
                        </div>
                    </div>
                `).join('');

                // Restore open accordion
                if (lastOpenTeslimatUrun) {
                    const idx = data.findIndex(u => u.urun_kodu === lastOpenTeslimatUrun);
                    if (idx !== -1) {
                        toggleTeslimatAccordion(idx, lastOpenTeslimatUrun, true);
                    }
                }
            } catch (e) { console.error(e); }
        }

        async function toggleTeslimatAccordion(idx, urunKodu, isAuto = false) {
            const el = document.getElementById('tsacc-' + idx);
            const body = document.getElementById('tsacc-body-' + idx);
            if (el.classList.contains('open') && !isAuto) { 
                el.classList.remove('open'); 
                lastOpenTeslimatUrun = null;
                return; 
            }
            document.querySelectorAll('#teslimatBekleyenAccordion .product-accordion.open').forEach(a => a.classList.remove('open'));
            el.classList.add('open');
            lastOpenTeslimatUrun = urunKodu;
            const musteriId = document.getElementById('teslimatMusteriFilter')?.value || '';
            let url = '../api/siparis_api.php?action=teslimat_musteriler&urun_kodu=' + encodeURIComponent(urunKodu) + '&_t=' + Date.now();
            if (musteriId) url += '&musteri_id=' + musteriId;
            try {
                const res = await fetch(url, { cache: 'no-store' });
                const data = await res.json();
                if (data.length === 0) { body.innerHTML = '<div style="padding:20px; text-align:center;">Detay bulunamadı.</div>'; return; }
                let html = `<table><thead><tr>
                    <th>Firma Adı</th><th>Miktar (Geldi)</th><th>Şimdiye Kadar Gelen (KG)</th><th>Kalan</th><th>Tarih</th><th>Teslim Miktarı</th><th>İşlem</th>
                </tr></thead><tbody>`;
                data.forEach(d => {
                    const tarih = d.tarih ? new Date(d.tarih).toLocaleDateString('tr-TR') : '-';
                    const istenen = parseFloat(d.istened_kg || d.istenen_kg);
                    const teslimEdilen = parseFloat(d.teslim_edilen_kg || 0);
                    const kalan = parseFloat(d.kalan_kg);
                    const yuzde = istenen > 0 ? Math.round((teslimEdilen / istenen) * 100) : 0;
                    html += `<tr>
                        <td><strong>${d.firma_adi}</strong></td>
                        <td>${istenen.toFixed(1)} KG</td>
                        <td>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <div style="flex:1; background:rgba(255,255,255,0.08); border-radius:10px; height:8px; min-width:60px; overflow:hidden;">
                                    <div style="width:${yuzde}%; height:100%; background:${yuzde >= 100 ? 'var(--success)' : 'var(--accent)'}; border-radius:10px; transition:width 0.3s;"></div>
                                </div>
                                <span style="font-size:0.8rem; color:${yuzde >= 100 ? 'var(--success)' : 'var(--accent)'}; font-weight:600; white-space:nowrap;">${teslimEdilen.toFixed(1)} KG</span>
                            </div>
                        </td>
                        <td style="color:var(--danger);font-weight:600;">${kalan.toFixed(1)} KG</td>
                        <td>${tarih}</td>
                        <td>
                                    <input type="number" step="0.1" min="0.1" max="${kalan}" value="${kalan.toFixed(1)}" 
                                        id="teslim-miktar-${d.detay_id}"
                                        style="width:90px; padding:5px 8px; background:rgba(0,0,0,0.3); border:1px solid var(--border-color); color:var(--text-light); border-radius:5px; font-size:0.85rem; text-align:center;"
                                        onfocus="this.select()"
                                        onkeydown="if(event.key==='Enter') teslimEtKismi(${d.detay_id}, ${kalan})"
                                        onchange="if(parseFloat(this.value)>${kalan})this.value=${kalan.toFixed(1)}; if(parseFloat(this.value)<=0)this.value=0.1;">
                            <span style="font-size:0.75rem; color:var(--text-main);">KG</span>
                        </td>
                        <td>
                            <button class="btn-sm btn-teslim" onclick="teslimEtKismi(${d.detay_id}, ${kalan})" style="white-space:nowrap;">🚚 Teslim Et</button>
                        </td>
                    </tr>`;
                });
                html += '</tbody></table>';
                body.innerHTML = html;

                // Restore focus within accordion
                if (nextFocusId) {
                    const nextEl = document.getElementById(nextFocusId);
                    if (nextEl) {
                        nextEl.focus();
                        if (typeof nextEl.select === 'function') nextEl.select();
                    }
                    nextFocusId = null;
                }
            } catch (e) { body.innerHTML = '<div style="padding:20px; color:var(--danger);">Hata oluştu.</div>'; }
        }

        // Kısmi / tam teslim fonksiyonu
        async function teslimEtKismi(detayId, maxKalan) {
            const input = document.getElementById('teslim-miktar-' + detayId);
            if (!input) { alert('Miktar alanı bulunamadı.'); return; }
            const miktar = parseFloat(input.value);
            if (isNaN(miktar) || miktar <= 0) { alert('Lütfen geçerli bir miktar giriniz.'); return; }
            if (miktar > maxKalan + 0.01) { alert(`Teslim miktarı kalan miktarı (${maxKalan} KG) aşamaz.`); input.value = maxKalan.toFixed(1); return; }

            try {
                const res = await fetch('../api/siparis_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'teslim_et', detay_id: detayId, miktar: miktar })
                });
                const result = await res.json();
                if (result.status === 'success') {
                    showNotification(result.mesaj || 'Teslim işlemi başarılı!');
                    
                    // Identify next input for focus
                    const currentInp = document.getElementById('teslim-miktar-' + detayId);
                    if (currentInp) {
                        // All inputs in the same accordion
                        const container = currentInp.closest('.accordion-body');
                        if (container) {
                            const allInps = Array.from(container.querySelectorAll('input[type="number"]'));
                            const idx = allInps.indexOf(currentInp);
                            if (idx !== -1 && idx < allInps.length - 1) {
                                nextFocusId = allInps[idx + 1].id;
                            }
                        }
                    }

                    refreshTeslimatTab();
                } else {
                    alert('Hata: ' + (result.message || 'Bilinmeyen hata'));
                }
            } catch (err) {
                alert('Sistemsel hata oluştu.');
            }
        }
        // Teslim edilenleri müşteri bazlı grupla
        async function fetchTeslimEdilenler() {
            const musteriId = document.getElementById('teslimatMusteriFilter')?.value || '';
            let url = '../api/siparis_api.php?action=teslim_edilenler&_t=' + Date.now();
            if (musteriId) url += '&musteri_id=' + musteriId;
            try {
                const res = await fetch(url, { cache: 'no-store' });
                const data = await res.json();
                const container = document.getElementById('teslimEdilenlerContainer');
                if (!container) return;

                if (!Array.isArray(data) || data.length === 0) {
                    container.innerHTML = '<div class="card" style="text-align:center; padding:30px;">Teslim edilmiş sipariş bulunamadı.</div>';
                    return;
                }

                // Müşteri bazlı grupla
                const grouped = {};
                data.forEach(d => {
                    const key = d.musteri_id;
                    if (!grouped[key]) {
                        grouped[key] = { firma_adi: d.firma_adi, telefon: d.telefon || '', musteri_id: d.musteri_id, items: [] };
                    }
                    grouped[key].items.push(d);
                });

                let html = '';
                Object.values(grouped).forEach((g, idx) => {
                    const toplamKg = g.items.reduce((sum, i) => sum + parseFloat(i.teslim_edilen_kg || 0), 0);
                    html += `
                        <div class="product-accordion" id="teacc-${idx}">
                            <div class="accordion-header" onclick="document.getElementById('teacc-${idx}').classList.toggle('open')">
                                <span class="product-code">👤 ${g.firma_adi}</span>
                                <div class="product-stats">
                                    <span class="stat-badge kg">${toplamKg.toFixed(1)} KG teslim</span>
                                    <span class="stat-badge customers">${g.items.length} ürün</span>
                                    <button class="btn-sm btn-teslim" style="margin-left:10px;" onclick="event.stopPropagation(); fisKapatVeYazdir('${g.firma_adi.replace(/'/g, "\\'")}', ${g.musteri_id})">✅ Fişi Kapat &amp; Yazdır</button>
                                </div>
                                <span class="accordion-chevron">▼</span>
                            </div>
                            <div class="accordion-body" id="teacc-body-${idx}">
                                <table>
                                    <thead><tr>
                                        <th>Ürün Kodu</th><th>Ürün Adı</th><th>Teslim Edilen (KG)</th><th>Tarih</th>
                                    </tr></thead>
                                    <tbody>`;
                    g.items.forEach(item => {
                        const tarih = item.tarih ? new Date(item.tarih).toLocaleDateString('tr-TR') : '-';
                        html += `<tr>
                            <td><strong>${item.urun_kodu}</strong></td>
                            <td>${item.urun_adi}</td>
                            <td>${parseFloat(item.teslim_edilen_kg).toFixed(1)} KG</td>
                            <td>${tarih}</td>
                        </tr>`;
                    });
                    html += `</tbody></table>
                                <div style="text-align:right; padding:10px; border-top:1px solid var(--border); margin-top:10px;">
                                    <strong>Toplam: ${toplamKg.toFixed(1)} KG</strong>
                                    <button class="btn-sm btn-teslim" style="margin-left:15px;" onclick="fisKapatVeYazdir('${g.firma_adi.replace(/'/g, "\\'")}', ${g.musteri_id})">✅ Fişi Kapat &amp; Yazdır</button>
                                </div>
                            </div>
                        </div>`;
                });
                container.innerHTML = html;
            } catch (e) {
                console.error(e);
                const c = document.getElementById('teslimEdilenlerContainer');
                if (c) c.innerHTML = '<div class="card" style="text-align:center; padding:30px; color:var(--danger);">Hata oluştu.</div>';
            }
        }

        // Teslimat hesabını kapat, DB'ye kaydet ve fişi yazdır
        async function fisKapatVeYazdir(firmaAdi, musteriId) {
            if (!confirm(`"${firmaAdi}" için teslimat hesabı kapatılacak ve fiş oluşturulacak.\n\nBundan sonra yapılacak teslimatlar ayrı bir fiş olarak kaydedilecektir.\n\nDevam etmek istiyor musunuz?`)) return;

            // Kur bilgisi yoksa tekrar çekmeyi dene
            if (currentUsdRate <= 0) {
                await fetchUsdRate();
            }

            try {
                // 1. DB'ye fiş olustur
                const res = await fetch('../api/siparis_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        action: 'teslimat_fis_olustur', 
                        musteri_id: musteriId,
                        usd_kuru: currentUsdRate
                    })
                });
                const r = await res.json();
                if (r.status !== 'success') {
                    alert('Hata: ' + (r.message || 'Bilinmeyen hata'));
                    return;
                }

                showNotification(r.mesaj || 'Fiş oluşturuldu!');

                // 2. Fişi yazdır (DB'den kalemler alarak)
                await printTeslimatFisi(firmaAdi, musteriId, r.fis_id);

                // 3. Teslim edilenler listesini yenile
                fetchTeslimEdilenler();

            } catch (e) {
                console.error(e);
                alert('Sistemsel hata oluştu.');
            }
        }

        // Teslimat Fişi Yazdır
        async function printTeslimatFisi(firmaAdi, musteriId, fisId = null) {
            try {
                // fis_id verilmişse doğrudan arşivden al, yoksa aktif teslim edilenlerden
                let data;
                if (fisId) {
                    const res2 = await fetch('../api/siparis_api.php?action=teslimat_fis_detay&fis_id=' + fisId + '&_t=' + Date.now(), { cache: 'no-store' });
                    const raw = await res2.json();
                    data = raw.map(r => ({ ...r, teslim_edilen_kg: r.teslim_edilen_kg, tarih: r.olusturma_tarihi }));
                } else {
                    const res2 = await fetch('../api/siparis_api.php?action=teslim_edilenler&musteri_id=' + musteriId + '&_t=' + Date.now(), { cache: 'no-store' });
                    data = await res2.json();
                }
                if (!data || data.length === 0) { alert('Bu müşteri için teslim edilen ürün bulunamadı.'); return; }

                const toplamKg = data.reduce((s, d) => s + parseFloat(d.teslim_edilen_kg || 0), 0);
                const bugün = new Date().toLocaleDateString('tr-TR', { year: 'numeric', month: 'long', day: 'numeric' });

                let satirlar = '';
                data.forEach((d, i) => {
                    satirlar += `<tr>
                        <td style="padding:6px 10px; border:1px solid #ddd; text-align:center;">${i + 1}</td>
                        <td style="padding:6px 10px; border:1px solid #ddd;">${d.urun_kodu}</td>
                        <td style="padding:6px 10px; border:1px solid #ddd;">${d.urun_adi}</td>
                        <td style="padding:6px 10px; border:1px solid #ddd; text-align:right;">${parseFloat(d.teslim_edilen_kg).toFixed(1)} KG</td>
                    </tr>`;
                });

                const printHTML = `
                    <html><head><title>Teslimat Fişi - ${firmaAdi}</title>
                    <style>
                        body { font-family: 'Segoe UI', Arial, sans-serif; padding: 30px; color: #333; }
                        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 15px; margin-bottom: 20px; }
                        .header h1 { margin: 0; font-size: 22px; }
                        .header p { margin: 5px 0; color: #666; }
                        .info { display: flex; justify-content: space-between; margin-bottom: 20px; }
                        .info div { font-size: 14px; }
                        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                        th { background: #f5f5f5; padding: 8px 10px; border: 1px solid #ddd; font-size: 13px; }
                        .total { text-align: right; font-size: 16px; font-weight: bold; margin-bottom: 40px; }
                        .sign-area { display: flex; justify-content: space-between; margin-top: 50px; }
                        .sign-box { width: 200px; text-align: center; }
                        .sign-box .line { border-top: 1px solid #333; margin-top: 60px; padding-top: 5px; }
                        @media print { body { padding: 15px; } }
                    </style></head><body>
                    <div class="header" style="display:none;"></div>
                    <div class="info">
                        <div><strong>Müşteri:</strong> ${firmaAdi}</div>
                        <div><strong>Tarih:</strong> ${bugün}</div>
                    </div>
                    <table>
                        <thead><tr>
                            <th>#</th><th>Ürün Kodu</th><th>Ürün Adı</th><th>Miktar (KG)</th>
                        </tr></thead>
                        <tbody>${satirlar}</tbody>
                    </table>
                    <div class="total">Toplam Teslim: ${toplamKg.toFixed(1)} KG</div>
                    <div class="sign-area">
                        <div class="sign-box">
                            <div class="line">Teslim Eden</div>
                        </div>
                        <div class="sign-box">
                            <div class="line">Teslim Alan</div>
                        </div>
                    </div>
                    </body></html>`;

                // Blob URL ile aç — Chrome popup blocker'ı aşar
                const blob = new Blob([printHTML], { type: 'text/html; charset=utf-8' });
                const blobUrl = URL.createObjectURL(blob);
                const w = window.open(blobUrl, '_blank');
                if (w) {
                    w.onload = () => { setTimeout(() => { w.print(); URL.revokeObjectURL(blobUrl); }, 300); };
                } else {
                    // Popup engellenirse aynı sekmede aç
                    const a = document.createElement('a');
                    a.href = blobUrl;
                    a.target = '_blank';
                    a.click();
                }
            } catch (e) {
                console.error(e);
                alert('Fiş oluşturulurken hata oluştu.');
            }
        }

        async function updateDetayDurum(detayId, yeniDurum, showConfirm = true) {
            const durumLabels = {
                'tedarik': 'Listeye Ekle',
                'geldi': 'Geldi',
                'tamamlandi': 'Teslim Edildi',
                'beklemede': 'Beklemede (Geri Al)',
                'iptal': 'İptal'
            };
            // Chrome'da confirm() dinamik içeriklerde çalışmadığından kaldırıldı

            try {
                const res = await fetch('../api/siparis_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'detay_durum_guncelle', detay_id: detayId, yeni_durum: yeniDurum })
                });
                const result = await res.json();
                if (result.status === 'success') {
                    showNotification('Sipariş durumu güncellendi!');
                    if (aktifSipTab === 'musteri') fetchMusteriSiparisleri();
                    else if (aktifSipTab === 'tedarik') fetchTedarikListesi();
                    else if (aktifSipTab === 'teslimat') refreshTeslimatTab();
                } else {
                    alert('Hata: ' + (result.message || 'Bilinmeyen hata'));
                }
            } catch (err) {
                alert('Sistemsel hata oluştu.');
            }
        }

        // =====================================
        // TAB 4: TESLİMAT FİŞLERİ
        // =====================================
        let teslimatFisMusteriLoaded = false;

        function setTeslimatFisTarih(period, btn) {
            // Aktif chip'ı güncelle
            document.querySelectorAll('.date-chip').forEach(c => c.classList.remove('active'));
            if (btn) btn.classList.add('active');

            const basEl = document.getElementById('teslimatFisTarihBas');
            const sonEl = document.getElementById('teslimatFisTarihSon');
            const customEl = document.getElementById('customDateRange');

            const today = new Date();
            const fmt = d => d.toISOString().split('T')[0];

            if (period === 'custom') {
                if (customEl) customEl.style.display = 'flex';
                return; // Özel aralıkta tarih seçimini kullanıcıya bırak
            }
            if (customEl) customEl.style.display = 'none';

            let bas = '', son = '';
            if (period === 'today') {
                bas = son = fmt(today);
            } else if (period === 'yesterday') {
                const d = new Date(today); d.setDate(d.getDate() - 1);
                bas = son = fmt(d);
            } else if (period === 'week') {
                const d = new Date(today);
                const day = d.getDay() || 7; // Pazartesi = 1
                d.setDate(d.getDate() - day + 1);
                bas = fmt(d);
                son = fmt(today);
            } else if (period === 'month') {
                bas = fmt(new Date(today.getFullYear(), today.getMonth(), 1));
                son = fmt(today);
            } else if (period === '7days') {
                const d = new Date(today); d.setDate(d.getDate() - 6);
                bas = fmt(d);
                son = fmt(today);
            } else if (period === '30days') {
                const d = new Date(today); d.setDate(d.getDate() - 29);
                bas = fmt(d);
                son = fmt(today);
            }
            // 'all' → boş bırak
            if (basEl) basEl.value = bas;
            if (sonEl) sonEl.value = son;
            loadTeslimatFisleri();
        }

        async function initTeslimatFisleri() {
            if (!teslimatFisMusteriLoaded) {
                try {
                    const res = await fetch('../api/siparis_api.php?action=musteriler_listesi');
                    const data = await res.json();
                    const sel = document.getElementById('teslimatFisMusteriFilter');
                    if (sel) {
                        sel.innerHTML = '<option value="">Tüm Müşteriler</option>';
                        data.forEach(m => {
                            sel.innerHTML += `<option value="${m.id}">${m.firma_adi}</option>`;
                        });
                    }
                    teslimatFisMusteriLoaded = true;
                } catch (e) { console.error(e); }
            }
            loadTeslimatFisleri();
        }

        async function loadTeslimatFisleri() {
            const musteriId = document.getElementById('teslimatFisMusteriFilter')?.value || '';
            const tarihBas = document.getElementById('teslimatFisTarihBas')?.value || '';
            const tarihSon = document.getElementById('teslimatFisTarihSon')?.value || '';
            let url = '../api/siparis_api.php?action=teslimat_arsiv&_t=' + Date.now();
            if (musteriId) url += '&musteri_id=' + musteriId;
            if (tarihBas) url += '&tarih_bas=' + tarihBas;
            if (tarihSon) url += '&tarih_son=' + tarihSon;

            const container = document.getElementById('teslimatFisiAccordionContainer');
            if (!container) return;
            container.innerHTML = '<div style="text-align:center; padding:40px; color:var(--text-main);">Yükleniyor...</div>';

            try {
                const res = await fetch(url, { cache: 'no-store' });
                const data = await res.json();

                // teslimat_arsiv apisi paginated döner: { status, data: [], total_pages, ... }
                const results = data.data || [];

                if (results.length === 0) {
                    container.innerHTML = '<div class="card" style="text-align:center; padding:30px;">Arşivlenmiş fiş bulunamadı.</div>';
                    return;
                }

                renderTeslimatFisiAccordion(results, container);
            } catch (e) {
                console.error(e);
                container.innerHTML = '<div class="card" style="text-align:center; padding:30px; color:var(--danger);">Hata oluştu.</div>';
            }
        }

        function renderTeslimatFisiAccordion(results, container) {
            let html = '';
            results.forEach((fis, idx) => {
                const tarih = new Date(fis.olusturma_tarihi).toLocaleString('tr-TR');
                
                html += `
                    <div class="product-accordion" id="tfacc-${idx}">
                        <div class="accordion-header" onclick="toggleTeslimatFisDetay(${idx}, ${fis.fis_id})">
                            <span class="product-name" style="width:250px; font-weight:700; color:var(--accent); font-size:1.05rem; text-align:left; margin-left:0;">${fis.firma_adi}</span>
                            <div class="product-stats" style="flex:1; justify-content:flex-start; gap:30px;">
                                <span style="font-size:0.85rem; color:var(--text-main); width:130px; text-align:left;">${tarih}</span>
                                <span class="stat-badge kg" style="width:80px; text-align:left;">${parseFloat(fis.toplam_kg).toFixed(1)} KG</span>
                                <span class="stat-badge customers" style="width:80px; text-align:left;">${fis.urun_sayisi} kalem</span>
                                <span class="stat-badge tamamlandi" style="width:90px; text-align:left;">$ ${parseFloat(fis.toplam_usd).toFixed(2)}</span>
                            </div>
                            <button class="btn-sm btn-teslim" style="margin-left:auto; margin-right:15px;" onclick="event.stopPropagation(); printTeslimatFisiProfesyonel(${fis.musteri_id}, ${fis.fis_id})">🖨️ Yazdır</button>
                            <span class="accordion-chevron">▼</span>
                        </div>
                        <div class="accordion-body" id="tfacc-body-${idx}" style="padding:0;">
                            <div style="padding:20px; text-align:center; color:var(--text-main);">Detaylar yükleniyor...</div>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        }

        async function toggleTeslimatFisDetay(idx, fisId) {
            const el = document.getElementById('tfacc-' + idx);
            const body = document.getElementById('tfacc-body-' + idx);
            if (el.classList.contains('open')) { el.classList.remove('open'); return; }
            
            el.classList.add('open');
            
            try {
                const res = await fetch('../api/siparis_api.php?action=teslimat_fis_detay&fis_id=' + fisId);
                const data = await res.json();
                
                if (!data || data.length === 0) {
                    body.innerHTML = '<div style="padding:20px; text-align:center;">Kalem bulunamadı.</div>';
                    return;
                }

                let rows = '';
                data.forEach((d, i) => {
                    const birimFiyat = parseFloat(d.usd_fiyat || 0);
                    const net = parseFloat(d.teslim_edilen_kg) * birimFiyat * (1 - parseFloat(d.iskonto_orani || 0)/100);
                    rows += `
                        <tr>
                            <td>${i+1}</td>
                            <td><strong>${d.urun_kodu}</strong></td>
                            <td>${d.urun_adi}</td>
                            <td>${parseFloat(d.teslim_edilen_kg).toFixed(1)} KG</td>
                            <td>$ ${birimFiyat.toFixed(2)}</td>
                            <td>%${parseFloat(d.iskonto_orani || 0)}</td>
                            <td>$ ${net.toFixed(2)}</td>
                        </tr>
                    `;
                });

                body.innerHTML = `
                    <table style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr style="background:rgba(255,255,255,0.03);">
                                <th>#</th><th>Ürün Kodu</th><th>Ürün Adı</th><th>Miktar</th><th>Birim Fiyat</th><th>İskonto</th><th>Net Tutar</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                `;
            } catch (err) {
                body.innerHTML = '<div style="padding:20px; color:var(--danger); text-align:center;">Detaylar yüklenemedi.</div>';
            }
        }

        function renderTeslimEdilenlerAccordion(data) {
            const container = document.getElementById('teslimEdilenlerContainer');
            if (!container) return;
            if (!data || !Array.isArray(data) || data.length === 0) {
                container.innerHTML = '<div style="text-align:center; padding:30px; color:var(--text-main);">Teslim edilmiş sipariş bulunamadı.</div>';
                return;
            }

            let grouped = {};
            data.forEach(item => {
                if (!grouped[item.musteri_id]) {
                    grouped[item.musteri_id] = {
                        firma_adi: item.firma_adi,
                        musteri_id: item.musteri_id,
                        iskonto_orani: parseFloat(item.iskonto_orani || 0),
                        items: []
                    };
                }
                grouped[item.musteri_id].items.push(item);
            });

            let html = '';
            Object.values(grouped).forEach((g, idx) => {
                let brutToplam = 0;
                let netToplam = 0;
                g.items.forEach(it => {
                    const brut = parseFloat(it.teslim_edilen_kg) * parseFloat(it.usd_fiyat);
                    brutToplam += brut;
                    netToplam += (brut - (brut * g.iskonto_orani / 100));
                });

                html += `
                    <div class="product-accordion" id="teacc-${idx}">
                        <div class="accordion-header" onclick="toggleAccordion('teacc-${idx}')">
                            <span class="product-code">👤 ${g.firma_adi}</span>
                            <span style="margin-left:15px; flex:1; color:var(--text-main); font-size:0.85rem;">${g.items.length} kalem teslimat</span>
                            <div class="product-stats">
                                <span class="stat-badge customers" style="background:rgba(27,197,189,0.1); color:var(--success);">$ ${netToplam.toFixed(2)}</span>
                                <button class="btn-sm btn-teslim" onclick="event.stopPropagation(); printTeslimatFisiProfesyonel(${g.musteri_id})">🖨️ Fiş Yazdır</button>
                            </div>
                            <span class="accordion-chevron">▼</span>
                        </div>
                        <div class="accordion-body" id="teacc-body-${idx}">
                            <table style="width:100%; border-collapse:collapse; margin-top:10px;">
                                <thead><tr>
                                    <th>#</th><th>Ürün</th><th>Miktar</th><th>Fiyat</th><th>Net</th><th>Tarih</th>
                                </tr></thead>
                                <tbody>`;
                g.items.forEach((item, i) => {
                    const miktar = parseFloat(item.teslim_edilen_kg || 0);
                    const birimFiyat = parseFloat(item.usd_fiyat || 0);
                    const brut = miktar * birimFiyat;
                    const net = brut - (brut * g.iskonto_orani / 100);
                    const tarih = item.tarih ? new Date(item.tarih).toLocaleDateString('tr-TR') : '-';
                    html += `<tr>
                        <td>${i + 1}</td>
                        <td><strong>${item.urun_kodu}</strong><br><small>${item.urun_adi}</small></td>
                        <td>${miktar.toFixed(1)} KG</td>
                        <td>$ ${birimFiyat.toFixed(2)}</td>
                        <td style="color:var(--success);font-weight:600;">$ ${net.toFixed(2)}</td>
                        <td>${tarih}</td>
                    </tr>`;
                });
                html += `</tbody></table>
                            <div style="padding:12px 15px; border-top:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center;">
                                <div style="font-size:0.88rem; color:var(--text-main);">Brüt: $ ${brutToplam.toFixed(2)} | İskonto: % ${g.iskonto_orani.toFixed(1)}</div>
                                <div style="display:flex; gap:10px;">
                                    <span style="font-size:1rem; color:var(--success); font-weight:700;">Net: $ ${netToplam.toFixed(2)}</span>
                                    <button class="btn-sm btn-close-fis" onclick="closeTeslimatAccount(${g.musteri_id})">🏁 Hesabı Kapat & Yazdır</button>
                                </div>
                            </div>
                        </div>
                    </div>`;
            });
            container.innerHTML = html;
        }

        async function closeTeslimatAccount(musteriId) {
            if (!confirm('Bu müşterinin teslimat hesabını kapatıp fiş numarasını arşivlemek istediğinize emin misiniz?')) return;
            try {
                const res = await fetch('../api/siparis_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'teslimat_fis_olustur', musteri_id: musteriId, usd_kuru: currentUsdRate })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    showNotification('Hesap başarıyla kapatıldı ve fiş oluşturuldu!');
                    // Otomatik yazdır
                    printTeslimatFisiProfesyonel(musteriId, data.fis_id);
                    refreshTeslimatTab();
                } else {
                    alert('Hata: ' + data.message);
                }
            } catch (err) {
                console.error(err);
                alert('İşlem sırasında bir hata oluştu.');
            }
        }

        async function printTeslimatFisiProfesyonel(musteriId, fisId = null) {
            let url;
            if (fisId) {
                url = '../api/siparis_api.php?action=teslimat_fis_detay&fis_id=' + fisId + '&_t=' + Date.now();
            } else {
                const tarihBas = document.getElementById('teslimatFisTarihBas')?.value || '';
                const tarihSon = document.getElementById('teslimatFisTarihSon')?.value || '';
                url = '../api/siparis_api.php?action=teslim_edilenler&musteri_id=' + musteriId + '&_t=' + Date.now();
                if (tarihBas) url += '&tarih_bas=' + tarihBas;
                if (tarihSon) url += '&tarih_son=' + tarihSon;
            }

            try {
                const res = await fetch(url, { cache: 'no-store' });
                const data = await res.json();
                if (!data || data.length === 0) { alert('Bu fiş/müşteri için kalem bulunamadı.'); return; }

                const firmaAdi = data[0].firma_adi;
                const telefon = data[0].telefon || '-';
                const iskonto = parseFloat(data[0].iskonto_orani || 0);
                const bugun = new Date().toLocaleDateString('tr-TR', { year: 'numeric', month: 'long', day: 'numeric' });
                const fisNo = 'TF-' + musteriId + '-' + Date.now().toString().slice(-6);

                let satirlar = '';
                let genelBrut = 0;
                if (Array.isArray(data)) {
                    data.forEach((d, i) => {
                        const miktar = parseFloat(d.teslim_edilen_kg || 0);
                        const birimFiyat = parseFloat(d.usd_fiyat || 0);
                        const brut = miktar * birimFiyat;
                        const iskontoTutar = brut * (iskonto / 100);
                        const net = brut - iskontoTutar;
                        genelBrut += brut;
                        satirlar += `<tr>
                            <td style="padding:8px 12px; border:1px solid #ddd; text-align:center; font-size:13px;">${i + 1}</td>
                            <td style="padding:8px 12px; border:1px solid #ddd; font-weight:600; font-size:13px;">${d.urun_kodu}</td>
                            <td style="padding:8px 12px; border:1px solid #ddd; font-size:13px;">${d.urun_adi}</td>
                            <td style="padding:8px 12px; border:1px solid #ddd; text-align:right; font-size:13px;">${miktar.toFixed(1)}</td>
                            <td style="padding:8px 12px; border:1px solid #ddd; text-align:right; font-size:13px;">$ ${birimFiyat.toFixed(2)}</td>
                            <td style="padding:8px 12px; border:1px solid #ddd; text-align:center; font-size:13px;">% ${iskonto.toFixed(1)}</td>
                            <td style="padding:8px 12px; border:1px solid #ddd; text-align:right; font-weight:600; font-size:13px;">$ ${net.toFixed(2)}</td>
                        </tr>`;
                    });
                }

                const genelIskonto = genelBrut * (iskonto / 100);
                const genelNet = genelBrut - genelIskonto;
                const genelTL = currentUsdRate > 0 ? genelNet * currentUsdRate : 0;

                const printHTML = `<html><head><title>Teslimat Fişi - ${firmaAdi}</title>
                <style>
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    body { font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif; padding: 35px 40px; color: #222; line-height: 1.5; }
                    .fis-header { text-align: center; padding-bottom: 18px; margin-bottom: 22px; border-bottom: 3px double #333; }
                    .fis-header h1 { font-size: 26px; font-weight: 800; letter-spacing: 2px; margin-bottom: 2px; }
                    .fis-header .subtitle { font-size: 14px; color: #555; letter-spacing: 1px; text-transform: uppercase; }
                    .fis-info { display: flex; justify-content: space-between; margin-bottom: 22px; padding: 14px 18px; background: #f8f9fa; border-radius: 6px; border: 1px solid #e9ecef; }
                    .fis-info .col { line-height: 1.9; font-size: 13px; }
                    .fis-info .col strong { color: #333; }
                    table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
                    th { background: #343a40; color: #fff; padding: 9px 12px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; border: 1px solid #343a40; }
                    td { font-size: 13px; }
                    tbody tr:nth-child(even) { background: #f8f9fa; }
                    .totals-box { margin-top: 0; border: 2px solid #343a40; border-top: none; border-radius: 0 0 6px 6px; overflow: hidden; }
                    .totals-row { display: flex; justify-content: flex-end; padding: 6px 18px; font-size: 13px; border-bottom: 1px solid #e9ecef; }
                    .totals-row:last-child { border-bottom: none; }
                    .totals-row .label { width: 160px; text-align: right; color: #555; }
                    .totals-row .value { width: 140px; text-align: right; font-weight: 600; }
                    .totals-row.grand { background: #343a40; color: #fff; padding: 10px 18px; font-size: 15px; }
                    .totals-row.grand .label { color: #ccc; }
                    .totals-row.grand .value { font-size: 16px; }
                    .kur-info { text-align: right; margin-top: 8px; font-size: 11px; color: #888; }
                    .sign-area { display: flex; justify-content: space-between; margin-top: 60px; }
                    .sign-box { width: 200px; text-align: center; }
                    .sign-box .line { border-top: 1px solid #333; margin-top: 55px; padding-top: 8px; font-size: 13px; color: #555; }
                    .footer { text-align: center; margin-top: 30px; padding-top: 12px; border-top: 1px solid #ddd; font-size: 11px; color: #aaa; }
                    @media print {
                        body { padding: 20px 25px; }
                        .fis-info { background: #f8f9fa !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                        th { background: #343a40 !important; color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                        tbody tr:nth-child(even) { background: #f8f9fa !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                        .totals-row.grand { background: #343a40 !important; color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                    }
                </style></head><body>
                <div class="fis-header" style="display:none;"></div>
                <div class="fis-info">
                    <div class="col">
                        <strong>Müşteri:</strong> ${firmaAdi}<br>
                        <strong>Düzenlenme Tarihi:</strong> ${bugun}
                    </div>
                    <div class="col" style="text-align:right;">
                        <strong>Fiş No:</strong> ${fisNo}<br>
                        <strong>Kalem Sayısı:</strong> ${data.length}
                    </div>
                </div>
                <table>
                    <thead><tr>
                        <th style="width:30px; text-align:center;">#</th>
                        <th>Ürün Kodu</th>
                        <th>Ürün Adı</th>
                        <th style="text-align:right;">Miktar (KG)</th>
                        <th style="text-align:right;">Birim Fiyat</th>
                        <th style="text-align:center;">İskonto</th>
                        <th style="text-align:right;">Net Tutar</th>
                    </tr></thead>
                    <tbody>${satirlar}</tbody>
                </table>
                <div class="totals-box">
                    <div class="totals-row">
                        <span class="label">Brüt Toplam (USD):</span>
                        <span class="value">$ ${genelBrut.toFixed(2)}</span>
                    </div>
                    <div class="totals-row">
                        <span class="label">İskonto (% ${iskonto.toFixed(1)}):</span>
                        <span class="value" style="color:#dc3545;">- $ ${genelIskonto.toFixed(2)}</span>
                    </div>
                    <div class="totals-row grand">
                        <span class="label">Net Toplam (USD):</span>
                        <span class="value">$ ${genelNet.toFixed(2)}</span>
                    </div>
                    <div class="totals-row grand">
                        <span class="label">Net Toplam (TL):</span>
                        <span class="value">₺ ${genelTL.toFixed(2)}</span>
                    </div>
                </div>
                <div class="kur-info">Güncel Kur: 1 USD = ${currentUsdRate.toFixed(2)} TL (${bugun})</div>
                <div class="sign-area">
                    <div class="sign-box"><div class="line">Teslim Eden</div></div>
                    <div class="sign-box"><div class="line">Teslim Alan</div></div>
                </div>
                <div class="footer">Bu belge AKSA TOPTAN tarafından düzenlenmiştir.</div>
                </body></html>`;

                const blob = new Blob([printHTML], { type: 'text/html; charset=utf-8' });
                const blobUrl = URL.createObjectURL(blob);
                const w = window.open(blobUrl, '_blank');
                if (w) {
                    w.onload = () => { setTimeout(() => { w.print(); URL.revokeObjectURL(blobUrl); }, 400); };
                } else {
                    const a = document.createElement('a');
                    a.href = blobUrl;
                    a.target = '_blank';
                    a.click();
                }
            } catch (e) {
                console.error(e);
                alert('Fiş oluşturulurken hata oluştu.');
            }
        }

        async function fetchTeslimEdilenlerLegacy() {
            const musteriId = document.getElementById('teslimatMusteriFilter')?.value || '';
            let url = '../api/siparis_api.php?action=teslim_edilenler';
            if (musteriId) url += '&musteri_id=' + musteriId;
            try {
                const res = await fetch(url);
                const data = await res.json();
                const tbody = document.getElementById('teslimEdilenlerBody');
                if (!tbody) return;
                tbody.innerHTML = '';

                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">Teslim edilen sipariş bulunamadı.</td></tr>';
                    return;
                }

                data.forEach(d => {
                    const tarihStr = d.tarih ? new Date(d.tarih).toLocaleDateString('tr-TR') : '-';
                    tbody.innerHTML += `
                        <tr>
                            <td><strong style="color:var(--accent)">${d.urun_kodu}</strong></td>
                            <td>${d.urun_adi}</td>
                            <td>${d.firma_adi}</td>
                            <td style="color:var(--success); font-weight:600;">${parseFloat(d.teslim_edilen_kg).toFixed(1)} KG</td>
                            <td>${tarihStr}</td>
                            <td>
                                <button class="btn-sm" style="background:#17a2b8;" onclick="printSiparisFisi(${d.detay_id}, '${d.firma_adi}', '${d.urun_kodu}', '${d.urun_adi}', ${d.teslim_edilen_kg}, '${d.tarih || ''}', ${d.usd_fiyat || 0}, ${d.iskonto_orani || 0}, '${d.telefon || ''}')">🖨️ Fiş Yazdır</button>
                            </td>
                        </tr>
                    `;
                });
            } catch (err) {
                console.error('Teslim edilenler çekilirken hata:', err);
            }
        }

        function printSiparisFisi(detayId, firmaAdi, urunKodu, urunAdi, miktar, tarih, usdFiyat, iskonto, telefon) {
            const brutTutar = miktar * usdFiyat;
            const iskontoTutar = brutTutar * (iskonto / 100);
            const netTutar = brutTutar - iskontoTutar;
            const tlTutar = currentUsdRate > 0 ? netTutar * currentUsdRate : 0;
            const tarihStr = tarih ? new Date(tarih).toLocaleDateString('tr-TR') : new Date().toLocaleDateString('tr-TR');

            let printWindow = window.open('', '_blank');
            let html = `
                <html>
                <head>
                    <title>Teslim Fişi</title>
                    <style>
                        body { font-family: 'Segoe UI', sans-serif; padding: 40px; color: #333; }
                        .header { text-align: center; border-bottom: 3px solid #222; margin-bottom: 30px; padding-bottom:15px; }
                        .header h1 { margin: 0; font-size: 1.8rem; }
                        .header p { margin: 5px 0 0; color: #666; font-size: 1rem; }
                        .info { margin-bottom: 25px; display: flex; justify-content: space-between; }
                        .info div { line-height: 1.8; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                        th, td { border: 1px solid #444; padding: 12px; text-align: left; }
                        th { background: #f5f5f5; font-weight: 600; }
                        .totals { text-align: right; margin-top: 25px; font-size: 1.15rem; line-height: 2; }
                        .signature { margin-top: 60px; display:flex; justify-content: space-between; }
                        .signature div { text-align:center; width: 200px; border-top: 1px solid #000; padding-top:10px; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>AKSA TOPTAN</h1>
                        <p>TESLİMAT FİŞİ</p>
                    </div>
                    <div class="info">
                        <div>
                            <strong>Müşteri:</strong> ${firmaAdi}<br>
                            <strong>Tel:</strong> ${telefon || '-'}
                        </div>
                        <div>
                            <strong>Tarih:</strong> ${tarihStr}<br>
                            <strong>Fiş No:</strong> #${detayId}
                        </div>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Ürün Kodu</th>
                                <th>Ürün Adı</th>
                                <th>Miktar (KG)</th>
                                <th>Birim Fiyat (USD)</th>
                                <th>İskonto (%)</th>
                                <th>Net Tutar (USD)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>${urunKodu}</td>
                                <td>${urunAdi}</td>
                                <td>${parseFloat(miktar).toFixed(1)}</td>
                                <td>$ ${parseFloat(usdFiyat).toFixed(2)}</td>
                                <td>% ${parseFloat(iskonto).toFixed(1)}</td>
                                <td><strong>$ ${netTutar.toFixed(2)}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="totals">
                        <strong>Toplam USD:</strong> $ ${netTutar.toFixed(2)}<br>
                        <strong>Toplam TL:</strong> ₺ ${tlTutar.toFixed(2)}
                    </div>
                    <div class="signature">
                        <div>Teslim Eden</div>
                        <div>Teslim Alan</div>
                    </div>
                    <script>window.onload = function() { window.print(); window.close(); };<\/script>
                </body>
                </html>
            `;
            printWindow.document.write(html);
            printWindow.document.close();
        }

        // --- TAKSİM / TESLİMAT YÖNETİMİ ---
        function loadTeslimatlar() {
            const html = `
                <div class="card">
                    <div class="card-title">Bekleyen Ürünler (Tedarikçiden Gelecekler)</div>
                    <table>
                        <thead>
                            <tr>
                                <th>Ürün Kodu</th>
                                <th>Ürün Adı</th>
                                <th>Toplam Bekleyen (KG)</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody id="bekleyenUrunlerBody">
                            <tr><td colspan="4" style="text-align:center;">Yükleniyor...</td></tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="card" id="dagitimCard" style="display:none; margin-top: 30px; border-top: 2px solid var(--accent);">
                    <div class="card-title" id="dagitimTitle">Ürün Dağıtımı</div>
                    <form id="dagitimForm" onsubmit="saveDagitim(event)">
                        <input type="hidden" id="d_urun_kodu">
                        <table>
                            <thead>
                                <tr>
                                    <th>Sipariş Tarihi</th>
                                    <th>Müşteri Firması</th>
                                    <th>İstenen (KG)</th>
                                    <th>Kalan (KG)</th>
                                    <th>Verilecek Miktar (KG)</th>
                                </tr>
                            </thead>
                            <tbody id="dagitimMusterilerBody">
                            </tbody>
                        </table>
                        <br>
                        <button type="submit" style="background-color: var(--success); width: 100%;">Dağıtımı Onayla ve Kaydet</button>
                    </form>
                </div>

                <div class="card" style="margin-top: 50px;">
                    <div class="card-title">Son Teslimatlar (Sevkiyat Geçmişi)</div>
                    <table>
                        <thead>
                            <tr>
                                <th>Tarih</th>
                                <th>Müşteri Firması</th>
                                <th>Tutar</th>
                                <th>İşlem</th>
                            </tr>
                        </thead>
                        <tbody id="teslimatGecmisiBody">
                            <tr><td colspan="4" style="text-align:center;">Yükleniyor...</td></tr>
                        </tbody>
                    </table>
                </div>
            `;
            document.getElementById('content-area').innerHTML = html;
            fetchBekleyenUrunler();
            fetchTeslimatGecmisi();
        }

        async function fetchTeslimatGecmisi() {
            try {
                const res = await fetch('../api/teslimat_api.php?action=liste');
                const data = await res.json();
                const tbody = document.getElementById('teslimatGecmisiBody');
                tbody.innerHTML = '';
                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">Kayıt yok.</td></tr>';
                    return;
                }
                data.forEach(t => {
                    tbody.innerHTML += `
                        <tr>
                            <td>${t.tarih}</td>
                            <td><strong>${t.firma_adi}</strong></td>
                            <td><b style="color:var(--success)">$ ${parseFloat(t.toplam_usd).toFixed(2)}</b> (₺ ${parseFloat(t.toplam_tl).toFixed(2)})</td>
                            <td>
                                <button onclick="printTeslimatFişi(${t.id})" style="padding: 4px 10px; background-color:#17a2b8;">Fiş Yazdır</button>
                            </td>
                        </tr>
                    `;
                });
            } catch (err) { console.error(err); }
        }

        async function printTeslimatFişi(id) {
            try {
                const res = await fetch('../api/teslimat_api.php?action=detay&id=' + id);
                const data = await res.json();
                const t = data.teslimat;
                const detaylar = data.detaylar;

                let printWindow = window.open('', '_blank');
                let html = `
                    <html>
                    <head>
                        <title>Teslim Fişi - #${id}</title>
                        <style>
                            body { font-family: sans-serif; padding: 40px; }
                            .header { text-align: center; border-bottom: 2px solid #000; margin-bottom: 30px; padding-bottom:10px; }
                 .info { margin-bottom: 20px; display: flex; justify-content: space-between; }
                            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                            th, td { border: 1px solid #000; padding: 10px; text-align: left; }
                            .totals { text-align: right; margin-top: 20px; font-weight: bold; font-size: 1.2rem; }
                        </style>
                    </head>
                    <body>
                        <div class="header">
                            <h1>AKSA TOPTAN</h1>
                            <p>TESLİMAT FİŞİ</p>
                        </div>
                        <div class="info">
                            <div>
                                <strong>Müşteri:</strong> ${t.firma_adi}<br>
                                <strong>Tel:</strong> ${t.telefon || '-'}
                            </div>
                            <div>
                                <strong>Fiş No:</strong> #${t.id}<br>
                                <strong>Tarih:</strong> ${t.tarih}
                            </div>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Ürün Kodu</th>
                                    <th>Ürün Adı</th>
                                    <th>Miktar (KG)</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                detaylar.forEach(d => {
                    html += `
                        <tr>
                            <td>${d.urun_kodu}</td>
                            <td>${d.urun_adi}</td>
                            <td>${d.teslim_edilen_kg} KG</td>
                        </tr>
                    `;
                });

                html += `
                            </tbody>
                        </table>
                        <div class="totals">
                            Toplam: $ ${parseFloat(t.toplam_usd).toFixed(2)} / ₺ ${parseFloat(t.toplam_tl).toFixed(2)}
                        </div>
                        <div style="margin-top: 50px; display:flex; justify-content: space-between;">
                            <div style="text-align:center; width: 200px; border-top: 1px solid #000; padding-top:10px;">Teslim Eden</div>
                            <div style="text-align:center; width: 200px; border-top: 1px solid #000; padding-top:10px;">Teslim Alan</div>
                        </div>
                        <script>window.onload = function() { window.print(); window.close(); };<\/script>
                    </body>
                    </html>
                `;
                printWindow.document.write(html);
                printWindow.document.close();
            } catch (err) { alert('Fiş oluşturulurken hata oluştu.'); }
        }

        async function fetchBekleyenUrunler() {
            try {
                const response = await fetch('../api/teslimat_api.php?action=bekleyen_urunler');
                const data = await response.json();
                const tbody = document.getElementById('bekleyenUrunlerBody');
                tbody.innerHTML = '';

                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">Bekleyen ürün bulunamadı. Tüm siparişler teslim edilmiş.</td></tr>';
                    document.getElementById('dagitimCard').style.display = 'none';
                    return;
                }

                data.forEach(u => {
                    tbody.innerHTML += `
                        <tr>
                            <td><strong>${u.urun_kodu}</strong></td>
                            <td>${u.urun_adi}</td>
                            <td><span style="color: var(--danger); font-weight: bold;">${u.toplam_bekleyen_kg} KG</span></td>
                            <td>
                                <button onclick="loadUrunDagitim('${u.urun_kodu}', '${u.urun_adi}')" style="padding: 5px 15px;">Dağıt (Taksim Et)</button>
                            </td>
                        </tr>
                    `;
                });
            } catch (err) {
                console.error('Bekleyen ürünler çekilirken hata:', err);
            }
        }

        async function loadUrunDagitim(urun_kodu, urun_adi) {
            try {
                const response = await fetch('../api/teslimat_api.php?action=bekleyen_musteriler&urun_kodu=' + urun_kodu);
                const data = await response.json();

                document.getElementById('dagitimTitle').innerText = `${urun_adi} (${urun_kodu}) - Müşterilere Dağıtım`;
                document.getElementById('d_urun_kodu').value = urun_kodu;

                const tbody = document.getElementById('dagitimMusterilerBody');
                tbody.innerHTML = '';

                if (!Array.isArray(data) || data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Kayıt bulunamadı.</td></tr>';
                } else {
                    data.forEach(d => {
                        tbody.innerHTML += `
                            <tr>
                                <td>${d.tarih}</td>
                                <td><strong>${d.firma_adi}</strong></td>
                                <td>${d.istenen_kg} KG</td>
                                <td><span style="color: var(--danger);">${d.kalan_kg} KG</span></td>
                                <td>
                                    <input type="number" step="0.01" min="0" max="${d.kalan_kg}" 
                                        class="dagitim-input" 
                                        data-detay-id="${d.detay_id}" 
                                        data-musteri-id="${d.musteri_id}" 
                                        data-siparis-id="${d.siparis_id}" 
                                        onfocus="this.select()"
                                        placeholder="Miktar girin" style="width:150px;">
                                </td>
                            </tr>
                        `;
                    });
                }

                document.getElementById('dagitimCard').style.display = 'block';
                document.getElementById('dagitimCard').scrollIntoView({ behavior: 'smooth' });

            } catch (err) {
                alert('Müşteri listesi getirilirken hata oluştu.');
            }
        }

        async function saveDagitim(e) {
            e.preventDefault();

            const urun_kodu = document.getElementById('d_urun_kodu').value;
            const inputs = document.querySelectorAll('.dagitim-input');
            const dagitimlar = [];

            let totalDagitilan = 0;

            inputs.forEach(input => {
                const miktar = parseFloat(input.value);
                if (miktar > 0) {
                    dagitimlar.push({
                        detay_id: input.getAttribute('data-detay-id'),
                        musteri_id: input.getAttribute('data-musteri-id'),
                        siparis_id: input.getAttribute('data-siparis-id'),
                        miktar: miktar
                    });
                    totalDagitilan += miktar;
                }
            });

            if (dagitimlar.length === 0) {
                alert('Lütfen en az bir müşteriye verilecek miktarı giriniz.');
                return;
            }

            if (!confirm(`Toplam ${totalDagitilan} KG ${urun_kodu} ürünü dağıtılacak.Onaylıyor musunuz ?`)) return;

            try {
                const response = await fetch('../api/teslimat_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'dagitim_yap', urun_kodu: urun_kodu, dagitimlar: dagitimlar })
                });

                const result = await response.json();
                if (result.status === 'success') {
                    showNotification('Dağıtım başarıyla kaydedildi!');
                    document.getElementById('dagitimCard').style.display = 'none';
                    fetchBekleyenUrunler();
                } else {
                    alert('Hata: ' + result.message);
                }
            } catch (err) {
                alert('Sistemsel hata oluştu.');
            }
        }

        // Yardımcı Fonksiyon: Bildirim Gösterme
        function showNotification(message, type = 'success') {
            const noti = document.getElementById('notification');
            if (!noti) return;
            noti.textContent = message;
            noti.style.background = type === 'error' ? 'var(--danger)' : 'var(--success)';
            noti.style.display = 'block';
            setTimeout(() => { noti.style.display = 'none'; }, 3000);
        }
    </script>
</body>

</html>