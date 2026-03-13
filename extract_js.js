const fs = require('fs');
const content = fs.readFileSync('admin/index.php', 'utf8');
let inScript = false;
const lines = content.split('\n');
for (let i = 0; i < lines.length; i++) {
    let line = lines[i];
    let originalLine = line;

    // We want to keep line empty if outside script, but we must handle <script> boundaries.
    // If a line has both <script> and </script> (inline script), we just leave it for simplicity or clear it. 
    // Usually scripts are multi-line.
    if (line.includes('</script>')) {
        inScript = false;
        lines[i] = ''; // blanking the closing tag line
    }

    if (!inScript && !originalLine.includes('<script')) {
        lines[i] = '';
    }

    if (originalLine.includes('<script')) {
        inScript = true;
        lines[i] = ''; // blanking the opening tag line
    }
}

fs.writeFileSync('exact_lines.js', lines.join('\n'));
