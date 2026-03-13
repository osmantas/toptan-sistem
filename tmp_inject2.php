<?php
// This script replaces loadRaporlar and fetchRaporData in admin/index.php

$file = 'c:\\xampp\\htdocs\\AKSA_TOPTAN\\admin\\index.php';
$content = file_get_contents($file);

// Check Chart.js is already included
if (strpos($content, 'chart.js') === false && strpos($content, 'Chart.js') === false) {
    // Add Chart.js CDN before </head>
    $content = str_replace('</head>', '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>' . "\n</head>", $content);
    echo "Chart.js CDN added.\n";
} else {
    echo "Chart.js already present.\n";
}

// The new loadRaporlar + fetchRaporData replacement
$newCode = <<<'JS'
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
            ['rprTarihBas','rprTarihSon'].forEach(id => {
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
            else if (preset === 'yesterday') { const d = new Date(today); d.setDate(d.getDate()-1); bas = fmt(d); son = fmt(d); }
            else if (preset === 'thisweek') { const d = new Date(today); d.setDate(d.getDate() - d.getDay() + (d.getDay()===0?-6:1)); bas = fmt(d); }
            else if (preset === 'thismonth') { bas = fmt(new Date(today.getFullYear(), today.getMonth(), 1)); }
            else if (preset === 'last7') { const d = new Date(today); d.setDate(d.getDate()-6); bas = fmt(d); }
            else if (preset === 'last30') { const d = new Date(today); d.setDate(d.getDate()-29); bas = fmt(d); }
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
                            labels.push(u.urun_kodu + ' ' + (u.urun_adi?.substring(0,18) || ''));
                            kgData.push(+kg.toFixed(2));
                            netData.push(+net.toFixed(2));
                            tableRows += `<tr>
                                <td style="color:var(--text-main)">${i+1}</td>
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
                                <td style="color:var(--text-main)">${i+1}</td>
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

            } catch(err) {
                console.error(err);
                if (tbody) tbody.innerHTML = '<tr><td colspan="7" class="rpr-no-data" style="color:var(--danger)">Hata oluştu: ' + err.message + '</td></tr>';
            }
        }

        function rprUpdateStats(kg, brut, net) {
            const iskonto = brut - net;
            const tl = currentUsdRate > 0 ? net * currentUsdRate : 0;
            const f = (n, prefix='') => n > 0 ? prefix + n.toFixed(2) : '—';
            document.getElementById('rprStat-kg').textContent = kg > 0 ? kg.toFixed(1) + ' KG' : '—';
            document.getElementById('rprStat-brut').textContent = '$ ' + f(brut);
            document.getElementById('rprStat-iskonto').textContent = '- $ ' + f(iskonto);
            document.getElementById('rprStat-net').textContent = '$ ' + f(net);
            document.getElementById('rprStat-tl').textContent = '₺ ' + f(tl);
        }

        function rprColors(n) {
            const palette = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#f97316','#84cc16','#ec4899','#14b8a6','#a855f7','#64748b'];
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
                    plugins: { ...chartDefaults.plugins, tooltip: { callbacks: { label: ctx => ` ${ctx.parsed[labels.length>6?'x':'y'].toFixed(1)} KG` } } },
                    scales: {
                        x: { ticks: { color: '#64748b', font:{size:10} }, grid: { color: 'rgba(255,255,255,.05)' } },
                        y: { ticks: { color: '#64748b', font:{size:10} }, grid: { color: 'rgba(255,255,255,.05)' } }
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
JS;

// Replace loadRaporlar and fetchRaporData
$startMarker = 'function loadRaporlar() {';
$endMarker = '        // Yardımcı Fonksiyon: Bildirim Gösterme';

$startPos = strpos($content, $startMarker);
$endPos = strpos($content, $endMarker);

if ($startPos === false || $endPos === false) {
    echo "ERROR: Could not find markers. startPos=" . var_export($startPos, true) . " endPos=" . var_export($endPos, true) . "\n";
    exit(1);
}

// Find the 8-space indent before the start marker
$realStart = $startPos;
// Go back to find the line start (up to 12 chars)
for ($i = 8; $i > 0; $i--) {
    if ($startPos >= $i && substr($content, $startPos - $i, $i) === str_repeat(' ', $i)) {
        $realStart = $startPos - $i;
        break;
    }
}

$before = substr($content, 0, $realStart);
$after = substr($content, $endPos);
$content = $before . $newCode . "\n\n        " . $after;

file_put_contents($file, $content);
echo "SUCCESS: loadRaporlar replaced with new modern version.\n";
?>