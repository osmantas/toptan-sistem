
        let currentUsdRate = 0;

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
            } else if (page === 'raporlar') {
                pageTitle.textContent = "Raporlar & İstatistik";
                loadRaporlar();
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
                                <input type="number" step="0.01" id="m_iskonto" value="0.00" required placeholder="Örn: 10.5">
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
                                <input type="number" step="0.01" id="edit_m_iskonto" required>
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

                if (data.length === 0) {
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

                <div class="card" style="margin-top: 0;">
                    <table>
                        <thead>
                            <tr>
                                <th>Kodu</th>
                                <th>Adı</th>
                                <th>Fiyat (USD/KG)</th>
                            </tr>
                        </thead>
                        <tbody id="urunTableBody">
                            <tr><td colspan="3" style="text-align:center;">Yükleniyor...</td></tr>
                        </tbody>
                    </table>
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
                                <input type="number" step="0.01" id="u_usd_fiyat" required placeholder="0.00">
                            </div>
                            <button type="submit" style="width: 100%; margin-top: 15px; padding: 12px;">Ürünü Kaydet</button>
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

        async function fetchUrunler() {
            try {
                const response = await fetch('../api/admin_urun_api.php');
                const data = await response.json();
                const tbody = document.getElementById('urunTableBody');
                tbody.innerHTML = '';

                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;">Kayıtlı ürün bulunamadı.</td></tr>';
                    return;
                }

                data.forEach(u => {
                    tbody.innerHTML += `
                        <tr>
                            <td><strong>${u.urun_kodu}</strong></td>
                            <td>${u.urun_adi}</td>
                            <td>$ ${u.usd_fiyat}</td>
                        </tr>
                    `;
                });
            } catch (err) {
                console.error('Ürünler çekilirken hata oluştu:', err);
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
                if (sel) data.forEach(m => {
                    sel.innerHTML += `<option value="${m.id}">${m.firma_adi}</option>`;
                });
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
                            <th style="padding:10px 14px; text-align:center;">Toplam KG</th>
                            <th style="padding:10px 14px; text-align:center;">Müşteri</th>
                            <th style="padding:10px 14px; text-align:center;">Miktar (Geldi)</th>
                            <th style="padding:10px 14px; text-align:center;">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>`;

            data.forEach((u, idx) => {
                const detaylar = tedarikDetayData[u.urun_kodu] || [];
                const toplamKg = parseFloat(u.toplam_bekleyen);
                const musteri = u.musteri_sayisi;

                // Her ürün için detay satırlarının toplam istenen_kg'si (sipariş fişi için)
                html += `
                    <tr class="tedarik-urun-row" style="border-top:1px solid var(--border-color); cursor:default;" id="td-urun-row-${idx}">
                        <td style="padding:10px 14px; vertical-align:top;">
                            <input type="checkbox" class="td-urun-check" data-urun="${u.urun_kodu}"
                                data-urun-adi="${u.urun_adi.replace(/"/g, '&quot;')}"
                                data-toplam="${toplamKg.toFixed(1)}"
                                onchange="updateTedarikUrunSecim()">
                        </td>
                        <td style="padding:10px 14px; vertical-align:top;">
                            <span style="font-family:monospace; font-weight:700; font-size:0.95rem; color:var(--accent);">${u.urun_kodu}</span>
                        </td>
                        <td style="padding:10px 14px; vertical-align:top;">
                            <span style="font-weight:600; color:var(--text-light);">${u.urun_adi}</span>
                            ${detaylar.length > 0 ? `<div style="margin-top:6px;">${detaylar.map(d => `<span style="font-size:0.75rem; color:var(--text-main); display:block;">└ ${d.firma_adi}: ${parseFloat(d.kalan_kg).toFixed(1)} KG</span>`).join('')}</div>` : ''}
                        </td>
                        <td style="padding:10px 14px; text-align:center; vertical-align:top;">
                            <span class="stat-badge kg">${toplamKg.toFixed(1)} KG</span>
                        </td>
                        <td style="padding:10px 14px; text-align:center; vertical-align:top;">
                            <span class="stat-badge customers">${musteri}</span>
                        </td>
                        <td style="padding:10px 14px; text-align:center; vertical-align:top;">
                            <div style="display:flex; flex-direction:column; align-items:center; gap:4px;">
                                <div style="display:flex; align-items:center; gap:5px;">
                                    <input type="number" step="0.1" min="0.1" max="${toplamKg.toFixed(1)}"
                                        value="${toplamKg.toFixed(1)}"
                                        id="td-urun-miktar-${u.urun_kodu.replace(/[^a-zA-Z0-9]/g, '_')}"
                                        style="width:85px; padding:4px 8px; background:rgba(0,0,0,0.3); border:1px solid var(--border-color); color:var(--text-light); border-radius:5px; font-size:0.88rem; text-align:center;"
                                        title="Gelen miktar (max: ${toplamKg.toFixed(1)} KG)">
                                    <span style="font-size:0.78rem; color:var(--text-main);">KG</span>
                                </div>
                                <span style="font-size:0.7rem; color:rgba(255,184,34,0.7);">Toplam: ${toplamKg.toFixed(1)} KG</span>
                            </div>
                        </td>
                        <td style="padding:10px 14px; text-align:center; vertical-align:top;">
                            <div style="display:flex; flex-direction:column; gap:4px; align-items:center;">
                                <button class="btn-sm btn-geldi" style="font-size:0.75rem; white-space:nowrap;" onclick="tedarikUrunGeldi('${u.urun_kodu}')" title="Girilen miktarı Geldi olarak işaretle">✅ Geldi</button>
                                <button class="btn-sm btn-iptal" style="font-size:0.75rem; white-space:nowrap;" onclick="tedarikUrunGeriAl('${u.urun_kodu}')" title="Müşteri Siparişlerine geri al">↩ Geri Al</button>
                            </div>
                        </td>
                    </tr>`;
            });

            html += `</tbody></table></div>`;
            container.innerHTML = html;
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
                    fetchTedarikListesi();
                } else {
                    alert('Hata: ' + (r.message || 'Bilinmeyen hata'));
                }
            } catch (e) {
                console.error(e);
                alert('Sistemsel hata oluştu.');
            }
        }

        // Tek ürünün tüm detaylarını "Beklemede" yap
        async function tedarikUrunGeriAl(urunKodu) {
            const detaylar = tedarikDetayData[urunKodu] || [];
            if (!detaylar.length) return;
            const ids = detaylar.map(d => d.detay_id);
            try {
                const res = await fetch('../api/siparis_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'toplu_durum_guncelle', detay_ids: ids, yeni_durum: 'beklemede' })
                });
                const r = await res.json();
                if (r.status === 'success') { showNotification(`${urunKodu} — listeye geri alındı.`); fetchTedarikListesi(); }
                else alert('Hata: ' + r.message);
            } catch (e) { alert('Sistemsel hata oluştu.'); }
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
            </style><script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"><\/script>
    </head>

    <body>
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
            <thead>
                <tr>
                    <th style="width:40px;">#</th>
                    <th style="width:130px;">Ürün Kodu</th>
                    <th>Ürün Adı</th>
                    <th style="width:120px; text-align:right;">Miktar (KG)</th>
                </tr>
            </thead>
            <tbody>
                ${satirlar}
                <tr class="total-row">
                    <td colspan="3" style="text-align:right;">TOPLAM SİPARİŞ:</td>
                    <td style="text-align:right; color:#4ade80;">${toplamKg.toFixed(1)} KG</td>
                </tr>
            </tbody>
        </table>
        <div class="footer">
            <div class="sign-col">
                <div class="sign-line">Hazırlayan</div>
            </div>
            <div class="sign-col">
                <div class="sign-line">Onaylayan</div>
            </div>
            <div class="sign-col">
                <div class="sign-line">Tedarik Firması</div>
            </div>
        </div>
        <p style="font-size:10px; color:#aaa; text-align:center; margin-top:30px;">AKSA TOPTAN — Sistem tarafından
            otomatik oluşturulmuştur.</p>
    </body>

</html>`;

                        const blob = new Blob([printHTML], { type: 'text/html; charset=utf-8' });
            const blobUrl = URL.createObjectURL(blob);
            const w = window.open(blobUrl, '_blank');
            if (w) {
                w.onload = () => { setTimeout(() => { w.print(); URL.revokeObjectURL(blobUrl); }, 300); };
            } else {
                const a = document.createElement('a'); a.href = blobUrl; a.target = '_blank'; a.click();
            }
                }

        // ================            =====================
        // TAB 3: TESLİMAT
                        // =====================================async function refreshTeslimatTab() {
                        await fetchTeslimatBekleyen();            
            await fetchTe            slimEdilenler();
        }

        async function             f                            etchTeslimatBekleyen() {
            const mu                steri            Id = d                                ocument.getElementById('teslimatMusteriFilter')?.valu                                e || '';
                            let url =                                     '../api/siparis_api.php?action=teslimat_bekleyen&_t=' + Date.now();
                            if (musteriId) u                                                                    rl += '&musteri_id=' + mu                    steriId;
            try {
                const res = await fetch(url, { cache: 'no-store' });
                cons                    t data =                 a                wait res.json();
                const container = document.getElementById('teslimatBekleyenAccordion');
                if (!container) return;
                if (data.length === 0) {
                    container.innerHTML = '<div class="card" style="text-align:center; padding:30px;">Teslim bekleyen ürün yok.</div>';
                    return;
                }
                container.innerHTML = data.map((u, idx) => `
<div class="product-accordion" id="tsacc-${idx}">
    <div class="accordion-header" onclick="toggleTeslimatAccordion(${id            x}, '${u.urun_kodu}')">
        <s        pan         class="product-code">${u.urun_kodu}</span>
        <spa            n class="product-name">${u.urun_adi}</span>
                    <div class="product-stats">
            <span class="stat-b            adge geldi">${parseFloat            (u.toplam_bekleyen).toFixed(1)} KG                    </s        pan>
            <span class="stat-badge customers">${u.            musteri_sayi                si} müşteri</span>
        </            div>
                    <            span class="accordion-chevron">▼</span>
    </div>
    <            div class="ac            cordion-body" id="tsacc-body-${idx}">
        <div style="paddi            ng:20px; text-align:center; color:var(--                text-main);"            >Yükleniyor...</div>
    </div>
</div>
`)                                            .join('');
            } catc            h (e) { con                sole.error(e);             }
                        }

        async f
                    unction toggleTeslimatAccordion(idx, urunKodu) {
                        const el = document.getElementById('tsacc-' + idx);
            const body = document.getElementById('tsacc                -body-' + id            x);
            if (el.classList.contains('open'))             { el.                classList.remove('open'); return; }
            docum                ent.querySelectorAll('#teslimat                BekleyenAccordion .produ
                    ct-accordion.open').forEach(a =>
                a.classList.remove('open'));
            el.classList.add('open');
            const musteriId = document.getElementById('teslimatMusteriFilter')?.value || '';
            let url = '../api/siparis_api.php?action=teslimat_musteriler&urun_kodu=' + encodeURIComponent(urunKodu) + '&_t=' +
                Date.now();
            if (musteriId) url += '&musteri_id=' + musteriId;
            try {
                const res = await fetch(url, { cache: 'no-store' });
                const data = await res.json();
                if (data.length === 0) {
                    body.innerHTML = '<div style="padding:20px; text-align:center;">Detay bulunamadı.</div>';
                    return;
                }
                let html = `<table>
    <thead>
        <tr>
            <th>Firma Adı</th>
            <th>İstenen (KG)</th>
            <th>Teslim Edilen</th>
            <th>Kalan</th>
            <th>Tarih</th>
            <th>Teslim Miktarı</th>
            <th>İşlem</th>
        </tr>
    </thead>
    <tbody>`;
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
                    <div
                        style="flex:1; background:rgba(255,255,255,0.08); border-radius:10px; height:8px; min-width:60px; overflow:hidden;">
                        <div
                            style="width:${yuzde}%; height:100%; background:${yuzde >= 100 ? 'var(--success)' : 'var(--accent)'}; border-radius:10px; transition:width 0.3s;">
                        </div>
                    </div>
                    <span
                        style="font-size:0.8rem; color:${yuzde >= 100 ? 'var(--success)' : 'var(--accent)'}; font-weight:600; white-space:nowrap;">${teslimEdilen.toFixed(1)}
                        KG (${yuzde}%)</span>
                </div>
            </td>
            <td style="color:var(--danger);font-weight:600;">${kalan.toFixed(1)} KG</td>
            <td>${tarih}</td>
            <td>
                <input type="number" step="0.1" min="0.1" max="${kalan}" value="${kalan.toFixed(1)}"
                    id="teslim-miktar-${d.detay_id}"
                    style="width:90px; padding:5px 8px; background:rgba(0,0,0,0.3); border:1px solid var(--border-color); color:var(--text-light); border-radius:5px; font-size:0.85rem; text-align:center;"
                    onchange="if(parseFloat(this.value)>${kalan})this.value=${kalan.toFixed(1)}; if(parseFloat(this.value)<=0)this.value=0.1;">
                <span style="font-size:0.75rem; color:var(--text-main);">KG</span>
            </td>
            <td>
                <button class="btn-sm btn-teslim" onclick="teslimEtKismi(${d.detay_id}, ${kalan})"
                    style="white-space:nowrap;">🚚 Teslim Et</button>
                <button class="btn-sm" style="background:#6610f2; white-space:nowrap;"
                    onclick="document.getElementById('teslim-miktar-${d.detay_id}').value=${kalan.toFixed(1)}"
                    title="Tamamını teslim et">📦 Tamamı</button>
            </td>
        </tr>`;
                });
                html += '</tbody></table>';
                body.innerHTML = html;
            } catch (e) { body.innerHTML = '<div style="padding:20px; color:var(--danger);">Hata oluştu.</div>'; }
        }

        // Kısmi / tam teslim fonksiyonu
        async function teslimEtKismi(detayId, maxKalan) {
            const input = document.getElementById('teslim-miktar-' + detayId);
            if (!input) { alert('Miktar alanı bulunamadı.'); return; }
            const miktar = parseFloat(input.value);
            if (isNaN(miktar) || miktar <= 0) { alert('Lütfen geçerli bir miktar giriniz.'); return; } if (miktar > maxKalan + 0.01) { alert(`Teslim miktarı kalan miktarı (${maxKalan} KG) aşamaz.`); input.value = maxKalan.toFixed(1); return; }

            try {
                const res = await fetch('../api/siparis_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'teslim_et', detay_id: detayId, miktar: miktar })
                });
                const result = await res.json();
                if (result.status === 'success') {
                    showNotification(result.mesaj || 'Teslim işlemi başarılı!');
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
                    container.innerHTML = '<div class="card" style="text-align:center; padding:30px;">Teslim edilmiş sipariş bulunamadı.
    </div > ';
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
                <button class="btn-sm btn-teslim" style="margin-left:10px;"
                    onclick="event.stopPropagation(); fisKapatVeYazdir('${g.firma_adi.replace(/'/g, " \\'")}',
                    ${g.musteri_id})">✅ Fişi Kapat &amp; Yazdır</button>
            </div>
            <span class="accordion-chevron">▼</span>
        </div>
        <div class="accordion-body" id="teacc-body-${idx}">
            <table>
                <thead>
                    <tr>
                        <th>Ürün Kodu</th>
                        <th>Ürün Adı</th>
                        <th>Teslim Edilen (KG)</th>
                        <th>Tarih</th>
                    </tr>
                </thead>
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
                    html += `</tbody>
            </table>
            <div style="text-align:right; padding:10px; border-top:1px solid var(--border); margin-top:10px;">
                <strong>Toplam: ${toplamKg.toFixed(1)} KG</strong>
                <button class="btn-sm btn-teslim" style="margin-left:15px;"
                    onclick="fisKapatVeYazdir('${g.firma_adi.replace(/'/g, " \\'")}', ${g.musteri_id})">✅ Fişi Kapat
                    &amp; Yazdır</button>
            </div>
        </div>
    </div>`;
                });
                container.innerHTML = html;
            } catch (e) {
                console.error(e);
                const c = document.getElementById('teslimEdilenlerContainer');
                if (c) c.innerHTML = '<div class="card" style="text-align:center; padding:30px; color:var(--danger);">Hata oluştu.
    </div > ';
            }
        }

        // Özel onay modalı (Chrome confirm() engelleyebilir)
        function showFisOnayModal(firmaAdi, musteriId) {
            // Varsa eski modal'ı kaldır
            const old = document.getElementById('fisOnayModal');
            if (old) old.remove();

            const modal = document.createElement('div');
            modal.id = 'fisOnayModal';
            modal.style.cssText =
                'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);z-index:10000;display:flex;align-items:center;justify-content:center;backdrop-filter:blur(4px);';
            modal.innerHTML = `
    <div
        style="background:var(--card-bg, #1e293b);border:1px solid var(--border-color, #334155);border-radius:16px;padding:32px;max-width:440px;width:90%;box-shadow:0 25px 60px rgba(0,0,0,0.5);text-align:center;">
        <div style="font-size:48px;margin-bottom:16px;">🧾</div>
        <h3 style="color:#fff;margin:0 0 12px;font-size:1.15rem;">Teslimat Fişi Oluştur</h3>
        <p style="color:#94a3b8;font-size:0.92rem;line-height:1.6;margin:0 0 8px;">
            <strong style="color:#e2e8f0;">"${firmaAdi}"</strong> için teslimat hesabı kapatılacak ve fiş oluşturulacak.
        </p>
        <p style="color:#64748b;font-size:0.82rem;margin:0 0 24px;">
            Bundan sonra yapılacak teslimatlar ayrı bir fiş olarak kaydedilecektir.
        </p>
        <div style="display:flex;gap:12px;justify-content:center;">
            <button id="fisOnayIptal"
                style="padding:10px 28px;border-radius:10px;border:1px solid var(--border-color, #475569);background:transparent;color:#94a3b8;cursor:pointer;font-size:0.95rem;font-weight:500;">Vazgeç</button>
            <button id="fisOnayTamam"
                style="padding:10px 28px;border-radius:10px;border:none;background:linear-gradient(135deg,#10b981,#059669);color:#fff;cursor:pointer;font-size:0.95rem;font-weight:600;box-shadow:0 4px 15px rgba(16,185,129,0.3);">✅
                Onayla & Oluştur</button>
        </div>
    </div>`;
            document.body.appendChild(modal);

            document.getElementById('fisOnayIptal').onclick = () => modal.remove();
            modal.onclick = (e) => { if (e.target === modal) modal.remove(); };
            document.getElementById('fisOnayTamam').onclick = () => {
                modal.remove();
                fisKapatVeYazdirOnaylandi(firmaAdi, musteriId);
            };
        }

        // fisKapatVeYazdir artık custom modal açıyor (Chrome uyumlu)
        function fisKapatVeYazdir(firmaAdi, musteriId) {
            showFisOnayModal(firmaAdi, musteriId);
        }

        // Onay sonrası çalışan ana fonksiyon
        async function fisKapatVeYazdirOnaylandi(firmaAdi, musteriId) {
            try {
                // 1. DB'ye fiş olustur (güncel kur ile)
                const res = await fetch('../api/siparis_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'teslimat_fis_olustur', musteri_id: musteriId, usd_kuru: currentUsdRate })
                });
                const r = await res.json();
                if (r.status !== 'success') {
                    alert('Hata: ' + (r.message || 'Bilinmeyen hata'));
                    return;
                }

                showNotification(r.mesaj || 'Fiş oluşturuldu!');

                // 2. Fişi yazdır (iframe ile — popup blocker sorunu yok)
                await printTeslimatFisiProfesyonel(r.fis_id);

                // 3. Teslim edilenler listesini yenile
                refreshTeslimatTab();

            } catch (e) {
                console.error(e);
                alert('Sistemsel hata oluştu.');
            }
        }

        // printTeslimatFisi artık kullanılmıyor, printTeslimatFisiProfesyonel kullanılıyor

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

                if (!Array.isArray(data) || data.length === 0) {
                    container.innerHTML = '<div class="card" style="text-align:center; padding:30px;">Arşivlenmiş teslimat fişi
                    bulunamadı.</div > ';
                    return;
                }

                renderTeslimatFisiAccordion(data, container);
            } catch (e) {
                console.error(e);
                container.innerHTML = '<div class="card" style="text-align:center; padding:30px; color:var(--danger);">Hata oluştu.
    </div > ';
            }
        }

        function renderTeslimatFisiAccordion(data, container) {
            let html = '';
            data.forEach((fis, idx) => {
                const tarihDate = new Date(fis.olusturma_tarihi);
                const tarihStr = fis.olusturma_tarihi ? tarihDate.toLocaleDateString('tr-TR', {
                    day: '2-digit', month: '2-digit',
                    year: 'numeric', hour: '2-digit', minute: '2-digit'
                }) : '-';
                const toplamKg = parseFloat(fis.toplam_kg || 0);
                const toplamUsd = parseFloat(fis.toplam_usd || 0);
                const toplamTl = parseFloat(fis.toplam_tl || 0);

                html += `
    <div class="product-accordion" id="tfacc-${idx}">
        <div class="accordion-header" onclick="toggleTeslimatFisiAccordion(${idx}, ${fis.fis_id})">
            <span class="product-code" style="min-width:100px;">🧾 #${fis.fis_id}</span>
            <span class="product-name" style="font-weight:600; color:#fff;">👤 ${fis.firma_adi}</span>
            <div class="product-stats">
                <span class="stat-badge kg">${toplamKg.toFixed(1)} KG</span>
                <span class="stat-badge customers">${fis.urun_sayisi} kalem</span>
                <span style="font-size:0.82rem; color:var(--success); font-weight:600;">$ ${toplamUsd.toFixed(2)}</span>
                <span style="font-size:0.82rem; color:var(--accent); font-weight:600;">₺ ${toplamTl.toFixed(2)}</span>
                <span class="stat-badge geldi">${tarihStr}</span>
                <button class="btn-sm btn-teslim" style="margin-left:6px;"
                    onclick="event.stopPropagation(); printTeslimatFisiProfesyonel(${fis.fis_id})">🖨️ Yazdır</button>
            </div>
            <span class="accordion-chevron">▼</span>
        </div>
        <div class="accordion-body" id="tfacc-body-${idx}">
            <div style="padding:20px; text-align:center; color:var(--text-main);">Yükleniyor...</div>
        </div>
    </div>`;
            });
            container.innerHTML = html;
        }

        async function toggleTeslimatFisiAccordion(idx, fisId) {
            const el = document.getElementById('tfacc-' + idx);
            const body = document.getElementById('tfacc-body-' + idx);
            if (el.classList.contains('open')) { el.classList.remove('open'); return; }
            document.querySelectorAll('#teslimatFisiAccordionContainer .product-accordion.open').forEach(a => {
                if (a.id !== 'tfacc-' + idx) a.classList.remove('open');
            });
            el.classList.add('open');

            if (body.getAttribute('data-loaded')) return;

            try {
                const res = await fetch('../api/siparis_api.php?action=teslimat_fis_detay&fis_id=' + fisId + '&_t=' + Date.now());
                const data = await res.json();
                if (data.length === 0) {
                    body.innerHTML = '<div style="padding:20px; text-align:center;">Detay bulunamadı.</div>';
                    return;
                }

                const iskonto = parseFloat(data[0].iskonto_orani || 0);
                const fisUsdKuru = parseFloat(data[0].usd_kuru || 0);

                let html = `<table>
        <thead>
            <tr>
                <th>#</th>
                <th>Ürün Kodu</th>
                <th>Ürün Adı</th>
                <th>Miktar (KG)</th>
                <th>Birim Fiyat (USD)</th>
                <th>İskontolu Fiyat</th>
                <th>Satır Toplamı (USD)</th>
            </tr>
        </thead>
        <tbody>`;

                let brutToplam = 0;
                let netToplam = 0;
                data.forEach((item, i) => {
                    const miktar = parseFloat(item.teslim_edilen_kg || 0);
                    const birimFiyat = parseFloat(item.usd_fiyat || 0);
                    const iskontoFiyat = birimFiyat - (birimFiyat * iskonto / 100);
                    const brut = miktar * birimFiyat;
                    const net = miktar * iskontoFiyat;
                    brutToplam += brut;
                    netToplam += net;

                    html += `<tr>
                <td>${i + 1}</td>
                <td><strong>${item.urun_kodu}</strong></td>
                <td>${item.urun_adi}</td>
                <td>${miktar.toFixed(1)} KG</td>
                <td>$ ${birimFiyat.toFixed(2)}</td>
                <td style="color:var(--success);">$ ${iskontoFiyat.toFixed(2)}</td>
                <td style="color:var(--success);font-weight:600;">$ ${net.toFixed(2)}</td>
            </tr>`;
                });

                const kurGosterim = fisUsdKuru > 0 ? fisUsdKuru : currentUsdRate;
                const tlToplam = kurGosterim > 0 ? netToplam * kurGosterim : 0;
                const iskontoToplam = brutToplam - netToplam;

                html += `</tbody>
    </table>
    <div
        style="padding:14px 18px; border-top:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
        <div style="font-size:0.85rem; color:var(--text-main);">Kur: 1 USD = ${kurGosterim.toFixed(2)} ₺ &nbsp;|&nbsp;
            İskonto: % ${iskonto.toFixed(1)}</div>
        <div style="display:flex; gap:16px; align-items:center; flex-wrap:wrap;">
            <span style="font-size:0.85rem;">Brüt: <strong>$ ${brutToplam.toFixed(2)}</strong></span>
            <span style="font-size:0.85rem; color:var(--danger);">İskonto: <strong>- $
                    ${iskontoToplam.toFixed(2)}</strong></span>
            <span style="font-size:1.05rem; color:var(--success); font-weight:700;">Net: $
                ${netToplam.toFixed(2)}</span>
            <span style="font-size:1.05rem; color:var(--accent); font-weight:700;">₺ ${tlToplam.toFixed(2)}</span>
        </div>
    </div>`;
                body.innerHTML = html;
                body.setAttribute('data-loaded', 'true');
            } catch (e) { body.innerHTML = '<div style="padding:20px; color:var(--danger);">Hata oluştu.</div>'; }
        }

        async function printTeslimatFisiProfesyonel(fisId) {
            let url = '../api/siparis_api.php?action=teslimat_fis_detay&fis_id=' + fisId + '&_t=' + Date.now();

            try {
                const res = await fetch(url, { cache: 'no-store' });
                const data = await res.json();
                if (!data || data.length === 0) { alert('Fiş detayı bulunamadı.'); return; }

                const firmaAdi = data[0].firma_adi;
                const telefon = data[0].telefon || '-';
                const iskonto = parseFloat(data[0].iskonto_orani || 0);
                const fisUsdKuru = parseFloat(data[0].usd_kuru || 0);
                const kur = fisUsdKuru > 0 ? fisUsdKuru : currentUsdRate;
                const tarihDate = new Date(data[0].olusturma_tarihi);
                const tarihStr = tarihDate.toLocaleDateString('tr-TR', {
                    year: 'numeric', month: 'long', day: 'numeric', hour:
                        '2-digit', minute: '2-digit'
                });

                let satirlar = '';
                let genelBrut = 0;
                let toplamKg = 0;
                data.forEach((d, i) => {
                    const miktar = parseFloat(d.teslim_edilen_kg || 0);
                    const birimFiyat = parseFloat(d.usd_fiyat || 0);
                    const iskontoFiyat = birimFiyat - (birimFiyat * iskonto / 100);
                    const brut = miktar * birimFiyat;
                    const net = miktar * iskontoFiyat;
                    genelBrut += brut;
                    toplamKg += miktar;
                    satirlar += `<tr>
        <td style="padding:10px 14px; border-bottom:1px solid #e0e0e0; text-align:center; color:#666;">${i + 1}</td>
        <td
            style="padding:10px 14px; border-bottom:1px solid #e0e0e0; font-weight:700; font-family:'Courier New',monospace; color:#1a1a1a;">
            ${d.urun_kodu}</td>
        <td style="padding:10px 14px; border-bottom:1px solid #e0e0e0; color:#333;">${d.urun_adi}</td>
        <td style="padding:10px 14px; border-bottom:1px solid #e0e0e0; text-align:right; color:#333;">
            ${miktar.toFixed(1)} KG</td>
        <td style="padding:10px 14px; border-bottom:1px solid #e0e0e0; text-align:right; color:#666;">$
            ${birimFiyat.toFixed(2)}</td>
        <td
            style="padding:10px 14px; border-bottom:1px solid #e0e0e0; text-align:right; color:#2e7d32; font-weight:500;">
            $ ${iskontoFiyat.toFixed(2)}</td>
        <td
            style="padding:10px 14px; border-bottom:1px solid #e0e0e0; text-align:right; font-weight:700; color:#1a1a1a;">
            $ ${net.toFixed(2)}</td>
    </tr>`;
                });

                const genelIskonto = genelBrut * (iskonto / 100);
                const genelNet = genelBrut - genelIskonto;
                const genelTL = kur > 0 ? genelNet * kur : 0;

                const printHTML = `
    <!DOCTYPE html>
    <html>

    <head>
        <meta charset="UTF-8">
        <title>Teslimat Fişi - ${firmaAdi} (#${fisId})</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
                padding: 40px 45px;
                color: #222;
                line-height: 1.5;
                background: #fff;
            }

            /* HEADER */
            .fis-header {
                display: flex;
                justify-content: space-between;
                align-items: flex-end;
                padding-bottom: 20px;
                margin-bottom: 24px;
                border-bottom: 3px solid #1a1a1a;
            }

            .fis-header .brand {}

            .fis-header .brand h1 {
                font-size: 28px;
                font-weight: 900;
                letter-spacing: 3px;
                color: #1a1a1a;
                margin-bottom: 2px;
            }

            .fis-header .brand .subtitle {
                font-size: 13px;
                color: #888;
                text-transform: uppercase;
                letter-spacing: 2px;
            }

            .fis-header .fis-no {
                text-align: right;
            }

            .fis-header .fis-no .no {
                font-size: 22px;
                font-weight: 800;
                color: #1a1a1a;
            }

            .fis-header .fis-no .tarih {
                font-size: 12px;
                color: #777;
                margin-top: 2px;
            }

            /* INFO BOX */
            .fis-info {
                display: flex;
                justify-content: space-between;
                margin-bottom: 28px;
                padding: 16px 20px;
                background: linear-gradient(135deg, #f8f9fa, #f0f2f5);
                border-radius: 8px;
                border-left: 4px solid #1a1a1a;
            }

            .fis-info .col {
                font-size: 13px;
                line-height: 2;
            }

            .fis-info .col strong {
                color: #1a1a1a;
                font-weight: 600;
            }

            .fis-info .col .highlight {
                display: inline-block;
                background: #e3f2fd;
                color: #1565c0;
                padding: 2px 10px;
                border-radius: 4px;
                font-weight: 700;
                font-size: 12px;
            }

            /* TABLE */
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 0;
            }

            thead tr {
                background: #1a1a1a;
            }

            th {
                color: #fff;
                padding: 11px 14px;
                font-size: 11px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.8px;
            }

            tbody tr:nth-child(even) {
                background: #fafafa;
            }

            tbody tr:hover {
                background: #f0f7ff;
            }

            /* TOTALS */
            .totals-wrapper {
                border: 2px solid #1a1a1a;
                border-top: none;
                border-radius: 0 0 8px 8px;
                overflow: hidden;
            }

            .totals-row {
                display: flex;
                justify-content: flex-end;
                padding: 8px 20px;
                font-size: 13px;
                border-bottom: 1px solid #eee;
            }

            .totals-row:last-child {
                border-bottom: none;
            }

            .totals-row .label {
                width: 180px;
                text-align: right;
                color: #666;
                padding-right: 16px;
            }

            .totals-row .value {
                width: 150px;
                text-align: right;
                font-weight: 700;
            }

            .totals-row.highlight {
                background: #1a1a1a;
                color: #fff;
                padding: 12px 20px;
                font-size: 16px;
            }

            .totals-row.highlight .label {
                color: #bbb;
            }

            .totals-row.highlight .value {
                font-size: 18px;
                letter-spacing: 0.5px;
            }

            .totals-row.tl {
                background: #2e7d32;
                color: #fff;
                padding: 12px 20px;
                font-size: 16px;
            }

            .totals-row.tl .label {
                color: #c8e6c9;
            }

            .totals-row.tl .value {
                font-size: 18px;
                letter-spacing: 0.5px;
            }

            /* KUR + KG */
            .meta-info {
                display: flex;
                justify-content: space-between;
                margin-top: 10px;
                font-size: 11px;
                color: #999;
            }

            /* SIGNATURES */
            .sign-area {
                display: flex;
                justify-content: space-between;
                margin-top: 70px;
            }

            .sign-box {
                width: 200px;
                text-align: center;
            }

            .sign-box .line {
                border-top: 1.5px solid #333;
                margin-top: 60px;
                padding-top: 8px;
                font-size: 13px;
                color: #555;
                font-weight: 500;
            }

            .footer {
                text-align: center;
                margin-top: 30px;
                padding-top: 14px;
                border-top: 1px solid #e0e0e0;
                font-size: 10px;
                color: #bbb;
                letter-spacing: 0.5px;
            }

            @media print {
                body {
                    padding: 15px 20px;
                }

                .fis-info {
                    background: linear-gradient(135deg, #f8f9fa, #f0f2f5) !important;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                thead tr {
                    background: #1a1a1a !important;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                th {
                    color: #fff !important;
                }

                tbody tr:nth-child(even) {
                    background: #fafafa !important;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                .totals-row.highlight {
                    background: #1a1a1a !important;
                    color: #fff !important;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                .totals-row.tl {
                    background: #2e7d32 !important;
                    color: #fff !important;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                .fis-info .col .highlight {
                    background: #e3f2fd !important;
                    color: #1565c0 !important;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }
            }
        </style>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"><\/script>
    </head>

    <body>

        <div class="fis-header">
            <div class="brand">
                <h1>AKSA TOPTAN</h1>
                <div class="subtitle">Teslimat Fişi</div>
            </div>
            <div class="fis-no">
                <div class="no">#${fisId}</div>
                <div class="tarih">${tarihStr}</div>
            </div>
        </div>

        <div class="fis-info">
            <div class="col">
                <strong>Müşteri:</strong> ${firmaAdi}<br>
                <strong>Telefon:</strong> ${telefon}<br>
                <strong>İskonto:</strong> <span class="highlight">% ${iskonto.toFixed(1)}</span>
            </div>
            <div class="col" style="text-align:right;">
                <strong>Fiş No:</strong> #${fisId}<br>
                <strong>Tarih:</strong> ${tarihStr}<br>
                <strong>Kalem Sayısı:</strong> ${data.length}
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width:30px;text-align:center;">#</th>
                    <th style="width:100px;">Ürün Kodu</th>
                    <th>Ürün Adı</th>
                    <th style="width:80px;text-align:right;">Miktar</th>
                    <th style="width:95px;text-align:right;">Birim Fiyat</th>
                    <th style="width:95px;text-align:right;">İsk. Fiyat</th>
                    <th style="width:105px;text-align:right;">Toplam</th>
                </tr>
            </thead>
            <tbody>${satirlar}</tbody>
        </table>

        <div class="totals-wrapper">
            <div class="totals-row">
                <div class="label">Toplam KG:</div>
                <div class="value">${toplamKg.toFixed(1)} KG</div>
            </div>
            <div class="totals-row">
                <div class="label">Toplam Brüt Tutar:</div>
                <div class="value">$ ${genelBrut.toFixed(2)}</div>
            </div>
            <div class="totals-row" style="color:#c62828;">
                <div class="label">Toplam İskonto (% ${iskonto.toFixed(1)}):</div>
                <div class="value">- $ ${genelIskonto.toFixed(2)}</div>
            </div>
            <div class="totals-row highlight">
                <div class="label">NET TOPLAM (USD):</div>
                <div class="value">$ ${genelNet.toFixed(2)}</div>
            </div>
            ${kur > 0 ? `
            <div class="totals-row tl">
                <div class="label">NET TOPLAM (TL):</div>
                <div class="value">₺ ${genelTL.toFixed(2)}</div>
            </div>` : ''}
        </div>

        <div class="meta-info">
            <span>Toplam ${data.length} kalem ürün teslim edilmiştir.</span>
            ${kur > 0 ? `<span>Döviz Kuru: 1 USD = ${kur.toFixed(4)} ₺</span>` : ''}
        </div>

        <div class="sign-area">
            <div class="sign-box">
                <div class="line">Teslim Eden</div>
            </div>
            <div class="sign-box">
                <div class="line">Teslim Alan</div>
            </div>
        </div>

        <div class="footer">
            Bu belge AKSA TOPTAN sipariş yönetim sistemi tarafından elektronik olarak oluşturulmuştur.
        </div>

    </body>

    </html>`;

                // Gizli iframe ile yazdır (popup blocker sorunu yok)
                let printFrame = document.getElementById('printFrame');
                if (!printFrame) {
                    printFrame = document.createElement('iframe');
                    printFrame.id = 'printFrame';
                    printFrame.style.cssText = 'position:fixed;right:0;bottom:0;width:0;height:0;border:none;';
                    document.body.appendChild(printFrame);
                }
                printFrame.srcdoc = printHTML;
                printFrame.onload = () => {
                    setTimeout(() => {
                        try { printFrame.contentWindow.print(); } catch (e) {
                            // Fallback: yeni sekme aç
                            const blob = new Blob([printHTML], { type: 'text/html; charset=utf-8' });
                            const blobUrl = URL.createObjectURL(blob);
                            window.open(blobUrl, '_blank');
                        }
                    }, 300);
                };
            } catch (e) {
                console.error(e);
                alert('Fiş yazdırılırken hata oluştu.');
            }
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
                <tr>
                    <td colspan="4" style="text-align:center;">Yükleniyor...</td>
                </tr>
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
            <button type="submit" style="background-color: var(--success); width: 100%;">Dağıtımı Onayla ve
                Kaydet</button>
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
                <tr>
                    <td colspan="4" style="text-align:center;">Yükleniyor...</td>
                </tr>
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
                    tbody.innerHTML = '<tr>
                        < td colspan = "4" style = "text-align:center;" > Kayıt yok.</td >
    </tr > ';
                    return;
                }
                data.forEach(t => {
                    tbody.innerHTML += `
    <tr>
        <td>${t.tarih}</td>
        <td><strong>${t.firma_adi}</strong></td>
        <td><b style="color:var(--success)">$ ${parseFloat(t.toplam_usd).toFixed(2)}</b> (₺
            ${parseFloat(t.toplam_tl).toFixed(2)})</td>
        <td>
            <button onclick="printTeslimatFişi(${t.id})" style="padding: 4px 10px; background-color:#17a2b8;">Fiş
                Yazdır</button>
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
            body {
                font-family: sans-serif;
                padding: 40px;
            }

            .header {
                text-align: center;
                border-bottom: 2px solid #000;
                margin-bottom: 30px;
                padding-bottom: 10px;
            }

            .info {
                margin-bottom: 20px;
                display: flex;
                justify-content: space-between;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }

            th,
            td {
                border: 1px solid #000;
                padding: 10px;
                text-align: left;
            }

            .totals {
                text-align: right;
                margin-top: 20px;
                font-weight: bold;
                font-size: 1.2rem;
            }
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
            <div style="text-align:center; width: 200px; border-top: 1px solid #000; padding-top:10px;">Teslim Eden
            </div>
            <div style="text-align:center; width: 200px; border-top: 1px solid #000; padding-top:10px;">Teslim Alan
            </div>
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

                if (data.length === 0) {
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

        // ====================================================
        // RAPORLAR & İSTATİSTİK — Modern Yeniden Tasarım
        // ====================================================
        function loadRaporlar() {
            const html = `
            <style>
                .rpr-preset-bar { display:flex; gap:6px; flex-wrap:wrap; margin-bottom:16px; }
                .rpr-preset { padding:6px 14px; border-radius:20px; border:1px solid var(--border); background:transparent; color:var(--text-main); cursor:pointer; font-size:0.82rem; transition:all .2s; }
                .rpr-preset:hover, .rpr-preset.active { background:var(--accent); color:#fff; border-color:var(--accent); }
                .rpr-mode-tabs { display:flex; gap:0; margin-bottom:20px; border:1px solid var(--border); border-radius:10px; overflow:hidden; }
                .rpr-mode-tab { flex:1; padding:10px; text-align:center; cursor:pointer; font-size:0.9rem; font-weight:600; transition:all .2s; background:transparent; color:var(--text-main); border:none; }
                .rpr-mode-tab.active { background:var(--accent); color:#fff; }
                .rpr-stat-cards { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:14px; margin-bottom:24px; }
                .rpr-stat { padding:18px 20px; border-radius:12px; border:1px solid; background:rgba(255,255,255,.03); }
                .rpr-stat-label { font-size:0.78rem; color:var(--text-main); margin-bottom:6px; text-transform:uppercase; letter-spacing:.5px; }
                .rpr-stat-value { font-size:1.55rem; font-weight:800; }
                .rpr-charts-row { display:grid; grid-template-columns:2fr 1fr; gap:18px; margin-bottom:24px; }
                @media(max-width:768px){ .rpr-charts-row { grid-template-columns:1fr; } }
                .rpr-chart-box { background:rgba(255,255,255,.02); border:1px solid var(--border); border-radius:12px; padding:18px; }
                .rpr-chart-title { font-size:0.88rem; font-weight:600; color:var(--text-light); margin-bottom:14px; }
                .rpr-table-wrap { overflow-x:auto; }
                .rpr-table { width:100%; border-collapse:collapse; font-size:0.87rem; }
                .rpr-table th { padding:10px 14px; background:rgba(255,255,255,.05); color:var(--text-main); font-weight:600; text-align:left; font-size:0.78rem; text-transform:uppercase; letter-spacing:.5px; }
                .rpr-table td { padding:10px 14px; border-bottom:1px solid rgba(255,255,255,.04); }
                .rpr-table tr:hover td { background:rgba(255,255,255,.04); }
                .rpr-no-data { text-align:center; padding:40px; color:var(--text-main); font-size:0.9rem; }
                .rpr-filter-row { display:flex; gap:10px; flex-wrap:wrap; align-items:center; margin-bottom:18px; }
                .rpr-filter-row select, .rpr-filter-row input[type=date] { padding:8px 12px; border-radius:8px; border:1px solid var(--border); background:var(--card-bg,#1e293b); color:var(--text-light); font-size:0.88rem; min-width:160px; }
                .rpr-filter-label { font-size:0.82rem; color:var(--text-main); white-space:nowrap; }
            </style>

            <div class="card" style="margin-bottom:0;">
                <div class="card-title" style="margin-bottom:18px;">📊 Raporlar & İstatistik</div>

                <!-- Tarih Ön-Ayarları -->
                <div class="rpr-preset-bar" id="rprPresetBar">
                    <button class="rpr-preset" onclick="rprSetPreset('today',this)">Bugün</button>
                    <button class="rpr-preset" onclick="rprSetPreset('yesterday',this)">Dün</button>
                    <button class="rpr-preset" onclick="rprSetPreset('thisweek',this)">Bu Hafta</button>
                    <button class="rpr-preset" onclick="rprSetPreset('thismonth',this)">Bu Ay</button>
                    <button class="rpr-preset" onclick="rprSetPreset('last7',this)">Son 7 Gün</button>
                    <button class="rpr-preset" onclick="rprSetPreset('last30',this)">Son 30 Gün</button>
                    <button class="rpr-preset" onclick="rprSetPreset('thisyear',this)">Bu Yıl</button>
                    <button class="rpr-preset active" onclick="rprSetPreset('all',this)">Tümü</button>
                </div>

                <!-- Özel Tarih Aralığı -->
                <div class="rpr-filter-row">
                    <span class="rpr-filter-label">📅 Tarih Aralığı:</span>
                    <input type="date" id="rprTarihBas">
                    <span style="color:var(--text-main);">—</span>
                    <input type="date" id="rprTarihSon">
                </div>

                <!-- Görünüm Modu Seçimi -->
                <div class="rpr-mode-tabs">
                    <button class="rpr-mode-tab active" id="rprTabMusteri" onclick="rprSetMode('musteri')">👤 Müşteri Bazlı</button>
                    <button class="rpr-mode-tab" id="rprTabUrun" onclick="rprSetMode('urun')">📦 Ürün Bazlı</button>
                </div>

                <!-- Müşteri Bazlı Filtre -->
                <div id="rprMusteriFilter" class="rpr-filter-row">
                    <span class="rpr-filter-label">Müşteri:</span>
                    <select id="rprMusteri">
                        <option value="">— Tüm Müşteriler —</option>
                    </select>
                    <button class="btn-sm btn-bulk-primary" onclick="rprFetch()">🔍 Filtrele</button>
                </div>

                <!-- Ürün Bazlı Filtre -->
                <div id="rprUrunFilter" class="rpr-filter-row" style="display:none;">
                    <span class="rpr-filter-label">Ürün:</span>
                    <select id="rprUrun">
                        <option value="">— Ürün Seçin —</option>
                    </select>
                    <button class="btn-sm btn-bulk-primary" onclick="rprFetch()">🔍 Filtrele</button>
                </div>

                <!-- Özet Kartlar -->
                <div class="rpr-stat-cards" id="rprStatCards">
                    <div class="rpr-stat" style="border-color:var(--accent);">
                        <div class="rpr-stat-label">Toplam KG</div>
                        <div class="rpr-stat-value" id="rprStat-kg" style="color:var(--accent)">— KG</div>
                    </div>
                    <div class="rpr-stat" style="border-color:#6366f1;">
                        <div class="rpr-stat-label">Brüt Ciro (USD)</div>
                        <div class="rpr-stat-value" id="rprStat-brut" style="color:#818cf8">$ —</div>
                    </div>
                    <div class="rpr-stat" style="border-color:var(--danger);">
                        <div class="rpr-stat-label">Toplam İskonto</div>
                        <div class="rpr-stat-value" id="rprStat-iskonto" style="color:var(--danger)">- $ —</div>
                    </div>
                    <div class="rpr-stat" style="border-color:var(--success);">
                        <div class="rpr-stat-label">Net Toplam (USD)</div>
                        <div class="rpr-stat-value" id="rprStat-net" style="color:var(--success)">$ —</div>
                    </div>
                    <div class="rpr-stat" style="border-color:#f59e0b;">
                        <div class="rpr-stat-label">TL Karşılığı</div>
                        <div class="rpr-stat-value" id="rprStat-tl" style="color:#f59e0b">₺ —</div>
                    </div>
                </div>

                <!-- Grafik Alanı -->
                <div class="rpr-charts-row">
                    <div class="rpr-chart-box">
                        <div class="rpr-chart-title" id="rprBarTitle">📊 KG Dağılımı (Bar)</div>
                        <canvas id="rprBarChart" style="max-height:300px;"></canvas>
                    </div>
                    <div class="rpr-chart-box">
                        <div class="rpr-chart-title" id="rprPieTitle">🍩 Net Tutar Dağılımı</div>
                        <canvas id="rprPieChart" style="max-height:300px;"></canvas>
                    </div>
                </div>

                <!-- Detay Tablosu -->
                <div id="rprTableTitle" style="font-size:0.95rem; font-weight:700; color:var(--text-light); margin-bottom:12px;">📋 Detay Listesi</div>
                <div class="rpr-table-wrap">
                    <table class="rpr-table">
                        <thead id="rprTableHead"></thead>
                        <tbody id="rprTableBody">
                            <tr><td colspan="6" class="rpr-no-data">Filtre uygulayarak rapor alın.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>`;

            document.getElementById('content-area').innerHTML = html;

            // Müşteri listesini doldur
            fetch('../api/siparis_api.php?action=musteriler_listesi')
                .then(r => r.json())
                .then(data => {
                    const sel = document.getElementById('rprMusteri');
                    if (sel) data.forEach(m => sel.innerHTML += `<option value="${m.id}">${m.firma_adi}</option>`);
                }).catch(e => console.error(e));

            // Ürün listesini doldur
            fetch('../api/siparis_api.php?action=urunler_listesi')
                .then(r => r.json())
                .then(data => {
                    const sel = document.getElementById('rprUrun');
                    if (sel) data.forEach(u => sel.innerHTML += `<option value="${u.urun_kodu}">${u.urun_adi} (${u.urun_kodu})</option>`);
                }).catch(e => console.error(e));

            // Tarih inputlarını takip et (preset'i kaldır)
            ['rprTarihBas', 'rprTarihSon'].forEach(id => {
                document.getElementById(id)?.addEventListener('change', () => {
                    document.querySelectorAll('.rpr-preset').forEach(b => b.classList.remove('active'));
                });
            });

            // İlk yükleme
            rprFetch();
        }

        // Mevcut filtre modu
        let _rprMode = 'musteri';
        let _rprBarChart = null;
        let _rprPieChart = null;

        function rprSetMode(mode) {
            _rprMode = mode;
            document.getElementById('rprTabMusteri').classList.toggle('active', mode === 'musteri');
            document.getElementById('rprTabUrun').classList.toggle('active', mode === 'urun');
            document.getElementById('rprMusteriFilter').style.display = mode === 'musteri' ? 'flex' : 'none';
            document.getElementById('rprUrunFilter').style.display = mode === 'urun' ? 'flex' : 'none';
            rprFetch();
        }

        function rprSetPreset(preset, btn) {
            document.querySelectorAll('.rpr-preset').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const today = new Date();
            const fmt = d => d.toISOString().split('T')[0];
            let bas = '', son = fmt(today);
            if (preset === 'today') { bas = fmt(today); }
            else if (preset === 'yesterday') { const d = new Date(today); d.setDate(d.getDate() - 1); bas = fmt(d); son = fmt(d); }
            else if (preset === 'thisweek') { const d = new Date(today); d.setDate(d.getDate() - d.getDay() + (d.getDay() === 0 ? -6 : 1)); bas = fmt(d); }
            else if (preset === 'thismonth') { bas = fmt(new Date(today.getFullYear(), today.getMonth(), 1)); }
            else if (preset === 'last7') { const d = new Date(today); d.setDate(d.getDate() - 6); bas = fmt(d); }
            else if (preset === 'last30') { const d = new Date(today); d.setDate(d.getDate() - 29); bas = fmt(d); }
            else if (preset === 'thisyear') { bas = today.getFullYear() + '-01-01'; }
            else { bas = ''; son = ''; }
            const b = document.getElementById('rprTarihBas');
            const s = document.getElementById('rprTarihSon');
            if (b) b.value = bas;
            if (s) s.value = son;
            rprFetch();
        }

        async function rprFetch() {
            const tbody = document.getElementById('rprTableBody');
            const thead = document.getElementById('rprTableHead');
            if (tbody) tbody.innerHTML = '<tr><td colspan="6" class="rpr-no-data">⏳ Yükleniyor...</td></tr>';

            const tb = document.getElementById('rprTarihBas')?.value || '';
            const ts = document.getElementById('rprTarihSon')?.value || '';

            try {
                let url, data;

                if (_rprMode === 'musteri') {
                    // Müşteri bazlı: ürünler bazında göster
                    const mid = document.getElementById('rprMusteri')?.value || '';
                    url = '../api/siparis_api.php?action=raporlar&_t=' + Date.now();
                    if (mid) url += '&musteri_id=' + mid;
                    if (tb) url += '&tarih_bas=' + tb;
                    if (ts) url += '&tarih_son=' + ts;

                    const res = await fetch(url);
                    data = await res.json();

                    // Başlıklar
                    thead.innerHTML = '<tr><th>#</th><th>Ürün Kodu</th><th>Ürün Adı</th><th>Toplam KG</th><th>Brüt (USD)</th><th>İskonto %</th><th>Net (USD)</th></tr>';

                    let toplamKg = 0, toplamBrut = 0, toplamNet = 0;
                    let tableRows = '';
                    const colors = rprColors(data.length);
                    const labels = [], kgData = [], netData = [];

                    if (!Array.isArray(data) || data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="7" class="rpr-no-data">Bu kriterlere uygun kayıt bulunamadı.</td></tr>';
                    } else {
                        data.forEach((u, i) => {
                            const kg = parseFloat(u.toplam_kg || 0);
                            const brut = parseFloat(u.toplam_brut || 0);
                            const net = parseFloat(u.toplam_net || 0);
                            const iskOran = brut > 0 ? ((brut - net) / brut * 100) : 0;
                            toplamKg += kg; toplamBrut += brut; toplamNet += net;
                            labels.push(u.urun_kodu + ' ' + (u.urun_adi?.substring(0, 18) || ''));
                            kgData.push(+kg.toFixed(2));
                            netData.push(+net.toFixed(2));
                            tableRows += `<tr>
                                <td style="color:var(--text-main)">${i + 1}</td>
                                <td><strong style="color:var(--accent)">${u.urun_kodu}</strong></td>
                                <td>${u.urun_adi}</td>
                                <td><strong>${kg.toFixed(1)} KG</strong></td>
                                <td style="color:#818cf8">$ ${brut.toFixed(2)}</td>
                                <td style="color:var(--danger)">% ${iskOran.toFixed(1)}</td>
                                <td style="color:var(--success);font-weight:700">$ ${net.toFixed(2)}</td>
                            </tr>`;
                        });
                        tbody.innerHTML = tableRows;
                        rprUpdateCharts(labels, kgData, netData, colors, 'Ürün (KG)', 'Net (USD)');
                    }
                    rprUpdateStats(toplamKg, toplamBrut, toplamNet);

                } else {
                    // Ürün bazlı: müşteriler bazında göster
                    const urunKodu = document.getElementById('rprUrun')?.value || '';
                    if (!urunKodu) {
                        tbody.innerHTML = '<tr><td colspan="6" class="rpr-no-data">Lütfen bir ürün seçin.</td></tr>';
                        rprUpdateStats(0, 0, 0);
                        return;
                    }
                    url = '../api/siparis_api.php?action=raporlar_urun_bazli&urun_kodu=' + encodeURIComponent(urunKodu) + '&_t=' + Date.now();
                    if (tb) url += '&tarih_bas=' + tb;
                    if (ts) url += '&tarih_son=' + ts;

                    const res = await fetch(url);
                    data = await res.json();

                    // Başlıklar
                    thead.innerHTML = '<tr><th>#</th><th>Müşteri</th><th>Toplam KG</th><th>Brüt (USD)</th><th>İskonto %</th><th>Net (USD)</th></tr>';

                    let toplamKg = 0, toplamBrut = 0, toplamNet = 0;
                    let tableRows = '';
                    const colors = rprColors(data.length);
                    const labels = [], kgData = [], netData = [];

                    if (!Array.isArray(data) || data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="6" class="rpr-no-data">Bu ürüne ait teslimat kaydı bulunamadı.</td></tr>';
                    } else {
                        data.forEach((m, i) => {
                            const kg = parseFloat(m.toplam_kg || 0);
                            const brut = parseFloat(m.toplam_brut || 0);
                            const net = parseFloat(m.toplam_net || 0);
                            const iskOran = parseFloat(m.iskonto_orani || 0);
                            toplamKg += kg; toplamBrut += brut; toplamNet += net;
                            labels.push(m.firma_adi);
                            kgData.push(+kg.toFixed(2));
                            netData.push(+net.toFixed(2));
                            tableRows += `<tr>
                                <td style="color:var(--text-main)">${i + 1}</td>
                                <td><strong>${m.firma_adi}</strong></td>
                                <td><strong>${kg.toFixed(1)} KG</strong></td>
                                <td style="color:#818cf8">$ ${brut.toFixed(2)}</td>
                                <td style="color:var(--danger)">% ${iskOran.toFixed(1)}</td>
                                <td style="color:var(--success);font-weight:700">$ ${net.toFixed(2)}</td>
                            </tr>`;
                        });
                        tbody.innerHTML = tableRows;
                        rprUpdateCharts(labels, kgData, netData, colors, 'Müşteri (KG)', 'Net (USD)');
                    }
                    rprUpdateStats(toplamKg, toplamBrut, toplamNet);
                }

            } catch (err) {
                console.error(err);
                if (tbody) tbody.innerHTML = '<tr><td colspan="7" class="rpr-no-data" style="color:var(--danger)">Hata oluştu: ' + err.message + '</td></tr>';
            }
        }

        function rprUpdateStats(kg, brut, net) {
            const iskonto = brut - net;
            const tl = currentUsdRate > 0 ? net * currentUsdRate : 0;
            const f = (n, prefix = '') => n > 0 ? prefix + n.toFixed(2) : '—';
            document.getElementById('rprStat-kg').textContent = kg > 0 ? kg.toFixed(1) + ' KG' : '—';
            document.getElementById('rprStat-brut').textContent = '$ ' + f(brut);
            document.getElementById('rprStat-iskonto').textContent = '- $ ' + f(iskonto);
            document.getElementById('rprStat-net').textContent = '$ ' + f(net);
            document.getElementById('rprStat-tl').textContent = '₺ ' + f(tl);
        }

        function rprColors(n) {
            const palette = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#f97316', '#84cc16', '#ec4899', '#14b8a6', '#a855f7', '#64748b'];
            const out = [];
            for (let i = 0; i < n; i++) out.push(palette[i % palette.length]);
            return out;
        }

        function rprUpdateCharts(labels, kgData, netData, colors, barLabel, pieLabel) {
            // Destroy existing charts
            if (_rprBarChart) { _rprBarChart.destroy(); _rprBarChart = null; }
            if (_rprPieChart) { _rprPieChart.destroy(); _rprPieChart = null; }

            const barCtx = document.getElementById('rprBarChart')?.getContext('2d');
            const pieCtx = document.getElementById('rprPieChart')?.getContext('2d');
            if (!barCtx || !pieCtx) return;

            const chartDefaults = {
                plugins: { legend: { labels: { color: '#94a3b8', font: { size: 11 } } } }
            };

            _rprBarChart = new Chart(barCtx, {
                type: 'bar',
                data: { labels, datasets: [{ label: barLabel, data: kgData, backgroundColor: colors, borderRadius: 6, borderSkipped: false }] },
                options: {
                    indexAxis: labels.length > 6 ? 'y' : 'x',
                    responsive: true, maintainAspectRatio: true,
                    plugins: { ...chartDefaults.plugins, tooltip: { callbacks: { label: ctx => ` ${ctx.parsed[labels.length > 6 ? 'x' : 'y'].toFixed(1)} KG` } } },
                    scales: {
                        x: { ticks: { color: '#64748b', font: { size: 10 } }, grid: { color: 'rgba(255,255,255,.05)' } },
                        y: { ticks: { color: '#64748b', font: { size: 10 } }, grid: { color: 'rgba(255,255,255,.05)' } }
                    }
                }
            });

            _rprPieChart = new Chart(pieCtx, {
                type: 'doughnut',
                data: { labels, datasets: [{ label: pieLabel, data: netData, backgroundColor: colors, borderWidth: 2, borderColor: '#1e293b' }] },
                options: {
                    responsive: true, maintainAspectRatio: true, cutout: '60%',
                    plugins: { ...chartDefaults.plugins, tooltip: { callbacks: { label: ctx => ` $ ${ctx.parsed.toFixed(2)}` } } }
                }
            });
        }

        // Eski fetchRaporData - yeni rprFetch ile değiştirildi (uyumluluk için alias)
        function fetchRaporData() { rprFetch(); }

        // Yardımcı Fonksiyon: Bildirim Gösterme
        function showNotification(message, type = 'success') {
            const noti = document.getElementById('notification');
            if (!noti) return;
            noti.textContent = message;
            noti.style.background = type === 'error' ? 'var(--danger)' : 'var(--success)';
            noti.style.display = 'block';
            setTimeout(() => { noti.style.display = 'none'; }, 3000);
        }
    