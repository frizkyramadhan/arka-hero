const assert = require('assert');
const fs = require('fs');
const path = require('path');
const vscode = require('vscode');

const SOURCE = path.join(__dirname, '..', 'docs', 'SUPPLIES_USER_MANUAL.md');
const OUTPUT = path.join(__dirname, '..', 'docs', 'SUPPLIES_USER_MANUAL.pdf');

function waitForFile(filePath, timeoutMs = 120000) {
    const started = Date.now();

    return new Promise((resolve, reject) => {
        const tick = () => {
            if (fs.existsSync(filePath)) {
                resolve();
                return;
            }

            if (Date.now() - started > timeoutMs) {
                reject(new Error(`Timed out waiting for ${filePath}`));
                return;
            }

            setTimeout(tick, 500);
        };

        tick();
    });
}

async function run() {
    if (fs.existsSync(OUTPUT)) {
        fs.unlinkSync(OUTPUT);
    }

    const extension = vscode.extensions.getExtension('yzane.markdown-pdf');
    if (!extension) {
        const installed = vscode.extensions.all.map((item) => item.id).sort();
        throw new Error(
            'yzane.markdown-pdf extension not loaded. Installed: ' + installed.join(', '),
        );
    }

    await extension.activate();

    const document = await vscode.workspace.openTextDocument(SOURCE);
    await vscode.window.showTextDocument(document, { preview: false });

    await new Promise((resolve) => setTimeout(resolve, 1000));
    await vscode.commands.executeCommand('extension.markdown-pdf.pdf');
    await waitForFile(OUTPUT);

    const stats = fs.statSync(OUTPUT);
    assert.ok(stats.size > 1000, `PDF too small (${stats.size} bytes)`);
}

module.exports = { run };
