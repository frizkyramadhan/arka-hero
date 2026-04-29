/**
 * Captures screenshots for docs/user-manual/11-leave-management.md
 * Usage: BASE_URL=http://localhost/arka-hero LOGIN=admin@arka.co.id PASSWORD=admin node scripts/screenshot-leave-manual.mjs
 */
import { chromium } from 'playwright';
import { fileURLToPath } from 'url';
import path from 'path';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(__dirname, '..');
const outDir = path.join(root, 'docs', 'user-manual', 'images');

const BASE_URL = (process.env.BASE_URL || 'http://localhost/arka-hero').replace(/\/$/, '');
const LOGIN = process.env.LOGIN || 'admin@arka.co.id';
const PASSWORD = process.env.PASSWORD || 'admin';

async function login(page) {
    await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle', timeout: 60000 });
    await page.fill('input[name="login"]', LOGIN);
    await page.fill('input[name="password"]', PASSWORD);
    await page.click('button[type="submit"]');
    await page.waitForURL((url) => !url.pathname.includes('/login'), { timeout: 45000 });
    await page.waitForLoadState('networkidle').catch(() => {});
}

async function main() {
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({
        viewport: { width: 1440, height: 900 },
        locale: 'en-US',
    });
    const page = await context.newPage();

    console.log(`Logging in as ${LOGIN} at ${BASE_URL}...`);
    await login(page);

    if (page.url().includes('/login')) {
        console.error('Login failed — still on login page. Set LOGIN and PASSWORD env vars.');
        await browser.close();
        process.exit(1);
    }

    const shots = [];

    // 1. Leave Management Dashboard
    await page.goto(`${BASE_URL}/dashboard/leave-management`, {
        waitUntil: 'networkidle',
        timeout: 60000,
    });
    await page.waitForTimeout(800);
    await page.screenshot({
        path: path.join(outDir, 'leave_management_dashboard.png'),
        fullPage: true,
    });
    shots.push('leave_management_dashboard.png');

    // 2. Entitlements — project filter (before table load is OK; try load employees if project exists)
    await page.goto(`${BASE_URL}/leave/entitlements`, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForTimeout(600);
    const projectSelect = page.locator('#project_id');
    const optCount = await projectSelect.locator('option').count();
    if (optCount > 1) {
        await projectSelect.selectOption({ index: 1 });
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'networkidle', timeout: 60000 }).catch(() => {}),
            page.locator('button[type="submit"]:has-text("Load Employees")').click(),
        ]);
        await page.waitForTimeout(1000);
    }
    await page.screenshot({
        path: path.join(outDir, 'leave_entitlements_project_filter.png'),
        fullPage: false,
    });
    shots.push('leave_entitlements_project_filter.png');

    // 3. Entitlements — employee table (same page, often needs scroll)
    await page.screenshot({
        path: path.join(outDir, 'leave_entitlements_employee_table.png'),
        fullPage: true,
    });
    shots.push('leave_entitlements_employee_table.png');

    // 4. Leave Requests list
    await page.goto(`${BASE_URL}/leave/requests`, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForTimeout(1200);
    await page.screenshot({
        path: path.join(outDir, 'leave_requests_list.png'),
        fullPage: true,
    });
    shots.push('leave_requests_list.png');

    // 5. Create Leave Request — Flight + Approver column
    await page.goto(`${BASE_URL}/leave/requests/create`, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForTimeout(1500);
    const flightCard = page.locator('.flight-request-fields-wrapper').first();
    const approverCard = page.locator('.card').filter({ hasText: 'Approver Selection' }).first();
    if ((await flightCard.count()) > 0 && (await approverCard.count()) > 0) {
        await flightCard.scrollIntoViewIfNeeded();
        await page.waitForTimeout(300);
        const boxA = await flightCard.boundingBox();
        const boxB = await approverCard.boundingBox();
        if (boxA && boxB) {
            const pad = 16;
            const x = Math.min(boxA.x, boxB.x) - pad;
            const y = Math.min(boxA.y, boxB.y) - pad;
            const w = Math.max(boxA.x + boxA.width, boxB.x + boxB.width) - x + pad;
            const h = Math.max(boxA.y + boxA.height, boxB.y + boxB.height) - y + pad;
            await page.screenshot({
                path: path.join(outDir, 'leave_request_create_flight_approver.png'),
                clip: { x: Math.max(0, x), y: Math.max(0, y), width: Math.min(w, 1440), height: Math.min(h, 2000) },
            });
        } else {
            await page.screenshot({
                path: path.join(outDir, 'leave_request_create_flight_approver.png'),
                fullPage: true,
            });
        }
    } else {
        await page.screenshot({
            path: path.join(outDir, 'leave_request_create_flight_approver.png'),
            fullPage: true,
        });
    }
    shots.push('leave_request_create_flight_approver.png');

    // 6. Reports index
    await page.goto(`${BASE_URL}/leave/reports`, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForTimeout(600);
    await page.screenshot({
        path: path.join(outDir, 'leave_reports_index.png'),
        fullPage: true,
    });
    shots.push('leave_reports_index.png');

    // 7. Monitoring report
    await page.goto(`${BASE_URL}/leave/reports/monitoring`, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForTimeout(1200);
    await page.screenshot({
        path: path.join(outDir, 'leave_monitoring_report.png'),
        fullPage: true,
    });
    shots.push('leave_monitoring_report.png');

    // 8. My Leave Request (may redirect if no permission)
    await page.goto(`${BASE_URL}/leave/my-requests`, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForTimeout(1000);
    if (page.url().includes('/login')) {
        console.warn('Warning: /leave/my-requests redirected to login — skipping my_leave_request_list.png');
    } else {
        await page.screenshot({
            path: path.join(outDir, 'my_leave_request_list.png'),
            fullPage: true,
        });
        shots.push('my_leave_request_list.png');
    }

    await browser.close();

    console.log('Saved:', shots.map((f) => `docs/user-manual/images/${f}`).join('\n'));
}

main().catch((e) => {
    console.error(e);
    process.exit(1);
});
