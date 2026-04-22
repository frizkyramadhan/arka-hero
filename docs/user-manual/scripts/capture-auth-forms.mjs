/**
 * Screenshot hanya kartu form Register / Login (crop elemen).
 * node docs/user-manual/scripts/capture-auth-forms.mjs
 */
import { chromium } from 'playwright';
import { mkdir } from 'fs/promises';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const BASE = process.env.ARKA_HERO_BASE ?? 'http://localhost/arka-hero';
const OUT = join(__dirname, '..', 'images');

async function main() {
  await mkdir(OUT, { recursive: true });
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    await page.goto(`${BASE}/register`, { waitUntil: 'domcontentloaded', timeout: 30000 });
    const regCard = page.locator('.register-box .card').first();
    await regCard.waitFor({ state: 'visible', timeout: 10000 });
    await regCard.screenshot({ path: join(OUT, 'register.png') });

    await page.goto(`${BASE}/login`, { waitUntil: 'domcontentloaded', timeout: 30000 });
    const loginCard = page.locator('.login-box .card').first();
    await loginCard.waitFor({ state: 'visible', timeout: 10000 });
    await loginCard.screenshot({ path: join(OUT, 'login.png') });

    console.log('OK: images/register.png, images/login.png');
  } finally {
    await browser.close();
  }
}

main().catch((e) => {
  console.error(e);
  process.exit(1);
});
