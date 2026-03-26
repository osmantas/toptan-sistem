<?php
header('Content-Type: application/json; charset=utf-8');

function getHaremAltinKur()
{
    $url = 'https://haremaltin.com/dashboard/ajax/doviz';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
    // Harem altin ajax endpoint bazen referer falan kontrol edebilir
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-Requested-With: XMLHttpRequest',
        'Accept: application/json, text/javascript, */*; q=0.01'
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200 && $response) {
        $data = json_decode($response, true);
        if (isset($data['data']['USDTRY']['satis'])) {
            return (float) $data['data']['USDTRY']['satis'];
        }
    }

    return 0;
}

$kur = getHaremAltinKur();

if ($kur == 0) {
    // Sabit veya TCMB fallback eklenebilir. Şimdilik exchangerate-api fallback kullanıyoruz.
    $ch2 = curl_init('https://api.exchangerate-api.com/v4/latest/USD');
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
    $response2 = curl_exec($ch2);
    curl_close($ch2);
    if ($response2) {
        $data2 = json_decode($response2, true);
        if (isset($data2['rates']['TRY'])) {
            $kur = (float) $data2['rates']['TRY'];
        } else {
            $kur = 34.50;
        }
    } else {
        $kur = 34.50;
    }
}

echo json_encode(['status' => 'success', 'usd_kuru' => $kur]);
?>