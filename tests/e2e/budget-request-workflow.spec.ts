import { test, expect, type Page } from '@playwright/test';

/**
 * Phase 4 — Budget Request workflow on the Vue SPA (TanStack-backed pages).
 *
 * Drives the full lifecycle as the admin (who is both owner and approver — the
 * backend allows self-approval): create with an item → save & submit → approve,
 * plus a reject-with-note path. Replaces the legacy specs that targeted the old
 * PHP `/requests/dashboard` view.
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

/** Fill the create form with one item, then click "ส่งอนุมัติ" (save & submit). */
async function createAndSubmit(page: Page, title: string): Promise<void> {
    await page.goto('/requests/create');
    await page.getByPlaceholder(/เช่น คำของบประมาณ/).fill(title);
    await page.getByPlaceholder('ชื่อรายการ').fill('ค่าวัสดุสำนักงาน');
    const numbers = page.locator('input[type="number"]');
    await numbers.nth(0).fill('2');   // quantity
    await numbers.nth(1).fill('500'); // unit price
    await page.getByRole('button', { name: 'ส่งอนุมัติ' }).click();
    await page.waitForURL(/\/requests\/\d+$/);
}

test.describe('Budget request workflow (SPA)', () => {
    test('create → submit → approve', async ({ page }) => {
        await loginAsAdmin(page);
        const title = `คำขอทดสอบอนุมัติ ${Date.now()}`;

        await createAndSubmit(page, title);

        // Landed on the detail page as "รออนุมัติ" (pending).
        await expect(page.getByRole('heading', { name: 'รายละเอียดคำขอ' })).toBeVisible();
        await expect(page.getByText('รออนุมัติ', { exact: true })).toBeVisible();

        // Admin approves → status becomes "อนุมัติแล้ว".
        await page.getByRole('button', { name: 'อนุมัติ', exact: true }).click();
        await expect(page.getByText('อนุมัติแล้ว', { exact: true })).toBeVisible();
    });

    test('create → submit → reject with note', async ({ page }) => {
        await loginAsAdmin(page);
        const title = `คำขอทดสอบปฏิเสธ ${Date.now()}`;
        const reason = 'งบประมาณไม่เพียงพอ';

        await createAndSubmit(page, title);
        await expect(page.getByText('รออนุมัติ', { exact: true })).toBeVisible();

        // Open the reject form, enter a required note, confirm.
        await page.getByRole('button', { name: 'ปฏิเสธ', exact: true }).click();
        await page.getByPlaceholder('ระบุเหตุผล...').fill(reason);
        await page.getByRole('button', { name: 'ยืนยันปฏิเสธ' }).click();

        // Rejection reason surfaces on the detail page.
        await expect(page.getByText('เหตุผลการปฏิเสธ:')).toBeVisible();
        await expect(page.getByText(reason)).toBeVisible();
    });
});
