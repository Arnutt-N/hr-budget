// Local Laragon smoke test — login + key pages, screenshots as evidence.
// Uses the PHP-served subdirectory URL (not the dev server), so paths include /hr_budget/public.
import { chromium } from 'playwright';

const BASE = 'http://localhost/hr_budget/public/app';
const shots = 'test-results/local-smoke';
import { mkdirSync } from 'node:fs';
mkdirSync(shots, { recursive: true });

const browser = await chromium.launch();
const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
page.on('response', async (r) => {
    if (r.url().includes('/api/')) {
        let body = '';
        try { body = (await r.text()).slice(0, 200); } catch {}
        console.log(`[api] ${r.request().method()} ${r.url()} -> ${r.status()} ${body}`);
    }
});
page.on('console', (m) => { if (m.type() === 'error') console.log(`[console.error] ${m.text().slice(0, 200)}`); });
const results = [];

async function shot(name) {
    await page.screenshot({ path: `${shots}/${name}.png` });
}

// 1. Root redirects to login
await page.goto(`${BASE}/`, { waitUntil: 'domcontentloaded' });
await page.waitForURL(/\/login/, { timeout: 10000 });
await page.waitForTimeout(800);
results.push(['root redirects to /login', page.url()]);
await shot('01-login-page');

// 2. Login as admin
await page.fill('input[name="email"]', 'admin@moj.go.th');
await page.fill('input[name="password"]', 'admin123');
await page.click('button[type="submit"]');
await page.waitForURL(/\/dashboard/, { timeout: 15000 });
await page.waitForTimeout(2500); // charts/data settle
results.push(['login lands on dashboard', page.url()]);
await shot('02-dashboard');

// 3. Budget requests list
await page.goto(`${BASE}/requests`, { waitUntil: 'domcontentloaded' });
await page.waitForTimeout(2000);
results.push(['requests page', page.url()]);
await shot('03-requests');

// 4. Fiscal years admin
await page.goto(`${BASE}/fiscal-years`, { waitUntil: 'domcontentloaded' });
await page.waitForTimeout(2000);
results.push(['fiscal-years page', page.url()]);
await shot('04-fiscal-years');

// 5. Reload dashboard (session survives via httpOnly cookie)
await page.goto(`${BASE}/dashboard`, { waitUntil: 'domcontentloaded' });
await page.waitForTimeout(1500);
const stillDash = page.url().includes('/dashboard');
results.push(['session survives reload', stillDash ? 'OK' : `REDIRECTED: ${page.url()}`]);

// 6. Auth state via API from the page context
const me = await page.evaluate(async () => {
    const r = await fetch('/hr_budget/public/api/v1/auth/me', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'include',
    });
    return { status: r.status, body: await r.json() };
});
results.push(['api /auth/me', `${me.status} ${JSON.stringify(me.body?.data?.user?.email ?? me.body?.data?.email ?? '')}`]);

console.log('\n=== LOCAL SMOKE RESULTS ===');
for (const [k, v] of results) console.log(`${k}: ${v}`);

await browser.close();
