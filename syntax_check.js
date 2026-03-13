const fs = require('fs');
const content = fs.readFileSync('admin/index.php', 'utf8');
const scriptStart = content.indexOf('<script>');
const scriptEnd = content.indexOf('</script>', scriptStart);
const code = content.substring(scriptStart + 8, scriptEnd);

try {
    new (require('vm').Script)(code);
    console.log("Syntax OK for first script");
} catch (e) {
    console.error(e.message);
    // Delivery Management & Financial Details
    // - [x] Verify changes on simulated mobile displays.
    // - [x] Reorder columns in "Delivered Items" (Date between Product and Amount).
    // - [x] Add Price, Discount, and Total Amount columns.
    // - [x] Calculate and display Row Totals and Grand Totals.
    // - [x] Fix delivery accordion closing prematurely.
    // - [x] Fix partial delivery remaining amount bug (ID synchronization).
    // - [/] Add TL (Turkish Lira) conversion based on current exchange rate to the summary.
    console.error(e.stack);
}

const scriptStart2 = content.indexOf('<script>', scriptEnd);
if (scriptStart2 > -1) {
    const scriptEnd2 = content.lastIndexOf('</script>');
    const code2 = content.substring(scriptStart2 + 8, scriptEnd2);
    try {
        new (require('vm').Script)(code2);
        console.log("Syntax OK for second script");
    } catch (e) {
        console.error("Syntax Error in Second Script", e.stack.split('\n')[0]);
    }
}
