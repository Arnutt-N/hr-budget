import { test, expect, type Page } from '@playwright/test';

/**
 * Phase 2 — admin fiscal-years CRUD via PrimeVue DataTable + Dialog.
 *
 * Covers: list render, create (unique year), edit, zod validation error,
 * delete with ConfirmDialog accept. PrimeVue Dialog/ConfirmDialog render in
 * a portal — selectors are scoped via getByRole('dialog' | 'alertdialog').
 */

const ADMIN_EMAIL = 'admin@moj.go.th';
const ADMIN_PASSWORD = 'admin123';

// Unique-per-run year inside the server-accepted range (2400-2700),
// far above real data (2566-2570) to avoid collisions
const TEST_YEAR = 2650 + Math.floor(Math.random() * 49);

async function loginAsAdmin(page: Page): Promise<void> {
    await page.goto('/login');
    await page.fill('input[name="email"]', ADMIN_EMAIL);
    await page.fill('input[name="password"]', ADMIN_PASSWORD);
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard');
}

test.describe.serial('Admin fiscal years CRUD', () => {
    test('list page renders DataTable with existing data', async ({ page }) => {
        await loginAsAdmin(page);
        await page.goto('/fiscal-years');

        await expect(page.getByRole('heading', { name: 'ปีงบประมาณ', exact: true })).toBeVisible();
        // Column headers from the DataTable
        await expect(page.getByRole('columnheader', { name: 'ปีงบประมาณ' })).toBeVisible();
        await expect(page.getByRole('columnheader', { name: 'สถานะ' })).toBeVisible();
    });

    test('submitting an empty form shows Thai zod errors', async ({ page }) => {
        await loginAsAdmin(page);
        await page.goto('/fiscal-years');

        await page.getByRole('button', { name: 'เพิ่มปีงบประมาณ' }).click();
        const dialog = page.getByRole('dialog');
        await expect(dialog).toBeVisible();

        // Clear prefilled year then submit empty
        await dialog.locator('#fy-year').click();
        await dialog.locator('#fy-year input, input#fy-year').first().fill('');
        await dialog.getByRole('button', { name: 'บันทึก' }).click();

        await expect(dialog.getByText('กรุณาเลือกวันเริ่มต้น')).toBeVisible();
        await expect(dialog.getByText('กรุณาเลือกวันสิ้นสุด')).toBeVisible();

        // Close without saving
        await dialog.getByRole('button', { name: 'ยกเลิก' }).click();
        await expect(dialog).toBeHidden();
    });

    test('create a new fiscal year shows toast and row appears', async ({ page }) => {
        await loginAsAdmin(page);
        await page.goto('/fiscal-years');

        await page.getByRole('button', { name: 'เพิ่มปีงบประมาณ' }).click();
        const dialog = page.getByRole('dialog');
        await expect(dialog).toBeVisible();

        await dialog.locator('#fy-year input, input#fy-year').first().fill(String(TEST_YEAR));
        await dialog.locator('#fy-start').fill(`${TEST_YEAR - 544}-10-01`);
        await dialog.locator('#fy-end').fill(`${TEST_YEAR - 543}-09-30`);
        await dialog.getByRole('button', { name: 'บันทึก' }).click();

        await expect(page.getByText('เพิ่มปีงบประมาณสำเร็จ')).toBeVisible();
        await expect(page.getByRole('cell', { name: String(TEST_YEAR), exact: true })).toBeVisible();
    });

    test('edit the created year shows success toast', async ({ page }) => {
        await loginAsAdmin(page);
        await page.goto('/fiscal-years');

        const row = page.getByRole('row', { name: new RegExp(String(TEST_YEAR)) });
        await row.getByRole('button', { name: 'แก้ไข' }).click();

        const dialog = page.getByRole('dialog');
        await expect(dialog).toBeVisible();
        await dialog.locator('#fy-end').fill(`${TEST_YEAR - 543}-09-29`);
        await dialog.getByRole('button', { name: 'บันทึก' }).click();

        await expect(page.getByText('แก้ไขปีงบประมาณสำเร็จ')).toBeVisible();
    });

    test('delete with ConfirmDialog accept removes the row', async ({ page }) => {
        await loginAsAdmin(page);
        await page.goto('/fiscal-years');

        const row = page.getByRole('row', { name: new RegExp(String(TEST_YEAR)) });
        await row.getByRole('button', { name: 'ลบ' }).click();

        // ConfirmDialog renders as alertdialog in a portal
        const confirmDialog = page.getByRole('alertdialog');
        await expect(confirmDialog).toBeVisible();
        await confirmDialog.getByRole('button', { name: 'ลบ' }).click();

        await expect(page.getByText('ลบปีงบประมาณสำเร็จ')).toBeVisible();
        await expect(page.getByRole('cell', { name: String(TEST_YEAR), exact: true })).toBeHidden();
    });
});
