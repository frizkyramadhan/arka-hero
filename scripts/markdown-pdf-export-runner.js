const path = require('path');
const { runTests } = require('@vscode/test-electron');

async function main() {
    const repoRoot = path.resolve(__dirname, '..');
    const extensionPath = path.join(__dirname, '.vscode-test', 'markdown-pdf-ext');
    const extensionTestsPath = path.join(__dirname, 'markdown-pdf-export-test.js');

    await runTests({
        extensionDevelopmentPath: extensionPath,
        extensionTestsPath,
        launchArgs: [repoRoot],
    });
}

main().catch((error) => {
    console.error(error);
    process.exit(1);
});
