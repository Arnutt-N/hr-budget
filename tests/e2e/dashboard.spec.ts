import { test, expect, type Page } from '@playwright/test';

/**
 * Phase 3 — Dashboard: stat cards + monthly expenditure chart, fed by the new
 * JWT-authed /api/v1/dashboard/* endpoints.
 *
 * The seeded test DB has budget_trackings for FY 2569 (non-zero totals) and
 * expenditure rows inside the FY-2569 window (Oct 2025 – Sep 2026), so both the
 * stat cards and the chart canvas render.
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

test.describe('Dashboard', () => {
    test('renders the single Dashboard heading, stat cards, and the monthly chart', async ({ page }) => {
        await loginAsAdmin(page);

        // The one page heading (its accessible name contains "Dashboard").
        await expect(page.getByRole('heading', { name: /Dashboard/i })).toBeVisible();

        // Stat cards from the summary endpoint.
        await expect(page.getByText('งบประมาณทั้งหมด')).toBeVisible();
        await expect(page.getByText('เบิกจ่ายแล้ว')).toBeVisible();
        await expect(page.getByText('อัตราการใช้จ่าย')).toBeVisible();

        // Chart section + rendered <canvas> (seed has CE-2026 expenditure data).
        await expect(page.getByRole('heading', { name: 'เบิกจ่ายรายเดือน' })).toBeVisible();
        await expect(page.locator('canvas')).toBeVisible({ timeout: 10_000 });
    });
});
