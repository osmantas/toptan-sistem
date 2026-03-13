$excel = New-Object -ComObject Excel.Application
$excel.Visible = $false
$excel.DisplayAlerts = $false
$wb = $excel.Workbooks.Open("c:\xampp\htdocs\AKSA_TOPTAN\SONUC.xlsx")
$ws = $wb.Sheets.Item(1)
$rows = $ws.UsedRange.Rows.Count
$cols = $ws.UsedRange.Columns.Count
Write-Host "Rows: $rows, Cols: $cols"
for ($r = 1; $r -le [Math]::Min($rows, 20); $r++) {
    $line = ""
    for ($c = 1; $c -le $cols; $c++) {
        $val = $ws.Cells.Item($r, $c).Text
        $line += "$val`t"
    }
    Write-Host $line
}
# Save as CSV for PHP processing
$csvPath = "c:\xampp\htdocs\AKSA_TOPTAN\SONUC.csv"
$ws2 = $wb.Sheets
for ($s = 1; $s -le $ws2.Count; $s++) {
    $sheet = $ws2.Item($s)
    $sheet.SaveAs($csvPath, 6) # 6 = CSV
    break
}
$wb.Close($false)
$excel.Quit()
[System.Runtime.Interopservices.Marshal]::ReleaseComObject($excel) | Out-Null
Write-Host "CSV saved!"
