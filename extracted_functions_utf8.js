                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'detay_durum_guncelle', detay_id: detayId, yeni_durum: yeniDurum })
                });
                const result = await res.json();
                if (result.status === 'success') {
                    showNotification('SipariÅŸ durumu gÃ¼ncellendi!');
                    if (aktifSipTab === 'musteri') fetchMusteriSiparisleri();
                    else if (aktifSipTab === 'tedarik') fetchTedarikListesi();
                    else if (aktifSipTab === 'teslimat') refreshTeslimatTab();
                } else {
                    alert('Hata: ' + (result.message || 'Bilinmeyen hata'));
                }
            } catch (err) {
                alert('Sistemsel hata oluÅŸtu.');
            }
        }

        // =====================================
        // TAB 4: TESLÄ°MAT FÄ°ÅLERÄ°
        // =====================================
        let teslimatFisMusteriLoaded = false;

        function setTeslimatFisTarih(period, btn) {
            // Aktif chip'Ä± gÃ¼ncelle
            document.querySelectorAll('.date-chip').forEach(c => c.classList.remove('active'));
            if (btn) btn.classList.add('active');

            const basEl = document.getElementById('teslimatFisTarihBas');
            const sonEl = document.getElementById('teslimatFisTarihSon');
            const customEl = document.getElementById('customDateRange');

            const today = new Date();
            const fmt = d => d.toISOString().split('T')[0];

            if (period === 'custom') {
                if (customEl) customEl.style.display = 'flex';
                return; // Ã–zel aralÄ±kta tarih seÃ§imini kullanÄ±cÄ±ya bÄ±rak
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
            // 'all' â†’ boÅŸ bÄ±rak
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
                        sel.innerHTML = '<option value="">TÃ¼m MÃ¼ÅŸteriler</option>';
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
            container.innerHTML = '<div style="text-align:center; padding:40px; color:var(--text-main);">YÃ¼kleniyor...</div>';

            try {
                const res = await fetch(url, { cache: 'no-store' });
                const data = await res.json();

                if (!Array.isArray(data) || data.length === 0) {
                    container.innerHTML = '<div class="card" style="text-align:center; padding:30px;">ArÅŸivlenmiÅŸ teslimat fiÅŸi
                    bulunamadÄ±.</div > ';
                    return;
                }

                renderTeslimatFisiAccordion(data, container);
            } catch (e) {
                console.error(e);
                container.innerHTML = '<div class="card" style="text-align:center; padding:30px; color:var(--danger);">Hata oluÅŸtu.
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
            <span class="product-code" style="min-width:100px;">ğŸ§¾ #${fis.fis_id}</span>
            <span class="product-name" style="font-weight:600; color:#fff;">ğŸ‘¤ ${fis.firma_adi}</span>
            <div class="product-stats">
                <span class="stat-badge kg">${toplamKg.toFixed(1)} KG</span>
                <span class="stat-badge customers">${fis.urun_sayisi} kalem</span>
                <span style="font-size:0.82rem; color:var(--success); font-weight:600;">$ ${toplamUsd.toFixed(2)}</span>
                <span style="font-size:0.82rem; color:var(--accent); font-weight:600;">â‚º ${toplamTl.toFixed(2)}</span>
                <span class="stat-badge geldi">${tarihStr}</span>
                <button class="btn-sm btn-teslim" style="margin-left:6px;"
                    onclick="event.stopPropagation(); printTeslimatFisiProfesyonel(${fis.fis_id})">ğŸ–¨ï¸ YazdÄ±r</button>
            </div>
            <span class="accordion-chevron">â–¼</span>
        </div>
        <div class="accordion-body" id="tfacc-body-${idx}">
            <div style="padding:20px; text-align:center; color:var(--text-main);">YÃ¼kleniyor...</div>
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
                    body.innerHTML = '<div style="padding:20px; text-align:center;">Detay bulunamadÄ±.</div>';
                    return;
                }

                const iskonto = parseFloat(data[0].iskonto_orani || 0);
                const fisUsdKuru = parseFloat(data[0].usd_kuru || 0);

                let html = `<table>
        <thead>
            <tr>
                <th>#</th>
                <th>ÃœrÃ¼n Kodu</th>
                <th>ÃœrÃ¼n AdÄ±</th>
                <th>Miktar (KG)</th>
                <th>Birim Fiyat (USD)</th>
                <th>Ä°skontolu Fiyat</th>
                <th>SatÄ±r ToplamÄ± (USD)</th>
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
        <div style="font-size:0.85rem; color:var(--text-main);">Kur: 1 USD = ${kurGosterim.toFixed(2)} â‚º &nbsp;|&nbsp;
            Ä°skonto: % ${iskonto.toFixed(1)}</div>
        <div style="display:flex; gap:16px; align-items:center; flex-wrap:wrap;">
            <span style="font-size:0.85rem;">BrÃ¼t: <strong>$ ${brutToplam.toFixed(2)}</strong></span>
            <span style="font-size:0.85rem; color:var(--danger);">Ä°skonto: <strong>- $
                    ${iskontoToplam.toFixed(2)}</strong></span>
            <span style="font-size:1.05rem; color:var(--success); font-weight:700;">Net: $
                ${netToplam.toFixed(2)}</span>
            <span style="font-size:1.05rem; color:var(--accent); font-weight:700;">â‚º ${tlToplam.toFixed(2)}</span>
        </div>
    </div>`;
                body.innerHTML = html;
                body.setAttribute('data-loaded', 'true');
            } catch (e) { body.innerHTML = '<div style="padding:20px; color:var(--danger);">Hata oluÅŸtu.</div>'; }
        }

        async function printTeslimatFisiProfesyonel(fisId) {
            let url = '../api/siparis_api.php?action=teslimat_fis_detay&fis_id=' + fisId + '&_t=' + Date.now();

            try {
                const res = await fetch(url, { cache: 'no-store' });
                const data = await res.json();
                if (!data || data.length === 0) { alert('FiÅŸ detayÄ± bulunamadÄ±.'); return; }

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
        <title>Teslimat FiÅŸi - ${firmaAdi} (#${fisId})</title>
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
                <div class="subtitle">Teslimat FiÅŸi</div>
            </div>
            <div class="fis-no">
                <div class="no">#${fisId}</div>
                <div class="tarih">${tarihStr}</div>
            </div>
        </div>

        <div class="fis-info">
            <div class="col">
                <strong>MÃ¼ÅŸteri:</strong> ${firmaAdi}<br>
                <strong>Telefon:</strong> ${telefon}<br>
                <strong>Ä°skonto:</strong> <span class="highlight">% ${iskonto.toFixed(1)}</span>
            </div>
            <div class="col" style="text-align:right;">
                <strong>FiÅŸ No:</strong> #${fisId}<br>
                <strong>Tarih:</strong> ${tarihStr}<br>
                <strong>Kalem SayÄ±sÄ±:</strong> ${data.length}
            </div>
        </div>

        <table>
            <thead>
                <tr>
