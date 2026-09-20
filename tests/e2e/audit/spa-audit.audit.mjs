// SPA design-audit live pass — screenshots, axe, real-render contrast,
// 360px overflow and sampled focus checks for all 31 routes.
//
// Run (Windows, repo root):
//   Terminal 1:  cd frontend ; npm run dev          # or: npx vite preview --port 5174
//   Terminal 2:  npm run test:e2e:audit              # mock API, zero backend needed
// Live backend instead of mocks:
//   $env:AUDIT_MODE='live'; npm run test:e2e:audit  # logs in per route (admin)
//
// Env: BASE_URL = SPA root (default http://localhost:5174).
//   For the deploy build: BASE_URL=http://<host>/hr_budget/public/app
//   AUDIT_MODE=mock|live (default mock)
//   AUDIT_ADMIN_EMAIL / AUDIT_ADMIN_PASSWORD (live mode; default admin@moj.go.th/admin123)
// Output: test-results/audit/{shots/*.png, *.json, summary.json}
// NOTE: `.audit.mjs` (not `.spec.mjs`) so the root config's default testMatch
// never collects this 31-route sweep — it runs only through the dedicated
// `playwright.audit.config.mjs` (`npm run test:e2e:audit`, CI `audit` job).
// `.mjs` also passes plain `node --check`.
import { test, expect } from '@playwright/test';
import { mkdirSync, writeFileSync, readFileSync, readdirSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
import { installMockRoutes } from './mockApi.mjs';
import { resolveAxe, axeViolations, auditContrast, auditFocus, overflowX } from './checks.mjs';

test.describe.configure({ mode: 'serial' });

const HERE = dirname(fileURLToPath(import.meta.url));
const OUT = join(HERE, '..', '..', '..', 'test-results', 'audit');
const SHOTS = join(OUT, 'shots');
mkdirSync(SHOTS, { recursive: true });

const MODE = process.env.AUDIT_MODE || 'mock';
const ADMIN_EMAIL = process.env.AUDIT_ADMIN_EMAIL || 'admin@moj.go.th';
const ADMIN_PASSWORD = process.env.AUDIT_ADMIN_PASSWORD || 'admin123';

const ROUTES = ['/login', '/dashboard', '/requests', '/requests/1', '/requests/create', '/requests/1/edit',
  '/disbursements', '/disbursements/wizard', '/budget-execution', '/analytics', '/notifications', '/vault',
  '/fiscal-years', '/organizations', '/categories', '/users', '/users/1/access-grants', '/roles', '/divisions',
  '/plans', '/target-types', '/targets', '/positions', '/allowance-types', '/salary-scales',
  '/salary-raise-rounds', '/personnel-budget-policies', '/vacancy-recruitment', '/personnel-allowances',
  '/personnel-assignments', '/compute-budget'];
const FOCUS_ROUTES = ['/login', '/dashboard', '/requests/create', '/users'];

const slug = (r) => r.replace(/\//g, '-').replace(/^-/, '') || 'root';
let AXE = null;
test.beforeAll(() => { AXE = resolveAxe(); });

async function ensureLogin(page) {
  await page.goto('/login', { waitUntil: 'domcontentloaded' });
  if (new URL(page.url()).pathname.endsWith('/dashboard')) return;
  await page.fill('input[name="email"]', ADMIN_EMAIL);
  await page.fill('input[name="password"]', ADMIN_PASSWORD);
  await page.click('button[type="submit"]');
  await page.waitForURL('**/dashboard', { timeout: 15000 });
}

for (const route of ROUTES) {
  test(`audit ${route}`, async ({ page }) => {
    test.setTimeout(60000);
    const errors = [];
    page.on('pageerror', (e) => errors.push('PAGEERROR: ' + String(e).slice(0, 200)));
    page.on('console', (m) => { if (m.type() === 'error') errors.push('CONSOLE: ' + m.text().slice(0, 200)); });
    if (MODE === 'mock') {
      await installMockRoutes(page, route !== '/login');
    } else if (route !== '/login') {
      await ensureLogin(page);
    }
    await page.goto(route, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(1500);
    const out = { route, mode: MODE, axe: [], contrast: [], focus: null, overflow360: null, errors: [] };
    await page.screenshot({ path: join(SHOTS, `${slug(route)}-desktop.png`) });
    out.axe = await axeViolations(page, AXE);
    out.contrast = await auditContrast(page);
    if (FOCUS_ROUTES.includes(route)) out.focus = await auditFocus(page);
    await page.setViewportSize({ width: 360, height: 800 });
    await page.waitForTimeout(500);
    out.overflow360 = await overflowX(page);
    await page.screenshot({ path: join(SHOTS, `${slug(route)}-360.png`) });
    out.errors = [...new Set(errors)].slice(0, 8);
    const jsonPath = join(OUT, `${slug(route)}.json`);
    writeFileSync(jsonPath, JSON.stringify(out, null, 1));
    await test.info().attach(`${slug(route)}.json`, { path: jsonPath });
    console.log(`${route}: errors=${out.errors.length} axe=${out.axe.length} contrast=${out.contrast.length} overflow360=${out.overflow360}`);
    for (const e of out.errors) console.log(`    ${e}`);
    const blocking = out.axe.filter((v) => v.impact === 'serious' || v.impact === 'critical');
    expect.soft(blocking, `axe serious/critical on ${route}`).toEqual([]);
  });
}

// Runs last (serial): totals across per-route JSONs. Gates (soft, so one
// log line lists every failing route instead of stopping at the first):
// axe serious/critical (per-route test above), contrast, 360px overflow and
// pageerrors (real crashes — fixed via mock shapes in mockApi.mjs).
// Raw console/401 lines stay informational: the /login 401 is the router
// guard's by-design logged-out /auth/me probe (see auth.ts bootstrap), and
// live-mode runs add real-backend noise the mock run cannot gate on.
test('audit summary', async () => {
  const files = readdirSync(OUT).filter((f) => f.endsWith('.json') && f !== 'summary.json');
  const axeTotals = {};
  let contrastN = 0, errN = 0, pageErrN = 0;
  const overflow = [];
  const pageErrRoutes = [];
  for (const f of files) {
    const r = JSON.parse(readFileSync(join(OUT, f), 'utf8'));
    for (const v of r.axe) axeTotals[v.id] = (axeTotals[v.id] || 0) + v.nodes;
    contrastN += r.contrast.length;
    errN += r.errors.length;
    const pe = r.errors.filter((e) => String(e).startsWith('PAGEERROR'));
    pageErrN += pe.length;
    if (pe.length) pageErrRoutes.push(r.route);
    if (r.overflow360 > 0) overflow.push(`${r.route} (+${r.overflow360}px)`);
  }
  const summary = { routes: files.length, mode: MODE, axeTotals, contrastFails: contrastN, errorLines: errN, pageerrors: pageErrN, overflow360: overflow };
  writeFileSync(join(OUT, 'summary.json'), JSON.stringify(summary, null, 1));
  console.log(`AUDIT SUMMARY (${files.length} routes, ${MODE}): axe=${JSON.stringify(axeTotals)} contrast=${contrastN} errors=${errN} pageerrors=${pageErrN}`);
  console.log(`overflow360: ${overflow.length ? overflow.join(', ') : 'none'}`);
  if (pageErrRoutes.length) console.log(`pageerror routes: ${pageErrRoutes.join(', ')}`);
  expect.soft(contrastN, 'total contrast failures').toEqual(0);
  expect.soft(overflow, 'routes with 360px overflow').toEqual([]);
  expect.soft(pageErrN, `pageerrors on ${pageErrRoutes.join(', ')}`).toEqual(0);
});
