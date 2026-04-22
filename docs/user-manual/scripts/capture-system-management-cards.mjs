/**
 * Screenshot area konten utama penuh (tanpa crop parsial) — seluruh kolom di samping sidebar:
 * judul halaman, breadcrumb, dan isi (section.content).
 *
 * node docs/user-manual/scripts/capture-system-management-cards.mjs
 *
 * Opsional: USER_SCREENSHOT_ID (default 1), ARKA_HERO_BASE, ARKA_HERO_USER, ARKA_HERO_PASS
 */
import { chromium } from 'playwright';
import { mkdir } from 'fs/promises';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const BASE = process.env.ARKA_HERO_BASE ?? 'http://localhost/arka-hero';
const OUT = join(__dirname, '..', 'images');
const USER = process.env.ARKA_HERO_USER ?? 'admin';
const PASS = process.env.ARKA_HERO_PASS ?? 'admin';
const USER_DETAIL_ID = process.env.USER_SCREENSHOT_ID ?? '1';

/** Seluruh kolom konten AdminLTE (bukan sidebar) — menghindari crop col-md-8 / satu kartu saja */
const CONTENT_WRAPPER = 'div.content-wrapper';

async function login(page) {
  await page.goto(`${BASE}/login`, { waitUntil: 'domcontentloaded', timeout: 30000 });
  await page.fill('input[name="login"]', USER);
  await page.fill('input[name="password"]', PASS);
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle', timeout: 30000 }).catch(() => {}),
    page.click('button[type="submit"]'),
  ]);
  await page.waitForTimeout(1200);
  if (page.url().includes('/login')) {
    throw new Error('Login gagal — periksa kredensial dan server.');
  }
}

/**
 * Tampilkan semua baris DataTables jika ada (lengthMenu memuat -1 = All).
 */
async function dataTableShowAll(page, tableSelector) {
  await page.waitForTimeout(1500);
  const ok = await page.evaluate((sel) => {
    if (typeof window.jQuery === 'undefined') return false;
    const $el = window.jQuery(sel);
    if (!$el.length || !window.jQuery.fn.DataTable) return false;
    if (!window.jQuery.fn.DataTable.isDataTable(sel)) return false;
    const api = $el.DataTable();
    const settings = api.settings()[0];
    const menu = settings?.aLengthMenu;
    const canAll = Array.isArray(menu) && menu.some((m) => m === -1 || m === '-1');
    if (canAll) {
      api.page.len(-1).draw(false);
      return true;
    }
    return false;
  }, tableSelector);
  if (ok) {
    await page.waitForTimeout(800);
  }
}

async function shotContentWrapper(page, file) {
  const loc = page.locator(CONTENT_WRAPPER).first();
  await loc.waitFor({ state: 'visible', timeout: 20000 });
  await page.evaluate(() => window.scrollTo(0, 0));
  await page.waitForTimeout(200);
  await loc.screenshot({ path: join(OUT, file) });
  console.log('OK:', file);
}

async function main() {
  await mkdir(OUT, { recursive: true });
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 1920, height: 1080 } });

  try {
    await login(page);

    await page.goto(`${BASE}/users`, { waitUntil: 'networkidle', timeout: 45000 });
    await page.waitForTimeout(2500);
    await dataTableShowAll(page, '#example1');
    await shotContentWrapper(page, 'users-management.png');

    await page.goto(`${BASE}/users/create`, { waitUntil: 'networkidle', timeout: 45000 });
    await page.waitForTimeout(800);
    await shotContentWrapper(page, 'users-create.png');

    await page.goto(`${BASE}/users/${USER_DETAIL_ID}`, { waitUntil: 'networkidle', timeout: 45000 });
    await page.waitForTimeout(800);
    await shotContentWrapper(page, 'users-show.png');

    await page.goto(`${BASE}/users/${USER_DETAIL_ID}/edit`, { waitUntil: 'networkidle', timeout: 45000 });
    await page.waitForTimeout(500);
    const statusField = page.locator('#user_status, select[name="user_status"]').first();
    if (await statusField.count()) {
      await statusField.scrollIntoViewIfNeeded();
    }
    await page.waitForTimeout(400);
    await shotContentWrapper(page, 'users-edit.png');

    await page.goto(`${BASE}/roles`, { waitUntil: 'networkidle', timeout: 45000 });
    await page.waitForTimeout(2500);
    await dataTableShowAll(page, '#roles-datatable');
    await shotContentWrapper(page, 'roles-management.png');

    await page.goto(`${BASE}/roles/create`, { waitUntil: 'networkidle', timeout: 45000 });
    await page.waitForTimeout(800);
    await shotContentWrapper(page, 'roles-create.png');

    let roleEditPath = '/roles/2/edit';
    await page.goto(`${BASE}/roles`, { waitUntil: 'networkidle', timeout: 45000 });
    await page.waitForTimeout(2000);
    const editLink = page.locator('table a[href*="/edit"]').first();
    if (await editLink.count()) {
      const href = await editLink.getAttribute('href');
      if (href) {
        const m = href.match(/roles\/(\d+)\/edit/);
        if (m) roleEditPath = `/roles/${m[1]}/edit`;
      }
    }
    await page.goto(`${BASE}${roleEditPath}`, { waitUntil: 'networkidle', timeout: 45000 });
    await page.waitForTimeout(800);
    await shotContentWrapper(page, 'roles-edit.png');

    await page.goto(`${BASE}/permissions`, { waitUntil: 'networkidle', timeout: 45000 });
    await page.waitForTimeout(2500);
    await dataTableShowAll(page, '#permissions-table');
    await shotContentWrapper(page, 'permissions-management.png');
  } finally {
    await browser.close();
  }
}

main().catch((e) => {
  console.error(e);
  process.exit(1);
});
