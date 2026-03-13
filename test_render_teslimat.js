const fs = require('fs');

async function testFetch() {
    try {
        const res = await fetch('http://localhost/AKSA_TOPTAN/api/siparis_api.php?action=teslim_edilenler');
        const data = await res.json();

        console.log("Data count:", data.length);

        let grouped = {};
        data.forEach(d => {
            const key = d.musteri_id;
            if (!grouped[key]) {
                grouped[key] = { firma_adi: d.firma_adi, telefon: d.telefon || '', musteri_id: d.musteri_id, items: [] };
            }
            grouped[key].items.push(d);
        });

        console.log("Grouped keys:", Object.keys(grouped).length);

        let html = '';
        Object.values(grouped).forEach((g, idx) => {
            const toplamKg = g.items.reduce((sum, i) => sum + parseFloat(i.teslim_edilen_kg || 0), 0);
            html += `Group ${idx}: ${g.firma_adi} - ${toplamKg} KG\n`;

            g.items.forEach(item => {
                const tarih = item.tarih ? new Date(item.tarih).toLocaleDateString('tr-TR') : '-';
                html += `  - ${item.urun_kodu} (${item.urun_adi}) - ${parseFloat(item.teslim_edilen_kg).toFixed(1)} KG\n`;
            });
        });

        console.log("HTML Generation Success!");
        console.log(html);

    } catch (e) {
        console.error("ERROR:", e);
    }
}

testFetch();
