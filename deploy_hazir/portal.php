<?php
session_start();
// Eğer admin girişli olursa buraya girmesini engelleyebilirsiniz ama şimdilik müşteri üzerinden gidelim.
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Sipariş Portalı - AKSA Toptan</title>
    <style>
        :root {
            --primary-bg: #121212;
            --card-bg: #1e1e2d;
            --text-main: #e0e0e0;
            --accent: #1bc5bd;
            --accent-hover: #15a39d;
            --danger: #f64e60;
            --border: #2b2b40;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--primary-bg);
            color: var(--text-main);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            width: 100%;
            max-width: 1100px;
            padding: 20px;
        }

        .card {
            background-color: var(--card-bg);
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
        }

        h2 {
            margin-top: 0;
            color: #fff;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        input[type="text"],
        input[type="password"],
        input[type="number"] {
            width: 100%;
            padding: 12px;
            background-color: #121212;
            border: 1px solid var(--border);
            color: #fff;
            border-radius: 6px;
            outline: none;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        input:focus {
            border-color: var(--accent);
        }

        button {
            padding: 12px 15px;
            background-color: var(--accent);
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
        }

        button:hover {
            background-color: var(--accent-hover);
        }

        .btn-danger {
            background-color: var(--danger);
        }

        .btn-danger:hover {
            background-color: #d84351;
        }

        /* Portal Stilleri */
        #portal-area {
            display: none;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border);
        }

        /* Portal Layout: Sol Ürünler, Sağ Sepet */
        .portal-layout {
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }

        .products-section {
            flex: 1;
            position: relative;
        }

        .sticky-search {
            position: sticky;
            top: 0;
            z-index: 100;
            background-color: var(--card-bg);
            padding: 10px 0;
            margin-top: -10px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 15px;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .product-card {
            background-color: #1a1a24;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 15px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .product-card h4 {
            margin: 0 0 10px 0;
            color: #fff;
            font-size: 1rem;
        }

        .product-card .price {
            color: var(--accent);
            font-weight: bold;
            margin-bottom: 15px;
        }

        .sepet-container {
            width: 350px;
            background-color: #1a1a24;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 20px;
            position: sticky;
            top: 20px;
            max-height: calc(100vh - 40px);
            overflow-y: auto;
        }

        @media (max-width: 900px) {
            .portal-layout {
                flex-direction: column-reverse;
            }

            .sepet-container {
                width: 100%;
                position: relative;
                top: 0;
                margin-bottom: 25px;
                background-color: rgba(36, 36, 54, 0.6);
                /* Hafif transparan daha soft koyu arka plan */
                border: 1px solid rgba(27, 197, 189, 0.3);
                /* Çok ince konsept renk (turkuaz) çerçeve */
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
                border-radius: 12px;
                padding: 25px 20px;
            }

            /* Sepet başlığı mobilde daha çok parlasın */
            .sepet-container h3 {
                color: var(--accent);
                font-size: 1.3rem;
                border-bottom: 1px dashed rgba(27, 197, 189, 0.2);
                /* Çizgiyi de incelttik */
                padding-bottom: 10px;
                margin-top: 0;
            }
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 0.9rem;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        th {
            color: #fff;
        }

        /* Bildirim */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            background-color: var(--accent);
            color: white;
            border-radius: 6px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            display: none;
            z-index: 1000;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.7);
        }

        .modal-content {
            background-color: var(--card-bg);
            margin: 10% auto;
            padding: 20px;
            border: 1px solid var(--border);
            border-radius: 8px;
            width: 80%;
            max-width: 800px;
        }

        .close-btn {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close-btn:hover,
        .close-btn:focus {
            color: #fff;
            text-decoration: none;
        }

        /* Özel Onay Modalı */
        #customConfirmModal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0; top: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.75);
            align-items: center;
            justify-content: center;
        }
        #customConfirmModal.active { display: flex; }
        #customConfirmBox {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 30px 28px 24px;
            max-width: 420px;
            width: 90%;
            box-shadow: 0 8px 32px rgba(0,0,0,0.6);
        }
        #customConfirmBox h4 {
            margin: 0 0 12px 0;
            font-size: 1.1rem;
            color: #fff;
        }
        #customConfirmBox p {
            margin: 0 0 22px 0;
            color: var(--text-main);
            font-size: 0.95rem;
            line-height: 1.5;
        }
        #customConfirmBox .modal-btns {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        #customConfirmBox .modal-btns button {
            padding: 9px 22px;
            font-size: 0.95rem;
            border-radius: 8px;
            border: none;
            cursor: pointer;
        }
        #customConfirmBox .btn-cancel-modal {
            background: transparent;
            border: 1px solid var(--border) !important;
            color: var(--text-main);
        }
        #customConfirmBox .btn-ok-modal {
            background: var(--danger);
            color: #fff;
        }

        /* Sipariş iç tablosu */
        .order-details-table {
            width: 100%;
            font-size: 0.85rem;
            margin-top: 10px;
            margin-bottom: 20px;
            background: #14141e;
            border-radius: 4px;
            border-collapse: collapse;
        }

        .order-details-table th,
        .order-details-table td {
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid #2a2a35;
        }

        .order-details-table th {
            background: #1a1a24;
        }

        /* Responsive Mobile CSS */
        @media screen and (max-width: 768px) {
            .portal-layout {
                flex-direction: column;
            }

            .products-section {
                padding-right: 0;
            }

            .order-details-table thead {
                display: none;
            }

            .order-details-table,
            .order-details-table tbody,
            .order-details-table tr,
            .order-details-table td {
                display: block;
                width: 100%;
            }

            .order-details-table tr {
                margin-bottom: 15px;
                border: 1px solid var(--border);
                border-radius: 4px;
                padding: 10px;
                background: #1e1e2d;
            }

            .order-details-table td {
                text-align: right;
                padding-left: 50%;
                position: relative;
                border-bottom: none;
            }

            .order-details-table td::before {
                content: attr(data-label);
                position: absolute;
                left: 10px;
                width: 45%;
                text-align: left;
                font-weight: bold;
                color: #a0a0a0;
            }
        }

        /* Responsive Improvements */
        @media (max-width: 768px) {
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 10px;
            }

            .product-card {
                padding: 10px;
            }

            .product-card h4 {
                font-size: 0.9rem;
            }

            button {
                padding: 10px;
                font-size: 0.9rem;
            }

            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
                /* Diğer tablolar sağa kaysın */
            }

            /* Sepet tablosu 3 sütunlu olduğu için ekrana sığar, white-space kapatılır. */
            #sepet-table {
                white-space: normal;
                width: 100%;
            }

            .header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }

            .header>div:last-child {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                width: 100%;
            }

            .header>div:last-child button {
                flex: 1 1 calc(33% - 10px);
                margin: 0 !important;
                font-size: 0.8rem;
                padding: 10px 5px;
            }

            .modal-content {
                width: 95%;
                margin: 5% auto;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <!-- LOGIN EKRANI -->
        <div class="card" id="login-area" style="max-width: 400px; margin: 0 auto;">
            <h2>Sipariş Portalı Giriş</h2>
            <form id="loginForm" onsubmit="doLogin(event)">
                <div class="form-group">
                    <label>Kullanıcı Adı</label>
                    <input type="text" id="l_username" required>
                </div>
                <div class="form-group">
                    <label>Şifre</label>
                    <input type="password" id="l_password" required>
                </div>
                <button type="submit" style="width: 100%;">Giriş Yap</button>
            </form>
        </div>

        <!-- PORTAL EKRANI -->
        <div id="portal-area">
            <div class="card">
                <div class="header">
                    <div>
                        <h2>AKSA B2B Sipariş Portalı</h2>
                        <span id="welcome-text"></span>
                        <div id="usd-rate-portal"
                            style="margin-top:5px; color:var(--success); font-weight:bold; font-size: 0.9rem;">USD Kuru:
                            Yükleniyor...</div>
                    </div>
                    <div>
                        <button class="btn" id="btn-catalog"
                            style="width: auto; background-color: var(--accent); color: #fff; border:none; margin-right:10px;"
                            onclick="switchTab('catalog')">Ürünler</button>
                        <button class="btn" id="btn-orders"
                            style="width: auto; background-color: transparent; color: #fff; border: 1px solid var(--border); margin-right:10px;"
                            onclick="switchTab('orders')">Siparişlerim</button>
                        <button class="btn-danger" style="width: auto;" onclick="logout()">Çıkış Yap</button>
                    </div>
                </div>

                <div id="catalog-view" class="portal-layout">
                    <!-- SOL: Ürünler Listesi -->
                    <div class="products-section">
                        <div class="sticky-search">
                            <h3 style="margin-top:0;">Ürünler</h3>
                            <div class="form-group" style="margin-bottom:0; display:flex; gap:10px; align-items:center;">
                                <select id="portalUrunLimit" onchange="changeUrunLimit()" style="padding: 8px; border-radius: 4px; border: 1px solid var(--border); background: #1a1a24; color: var(--text-main); height: 40px;">
                                    <option value="10">10 Kayıt</option>
                                    <option value="20">20 Kayıt</option>
                                    <option value="50" selected>50 Kayıt</option>
                                    <option value="100">100 Kayıt</option>
                                    <option value="5000">Tümü</option>
                                </select>
                                <input type="text" id="search" placeholder="Ürün Ara..." oninput="filterProducts()" style="flex:1;">
                            </div>
                        </div>
                        <div class="product-grid" id="productGrid">
                            <!-- Ürünler buraya yüklenecek -->
                        </div>
                    </div>

                    <!-- SAĞ: Sabit Sepet -->
                    <div class="sepet-container" id="sepet-container" style="display:none;">
                        <h3 style="margin-top: 0; padding-bottom: 10px; border-bottom: 1px solid var(--border);">Sepetim
                        </h3>
                        <table id="sepet-table">
                            <thead>
                                <tr>
                                    <th>Ürün</th>
                                    <th>KG</th>
                                    <th>İşlem</th>
                                </tr>
                            </thead>
                            <tbody id="sepet-body"></tbody>
                        </table>
                        <button style="margin-top: 20px; width: 100%;" onclick="siparisVer()">Siparişi Onayla</button>
                    </div>
                </div>
            </div>

            <div id="orders-view" style="display:none; padding-top:20px;">
                <h2 style="margin-top:0;">Sipariş Geçmişim</h2>
                <div id="siparislerimBody">Yükleniyor...</div>
            </div>

        </div>
    </div>

    <div id="notification" class="notification">İşlem başarılı!</div>

    <!-- Özel Onay Modalı -->
    <div id="customConfirmModal">
        <div id="customConfirmBox">
            <h4 id="customConfirmTitle">Onay</h4>
            <p id="customConfirmMsg"></p>
            <div class="modal-btns">
                <button class="btn-cancel-modal" id="customConfirmCancel">İptal</button>
                <button class="btn-ok-modal" id="customConfirmOk">Tamam</button>
            </div>
        </div>
    </div>

    <script>
        let isLogged = <?php echo isset($_SESSION['musteri_id']) ? 'true' : 'false'; ?>;
        let musteriFirma = "<?php echo isset($_SESSION['firma_adi']) ? addslashes($_SESSION['firma_adi']) : ''; ?>";
        let allProducts = [];
        let sepet = [];
        let myOrders = []; // Müşterinin kendi siparişleri
        let updateTimeout = null;

        // ---- ÖZEL MODAL YARDIMCI FONKSİYONLARI ----
        function showConfirm(message, title = 'Onay') {
            return new Promise((resolve) => {
                const modal = document.getElementById('customConfirmModal');
                document.getElementById('customConfirmTitle').textContent = title;
                document.getElementById('customConfirmMsg').textContent = message;
                modal.classList.add('active');

                const okBtn = document.getElementById('customConfirmOk');
                const cancelBtn = document.getElementById('customConfirmCancel');

                function cleanup() {
                    modal.classList.remove('active');
                    okBtn.removeEventListener('click', onOk);
                    cancelBtn.removeEventListener('click', onCancel);
                }
                function onOk() { cleanup(); resolve(true); }
                function onCancel() { cleanup(); resolve(false); }

                okBtn.addEventListener('click', onOk);
                cancelBtn.addEventListener('click', onCancel);
            });
        }

        function showAlert(message) {
            return new Promise((resolve) => {
                const modal = document.getElementById('customConfirmModal');
                document.getElementById('customConfirmTitle').textContent = 'Bilgi';
                document.getElementById('customConfirmMsg').textContent = message;
                document.getElementById('customConfirmCancel').style.display = 'none';
                document.getElementById('customConfirmOk').textContent = 'Tamam';
                document.getElementById('customConfirmOk').style.background = 'var(--accent)';
                modal.classList.add('active');

                const okBtn = document.getElementById('customConfirmOk');
                function onOk() {
                    modal.classList.remove('active');
                    okBtn.removeEventListener('click', onOk);
                    document.getElementById('customConfirmCancel').style.display = '';
                    okBtn.textContent = 'Tamam';
                    okBtn.style.background = '';
                    resolve();
                }
                okBtn.addEventListener('click', onOk);
            });
        }
        // ---- ÖZEL MODAL YARDIMCI FONKSİYONLARI SONU ----

        document.addEventListener('DOMContentLoaded', () => {
            fetchUsdRatePortal();
            if (isLogged) {
                showPortal();
            } else {
                showLogin();
            }
        });

        async function fetchUsdRatePortal() {
            try {
                const response = await fetch('api/kur_api.php');
                const data = await response.json();
                if (data.status === 'success') {
                    document.getElementById('usd-rate-portal').innerText = `Güncel Kur: 1 USD = ${parseFloat(data.usd_kuru).toFixed(2)} ₺`;
                }
            } catch (err) {
                console.error('Kur alınamadı:', err);
                document.getElementById('usd-rate-portal').innerText = '';
            }
        }

        function switchTab(tab) {
            if (tab === 'catalog') {
                document.getElementById('catalog-view').style.display = 'flex';
                document.getElementById('orders-view').style.display = 'none';
                document.getElementById('btn-catalog').style.backgroundColor = 'var(--accent)';
                document.getElementById('btn-catalog').style.border = 'none';
                document.getElementById('btn-orders').style.backgroundColor = 'transparent';
                document.getElementById('btn-orders').style.border = '1px solid var(--border)';
            } else {
                document.getElementById('catalog-view').style.display = 'none';
                document.getElementById('orders-view').style.display = 'block';
                document.getElementById('btn-catalog').style.backgroundColor = 'transparent';
                document.getElementById('btn-catalog').style.border = '1px solid var(--border)';
                document.getElementById('btn-orders').style.backgroundColor = 'var(--accent)';
                document.getElementById('btn-orders').style.border = 'none';
                fetchSiparislerim();
            }
        }

        function showLogin() {
            document.getElementById('login-area').style.display = 'block';
            document.getElementById('portal-area').style.display = 'none';
        }

        function showPortal() {
            document.getElementById('login-area').style.display = 'none';
            document.getElementById('portal-area').style.display = 'block';
            document.getElementById('welcome-text').innerText = 'Hoş Geldiniz, ' + musteriFirma;
            loadProducts();
            fetchSiparislerim(); // Müşterinin mevcut siparişlerini arkaplanda yükle
        }

        async function doLogin(e) {
            e.preventDefault();
            const u = document.getElementById('l_username').value;
            const p = document.getElementById('l_password').value;

            try {
                const res = await fetch('api/login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ kullanici_adi: u, sifre: p })
                });

                const data = await res.json();
                if (data.status === 'success') {
                    isLogged = true;
                    musteriFirma = data.user.firma_adi;
                    showPortal();
                } else {
                    alert('Hata: ' + data.message);
                }
            } catch (err) {
                alert('Sistemsel Hata!');
                console.error(err);
            }
        }

        function logout() {
            fetch('api/logout.php').then(() => {
                isLogged = false;
                sepet = [];
                updateSepetUI();
                showLogin();
            });
        }

        let urunPage = 1;
        let urunLimit = 50; // varsayılan sayfa başı kayıt sayısı

        function changeUrunLimit() {
            const limitSelect = document.getElementById('portalUrunLimit');
            if(limitSelect) {
                urunLimit = parseInt(limitSelect.value);
            }
            urunPage = 1; // Limit değiştiğinde ilk sayfaya dön
            loadProducts();
        }

        async function loadProducts() {
            try {
                const searchInput = document.getElementById('search');
                const query = searchInput ? searchInput.value : '';

                const res = await fetch(`api/urun_api.php?page=${urunPage}&limit=${urunLimit}&search=${encodeURIComponent(query)}`);
                const result = await res.json();

                if (result.status === 'success') {
                    allProducts = result.data || [];
                    renderProducts(allProducts);
                    renderProductPagination(result.total_pages);
                } else {
                    renderProducts(allProducts); // legacy fallback ok
                }
            } catch (err) {
                console.error("Ürünler yüklenirken hata", err);
            }
        }

        let searchTimeout = null;
        function filterProducts() {
            if (searchTimeout) clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                urunPage = 1;
                loadProducts();
            }, 300); // 300ms debounce
        }

        function updateUrunPage(p) {
            urunPage = p;
            loadProducts();
        }

        function renderProductPagination(totalPages) {
            let container = document.getElementById('portalPaginationContainer');
            // Container yoksa Grid altina ekle
            if (!container) {
                const grid = document.getElementById('productGrid');
                container = document.createElement('div');
                container.id = 'portalPaginationContainer';
                container.style.marginTop = '20px';
                container.style.display = 'flex';
                container.style.justifyContent = 'center';
                container.style.gap = '8px';
                container.style.flexWrap = 'wrap';
                grid.parentNode.insertBefore(container, grid.nextSibling);
            }

            if (totalPages <= 1) {
                container.innerHTML = '';
                return;
            }

            let html = '';
            
            // Ortak buton stili
            const baseStyle = "padding: 5px 12px; border-radius: 6px; font-size: 0.9rem; cursor: pointer; transition: all 0.2s;";
            
            // Geri butonu
            const prevDisabled = urunPage === 1;
            html += `<button style="${baseStyle} border: 1px solid var(--border); background: transparent; color: var(--text-main); ${prevDisabled ? 'opacity:0.5; cursor:default;' : ''}" 
                        onclick="${!prevDisabled ? `updateUrunPage(${urunPage - 1})` : ''}" ${prevDisabled ? 'disabled' : ''}>◀ Önceki</button>`;

            // Sayfa numaraları
            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= urunPage - 2 && i <= urunPage + 2)) {
                    const isActive = (i === urunPage);
                    html += `<button style="${baseStyle} background: ${isActive ? 'var(--accent)' : 'transparent'}; border: 1px solid ${isActive ? 'var(--accent)' : 'var(--border)'}; color: ${isActive ? '#fff' : 'var(--text-main)'}; font-weight: ${isActive ? 'bold' : 'normal'};" onclick="updateUrunPage(${i})">${i}</button>`;
                } else if (i === urunPage - 3 || i === urunPage + 3) {
                    html += `<span style="color:var(--text-main); padding:0 5px; align-self: center;">...</span>`;
                }
            }

            // İleri butonu
            const nextDisabled = urunPage === totalPages;
            html += `<button style="${baseStyle} border: 1px solid var(--border); background: transparent; color: var(--text-main); ${nextDisabled ? 'opacity:0.5; cursor:default;' : ''}" 
                        onclick="${!nextDisabled ? `updateUrunPage(${urunPage + 1})` : ''}" ${nextDisabled ? 'disabled' : ''}>Sonraki ▶</button>`;

            container.innerHTML = html;
        }

        function renderProducts(products) {
            const grid = document.getElementById('productGrid');

            if (products.length === 0) {
                grid.innerHTML = '<p>Ürün bulunamadı.</p>';
                return;
            }

            let html = '';
            products.forEach(p => {
                html += `
                    <div class="product-card">
                        <div>
                            <h4 style="font-size: 1.25rem; color: var(--accent); margin-bottom: 5px;">${p.urun_kodu}</h4>
                            <div style="font-size: 0.85rem; color: #a0a0a0; margin-bottom: 15px;">${p.urun_adi}</div>
                        </div>
                        <div>
                            <input type="number" id="qty_${p.urun_kodu}" placeholder="Miktar (KG)" min="1" style="margin-bottom:10px;">
                            <button onclick="sepeteEkle('${p.urun_kodu}', '${p.urun_adi.replace(/'/g, "\\'")}')">Sepete Ekle</button>
                        </div>
                    </div>
                `;
            });
            grid.innerHTML = html;
        }

        function sepeteEkle(kodu, adi) {
            const qtyInput = document.getElementById('qty_' + kodu);
            const miktar = parseFloat(qtyInput.value);

            if (isNaN(miktar) || miktar <= 0) {
                alert('Geçerli bir miktar girin.');
                return;
            }

            const existing = sepet.find(item => item.urun_kodu === kodu);
            if (existing) {
                existing.miktar_kg += miktar;
            } else {
                sepet.push({ urun_kodu: kodu, urun_adi: adi, miktar_kg: miktar });
            }

            qtyInput.value = '';
            updateSepetUI();
            showNotification('Ürün sepete eklendi');
        }

        function removeSepet(kodu) {
            sepet = sepet.filter(item => item.urun_kodu !== kodu);
            updateSepetUI();
        }

        function updateSepetUI() {
            const container = document.getElementById('sepet-container');
            const tbody = document.getElementById('sepet-body');
            tbody.innerHTML = '';

            if (sepet.length === 0) {
                container.style.display = 'none';
                return;
            }

            container.style.display = 'block';
            sepet.forEach(item => {
                tbody.innerHTML += `
                    <tr>
                        <td>${item.urun_kodu}<br><small>${item.urun_adi}</small></td>
                        <td>${item.miktar_kg}</td>
                        <td><button class="btn-danger" style="padding: 4px 8px; font-size: 0.8rem;" onclick="removeSepet('${item.urun_kodu}')">Sil</button></td>
                    </tr>
                `;
            });
        }

        async function siparisVer() {
            if (sepet.length === 0) return;

            // Beklemede olan siparişi kontrol et
            let beklemede = myOrders.find(s => s.durum === 'beklemede');
            if (beklemede) {
                let addToPending = await showConfirm("Halihazırda 'Beklemede' olan bir siparişiniz (Sipariş #" + beklemede.id + ") bulunuyor.\n\nYeni ürünler bu mevcut siparişinize eklensin mi?\n\nTamam = Mevcut Siparişe Ekle\nİptal = Yeni Ayrı Sipariş Oluştur", 'Mevcut Sipariş Bulundu');
                if (addToPending) {
                    try {
                        // Güncel sipariş detaylarını yeniden çek (stale data'yı önle)
                        const freshRes = await fetch('api/musteri_siparisleri.php');
                        const freshData = await freshRes.json();
                        let freshBeklemede = null;
                        if (Array.isArray(freshData)) {
                            freshBeklemede = freshData.find(s => s.id == beklemede.id && s.durum === 'beklemede');
                        }
                        if (!freshBeklemede) {
                            await showAlert('Beklemede olan sipariş artık mevcut değil. Yeni sipariş oluşturuluyor...');
                            // Fall through to create new order
                        } else {
                            let sepetPayload = [];
                            let detaylar = freshBeklemede.detaylar || [];
                            // Mevcut ürünleri ve sepeti birleştir
                            detaylar.forEach(d => {
                                let exists = sepet.find(item => item.urun_kodu === d.urun_kodu);
                                if (exists) {
                                    sepetPayload.push({ urun_kodu: d.urun_kodu, urun_adi: d.urun_adi, miktar_kg: parseFloat(d.istenen_kg) + parseFloat(exists.miktar_kg) });
                                } else {
                                    sepetPayload.push({ urun_kodu: d.urun_kodu, urun_adi: d.urun_adi, miktar_kg: parseFloat(d.istenen_kg) });
                                }
                            });
                            // Sepette olup eski siparişte olmayanları ekle
                            sepet.forEach(item => {
                                let exists = detaylar.find(d => d.urun_kodu === item.urun_kodu);
                                if (!exists) {
                                    sepetPayload.push({ urun_kodu: item.urun_kodu, urun_adi: item.urun_adi, miktar_kg: parseFloat(item.miktar_kg) });
                                }
                            });

                            const res = await fetch('api/musteri_siparis_guncelle.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ siparis_id: freshBeklemede.id, sepet: sepetPayload })
                            });
                            const data = await res.json();
                            if (data.status === 'success') {
                                await showAlert('Ürünler mevcut siparişinize başarıyla eklendi!');
                                sepet = [];
                                updateSepetUI();
                                fetchSiparislerim();
                                return;
                            } else {
                                await showAlert('Hata: ' + data.message);
                                return;
                            }
                        }
                    } catch (err) {
                        console.error('Sipariş güncelleme hatası:', err);
                        await showAlert('Sipariş güncellenirken hata oluştu: ' + err.message);
                        return;
                    }
                }
            }

            try {
                const res = await fetch('api/siparis_olustur.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ sepet: sepet })
                });

                const data = await res.json();
                if (data.status === 'success') {
                    await showAlert('Siparişiniz başarıyla alındı!');
                    sepet = [];
                    updateSepetUI();
                    fetchSiparislerim();
                } else {
                    await showAlert('Hata: ' + data.message);
                }
            } catch (err) {
                await showAlert('Sipariş işlemi sırasında hata oluştu.');
            }
        }

        function showNotification(msg) {
            const noti = document.getElementById('notification');
            noti.innerText = msg;
            noti.style.display = 'block';
            setTimeout(() => noti.style.display = 'none', 3000);
        }

        // --- SİPARİŞ GEÇMİŞİ FONKSİYONLARI ---

        async function fetchSiparislerim() {
            const body = document.getElementById('siparislerimBody');
            body.innerHTML = '<div style="text-align:center; padding: 20px;">Yükleniyor...</div>';

            try {
                const res = await fetch('api/musteri_siparisleri.php');
                const data = await res.json();

                if (data.status === 'error') {
                    body.innerHTML = `<p style="color:var(--danger)">${data.message}</p>`;
                    return;
                }

                if (data.length === 0) {
                    body.innerHTML = '<p>Henüz verilmiş bir siparişiniz bulunmamaktadır.</p>';
                    return;
                }

                myOrders = data; // Inline update için bellekte tutuyoruz

                let html = '';
                myOrders.forEach(sip => {
                    const durumMap = {
                        'beklemede': { label: '⏳ Beklemede', color: '#ffc107' },
                        'tedarik': { label: '📦 Tedarikte', color: '#a855f7' },
                        'geldi': { label: '🚚 Teslim Bekleniyor', color: '#fd7e14' },
                        'tamamlandi': { label: '✅ Teslim Edildi', color: '#1bc5bd' },
                        'iptal': { label: '❌ İptal', color: '#f64e60' }
                    };
                    const durumInfo = durumMap[sip.durum] || { label: sip.durum.toUpperCase(), color: '#aaa' };
                    let isBeklemede = sip.durum === 'beklemede';

                    let toplamKg = sip.toplam_istened || 0;

                    let orderHeaderButtons = isBeklemede ?
                        `<button class="btn-danger" style="padding: 4px 10px; font-size: 12px;" onclick="cancelFullOrder(${sip.id})">Komple İptal Et</button>`
                        : '';

                    html += `
                        <div style="border: 1px solid var(--border); padding: 15px; margin-bottom: 20px; border-radius: 8px; background: #1a1a24; position: relative;" id="siparis_card_${sip.id}">
                            <div style="display:flex; justify-content:space-between; margin-bottom:10px; flex-wrap: wrap; gap: 10px;">
                                <strong>Sipariş No: #${sip.id}</strong>
                                <span>Tarih: ${sip.tarih}</span>
                                <span>Toplam: ${sip.toplam_istenen} KG</span>
                                <span><strong style="color: ${durumInfo.color};">${durumInfo.label}</strong></span>
                                ${orderHeaderButtons}
                            </div>
                            <table class="order-details-table">
                                <thead>
                                    <tr>
                                        <th>Kodu</th>
                                        <th>Ürün Adı</th>
                                        <th>Miktar</th>
                                        <th>Durum</th>
                                        ${isBeklemede ? '<th>İşlem</th>' : ''}
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                    sip.detaylar.forEach(det => {
                        const detDurumMap = {
                            'beklemede': { label: '⏳ Beklemede', color: '#ffc107', bg: 'rgba(255,193,7,0.12)' },
                            'tedarik': { label: '📦 Tedarikte', color: '#a855f7', bg: 'rgba(168,85,247,0.12)' },
                            'geldi': { label: '🚚 Teslim Bekleniyor', color: '#fd7e14', bg: 'rgba(253,126,20,0.12)' },
                            'tamamlandi': { label: '✅ Teslim Edildi', color: '#1bc5bd', bg: 'rgba(27,197,189,0.12)' },
                            'iptal': { label: '❌ İptal', color: '#f64e60', bg: 'rgba(246,78,96,0.12)' }
                        };
                        const detDurum = detDurumMap[det.durum] || { label: det.durum, color: '#aaa', bg: 'rgba(255,255,255,0.05)' };

                        let miktarControl = isBeklemede ?
                            `<input type="number" id="input_${sip.id}_${det.urun_kodu}" value="${det.istened_kg || det.istenen_kg}" min="1" step="0.5" style="width: 80px; padding: 4px;" oninput="onKgChanged(${sip.id}, '${det.urun_kodu}')"> KG`
                            : `${det.istened_kg || det.istenen_kg} KG`;

                        let durumBadge = `<span style="display:inline-block; padding:3px 10px; border-radius:12px; font-size:0.78rem; font-weight:600; background:${detDurum.bg}; color:${detDurum.color};">${detDurum.label}</span>`;

                        let islemControl = isBeklemede && det.durum === 'beklemede' ?
                            `<td data-label="İşlem"><button class="btn-danger" style="padding: 4px 8px; font-size: 0.8rem;" onclick="inlineDeleteUrun(${sip.id}, '${det.urun_kodu}')">Sil</button></td>`
                            : (isBeklemede ? '<td></td>' : '');

                        html += `
                            <tr id="row_${sip.id}_${det.urun_kodu}">
                                <td data-label="Kodu">${det.urun_kodu}</td>
                                <td data-label="Ürün Adı">${det.urun_adi}</td>
                                <td data-label="Miktar">${miktarControl}</td>
                                <td data-label="Durum">${durumBadge}</td>
                                ${islemControl}
                            </tr>
                        `;
                    });

                    let saveBlock = isBeklemede ?
                        `<div style="text-align: right; margin-top: 10px; display: none;" id="save_block_${sip.id}">
                            <button class="btn" style="background-color:var(--success); color:#fff; padding:8px 15px;" onclick="saveBulkEdits(${sip.id})">Değişiklikleri Kaydet</button>
                        </div>`
                        : '';

                    html += `
                                </tbody>
                            </table>
                            ${saveBlock}
                        </div>
                    `;
                });

                body.innerHTML = html;
            } catch (err) {
                console.error(err);
                body.innerHTML = '<p style="color:var(--danger)">Siparişler çekilemedi.</p>';
            }
        }

        async function runInlineUpdate(siparisId, urunKodu, yeniMiktar, isDelete = false) {
            let siparis = myOrders.find(s => s.id == siparisId);
            if (!siparis) return;

            let sepetPayload = [];
            siparis.detaylar.forEach(d => {
                if (d.urun_kodu === urunKodu) {
                    if (!isDelete) {
                        sepetPayload.push({ urun_kodu: d.urun_kodu, urun_adi: d.urun_adi, miktar_kg: yeniMiktar });
                    }
                } else {
                    sepetPayload.push({ urun_kodu: d.urun_kodu, urun_adi: d.urun_adi, miktar_kg: parseFloat(d.istenen_kg) });
                }
            });

            if (sepetPayload.length === 0) {
                // Son ürünü silmek = siparişi iptal etmek
                if (await showConfirm('Son ürünü silmek siparişi iptal edecektir. Devam etmek istiyor musunuz?', 'Dikkat')) {
                    await cancelFullOrder(siparisId);
                } else {
                    fetchSiparislerim();
                }
                return;
            }

            try {
                const res = await fetch('api/musteri_siparis_guncelle.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ siparis_id: siparisId, sepet: sepetPayload })
                });

                const data = await res.json();
                if (data.status === 'success') {
                    showNotification('Sipariş güncellendi!');
                    fetchSiparislerim(); // Verileri yeniden indir ve çiz
                } else {
                    await showAlert('Hata: ' + data.message);
                    fetchSiparislerim();
                }
            } catch (err) {
                await showAlert('Sipariş güncellenirken hata oluştu.');
                fetchSiparislerim();
            }
        }

        function onKgChanged(siparisId, urunKodu, birimFiyat) {
            let input = document.getElementById(`input_${siparisId}_${urunKodu}`);
            let yeniMiktar = parseFloat(input.value);

            if (isNaN(yeniMiktar) || yeniMiktar <= 0) return;

            // Kaydet Butonunu Göster
            document.getElementById(`save_block_${siparisId}`).style.display = 'block';
        }

        function recalcTotal(siparisId) {
            // Müşteriden fiyatlar gizlendiği için bu fonksiyona gerek kalmadı.
        }

        async function saveBulkEdits(siparisId) {
            let siparis = myOrders.find(s => s.id == siparisId);
            if (!siparis) return;

            let sepetPayload = [];
            siparis.detaylar.forEach(det => {
                let qtyEl = document.getElementById(`input_${siparisId}_${det.urun_kodu}`);
                if (qtyEl) {
                    let qty = parseFloat(qtyEl.value);
                    if (!isNaN(qty) && qty > 0) {
                        sepetPayload.push({ urun_kodu: det.urun_kodu, urun_adi: det.urun_adi, miktar_kg: qty });
                    }
                }
            });

            if (sepetPayload.length === 0) {
                await showAlert('Tüm ürünleri 0 yaptınız, onaylamak istiyorsanız lütfen komple iptal edin.');
                return;
            }

            try {
                const res = await fetch('api/musteri_siparis_guncelle.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ siparis_id: siparisId, sepet: sepetPayload })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    showNotification('Tüm ürün değişiklikleri başarıyla kaydedildi!');
                    fetchSiparislerim();
                } else {
                    await showAlert('Hata: ' + data.message);
                }
            } catch (err) {
                await showAlert('Kaydederken hata oluştu.');
            }
        }

        async function cancelFullOrder(siparisId) {
            if (!await showConfirm('Bu siparişi tamamen iptal etmek istediğinize emin misiniz?\nİptal edilen siparişler geri alınamaz.', 'Siparişi İptal Et')) return;

            try {
                const res = await fetch('api/musteri_siparis_iptal.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ siparis_id: siparisId })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    showNotification('Sipariş iptal edildi.');
                    fetchSiparislerim();
                } else {
                    await showAlert('Hata: ' + data.message);
                }
            } catch (err) {
                console.error('İptal hatası:', err);
                await showAlert('İptal işlemi başarısız: ' + err.message);
            }
        }

        function inlineUpdateKg(siparisId, urunKodu, value) {
            if (isNaN(value) || value <= 0) {
                alert('Geçerli bir miktar girin.');
                fetchSiparislerim();
                return;
            }
            if (updateTimeout) clearTimeout(updateTimeout);
            updateTimeout = setTimeout(() => {
                runInlineUpdate(siparisId, urunKodu, value, false);
            }, 500); // 500ms debounce
        }

        async function inlineDeleteUrun(siparisId, urunKodu) {
            if (await showConfirm('Bu ürünü siparişinizden çıkarmak istediğinizden emin misiniz?', 'Ürünü Sil')) {
                runInlineUpdate(siparisId, urunKodu, 0, true);
            }
        }
    </script>
</body>

</html>