const fs = require('fs');
let c = fs.readFileSync('admin/index.php', 'utf8');

// The regex will find all instances of the corrupted block:
// const blob = new Blob([printHTML], { ... a.click(); }
const rx = /const\s+blob\s*=\s*new\s*Blob\(\[printHTML\][\s\S]*?a\.click\(\);\s*\}/g;

const replacement = `const blob = new Blob([printHTML], { type: 'text/html; charset=utf-8' });
            const blobUrl = URL.createObjectURL(blob);
            const w = window.open(blobUrl, '_blank');
            if (w) {
                w.onload = () => { setTimeout(() => { w.print(); URL.revokeObjectURL(blobUrl); }, 300); };
            } else {
                const a = document.createElement('a'); a.href = blobUrl; a.target = '_blank'; a.click();
            }`;

const newContent = c.replace(rx, replacement);

if (c !== newContent) {
    fs.writeFileSync('admin/index.php', newContent);
    console.log('Replaced successfully');
} else {
    console.log('No matches found');
}
