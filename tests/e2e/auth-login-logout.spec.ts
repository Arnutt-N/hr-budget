import { test, expect, type Page } from '@playwright/test';

/**
 * Phase 1 — cookie-based auth flow (SPA on :5174, API proxied to PHP).
 *
 * Also covers the middleware rejection paths that PHPUnit cannot assert
 * in-process (ApiResponse exits): 401 without a session, 403 CSRF on a
 * cookie-authed mutation missing X-Requested-With.
 */

const ADMIN_EMAIL = 'admin@moj.go.th';
const ADMIN_PASSWORD = 'admin123';

async function loginAsAdmin(page: Page): Promise<void> {
    await page.goto('/login');
    await page.fill('input[name="email"]', ADMIN_EMAIL);
    await page.fill('input[name="password"]', ADMIN_PASSWORD);
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard');
}

test.describe('Cookie auth', () => {
    test('login lands on dashboard and survives a page reload', async ({ page }) => {
        await loginAsAdmin(page);
        await expect(page).toHaveURL(/\/dashboard/);

        // httpOnly cookie session: refresh must rehydrate via /auth/me
        await page.reload();
        await expect(page).toHaveURL(/\/dashboard/);

        // Token must NOT be readable from JS anymore
        const localStorageToken = await page.evaluate(() => localStorage.getItem('hr_budget_token'));
        expect(localStorageToken).toBeNull();
        const documentCookie = await page.evaluate(() => document.cookie);
        expect(documentCookie).not.toContain('hr_budget_token');
    });

    test('logout clears the session server-side', async ({ page }) => {
        await loginAsAdmin(page);

        await page.click('text=ออกจากระบบ');
        await page.waitForURL('**/login');

        // Session is gone: API rejects, guard redirects
        const me = await page.request.get('/api/v1/auth/me');
        expect(me.status()).toBe(401);

        await page.goto('/dashboard');
        await expect(page).toHaveURL(/\/login/);
    });

    test('guard redirects logged-out visitor with redirect query', async ({ page }) => {
        await page.goto('/fiscal-years');
        await expect(page).toHaveURL(/\/login\?redirect=/);
    });

    test('invalid login shows Thai error and stays on login', async ({ page }) => {
        await page.goto('/login');
        await page.fill('input[name="email"]', ADMIN_EMAIL);
        await page.fill('input[name="password"]', 'wrong-password');
        await page.click('button[type="submit"]');

        await expect(page.getByText('อีเมลหรือรหัสผ่านไม่ถูกต้อง')).toBeVisible();
        await expect(page).toHaveURL(/\/login/);
    });

    test('client-side zod validation blocks malformed email', async ({ page }) => {
        await page.goto('/login');
        await page.fill('input[name="email"]', 'not-an-email');
        await page.fill('input[name="password"]', 'x');
        await page.click('button[type="submit"]');

        await expect(page.getByText('รูปแบบอีเมลไม่ถูกต้อง')).toBeVisible();
    });

    test('API returns 401 without any session', async ({ request }) => {
        const res = await request.get('/api/v1/auth/me');
        expect(res.status()).toBe(401);
    });

    test('cookie-authed mutation without CSRF header is rejected with 403', async ({ page }) => {
        await loginAsAdmin(page);

        // page.request reuses the browser cookies but does NOT send
        // X-Requested-With — exactly the cross-site forgery shape.
        const forged = await page.request.post('/api/v1/organizations', {
            data: { name: 'csrf-probe' },
        });
        expect(forged.status()).toBe(403);

        // Same request WITH the header passes the CSRF gate
        // (422/201 both prove we got past the middleware).
        const legit = await page.request.post('/api/v1/organizations', {
            data: {},
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        expect([201, 422]).toContain(legit.status());
    });
});
