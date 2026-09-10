/**
 * Screenshot halaman modul Supplies untuk SUPPLIES_USER_MANUAL.md
 *
 * node docs/user-manual/scripts/capture-supplies-screenshots.mjs
 *
 * Opsional: ARKA_HERO_BASE, ARKA_HERO_USER, ARKA_HERO_PASS
 */
import { chromium } from 'playwright';
import { mkdir } from 'fs/promises';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const BASE = process.env.ARKA_HERO_BASE ?? 'http://localhost/arka-hero';
const OUT = join(__dirname, '..', '..', 'images', 'supplies');
const USER = process.env.ARKA_HERO_USER ?? 'admin';
const PASS = process.env.ARKA_HERO_PASS ?? 'admin';

const CONTENT_WRAPPER = 'div.content-wrapper';

const DETAIL_IDS = {
    stockIn: process.env.SUPPLY_STOCK_IN_ID ?? 'a2a5934c-098e-4d14-b68e-f123527f9116',
    stockOut: process.env.SUPPLY_STOCK_OUT_ID ?? 'a2a5934c-e4eb-4c2a-a6a8-ac0d9b8c4259',
    order: process.env.SUPPLY_ORDER_ID ?? 'a2a39c04-7c0b-4212-ab29-da0ea4cdb062',
};

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

async function shotContent(page, file) {
    const loc = page.locator(CONTENT_WRAPPER).first();
    await loc.waitFor({ state: 'visible', timeout: 20000 });
    await page.evaluate(() => window.scrollTo(0, 0));
    await page.waitForTimeout(300);
    await loc.screenshot({ path: join(OUT, file) });
    console.log('OK:', file);
}

async function gotoAndShot(page, path, file, waitMs = 2000) {
    await page.goto(`${BASE}${path}`, { waitUntil: 'networkidle', timeout: 45000 });
    await page.waitForTimeout(waitMs);
    await shotContent(page, file);
}

async function main() {
    await mkdir(OUT, { recursive: true });
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage({ viewport: { width: 1920, height: 1080 } });

    try {
        await login(page);

        await gotoAndShot(page, '/dashboard/supplies-management', 'supplies-dashboard.png', 3000);

        await page.goto(`${BASE}/supplies/catalog`, { waitUntil: 'networkidle', timeout: 45000 });
        await page.waitForTimeout(2000);
        const projectFilter = page.locator('#filter_project');
        if (await projectFilter.count()) {
            await projectFilter.selectOption({ index: 1 });
            await page.waitForTimeout(2500);
        }
        await shotContent(page, 'supplies-catalog.png');

        await gotoAndShot(page, '/supplies/stock-ins', 'supplies-stock-in-list.png', 2500);
        await gotoAndShot(
            page,
            `/supplies/stock-ins/${DETAIL_IDS.stockIn}`,
            'supplies-stock-in-detail.png',
            1500,
        );
        await gotoAndShot(page, '/supplies/stock-ins/create', 'supplies-stock-in-create.png', 1500);

        await gotoAndShot(page, '/supplies/stock-outs', 'supplies-stock-out-list.png', 2500);
        await gotoAndShot(
            page,
            `/supplies/stock-outs/${DETAIL_IDS.stockOut}`,
            'supplies-stock-out-detail.png',
            1500,
        );
        await gotoAndShot(page, '/supplies/stock-outs/create', 'supplies-stock-out-create.png', 1500);

        await gotoAndShot(page, '/supplies/orders', 'supplies-orders-list.png', 2500);
        await gotoAndShot(
            page,
            `/supplies/orders/${DETAIL_IDS.order}`,
            'supplies-order-detail.png',
            1500,
        );

        await gotoAndShot(page, '/supplies/orders/my-orders', 'supplies-my-orders.png', 2500);
        await gotoAndShot(page, '/supplies/reports', 'supplies-reports.png', 1500);
        await gotoAndShot(page, '/supplies/reports/stock-card', 'supplies-stock-card-report.png', 2500);
        await gotoAndShot(page, '/supplies/item-categories', 'supplies-item-categories.png', 2500);
    } finally {
        await browser.close();
    }
}

main().catch((e) => {
    console.error(e);
    process.exit(1);
});
