import { test, expect, type Page } from '@playwright/test';

/** Phase 2 — admin plans (แผนงาน/ผลผลิต) CRUD (DataTable + Dialog). */

const ADMIN_EMAIL = 'admin@moj.go.th';
const ADMIN_PASSWORD = 'admin123';

const STAMP = Date.now().toString(36).slice(-6);
const TEST_CODE = `E2E-${STAMP}`;
const TEST_NAME = `แผนทดสอบ ${STAMP}`;

async function loginAsAdmin(page: Page): Promise<void> {
    await page.goto('/login');
    await page.fill('input[name="email"]', ADMIN_EMAIL);
    await page.fill('input[name="password"]', ADMIN_PASSWORD);
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard');
}

test.describe.serial('Admin plans CRUD', () => {
    test('list renders and empty form shows Thai zod error', async ({ page }) => {
        await loginAsAdmin(page);
        await page.goto('/plans');

        await expect(page.getByRole('heading', { level: 1, name: 'แผนงาน/ผลผลิต' })).toBeVisible({ timeout: 15000 });

        await page.getByRole('button', { name: 'เพิ่มแผนงาน' }).click();
        const dialog = page.getByRole('dialog');
        await expect(dialog).toBeVisible();

        // fiscal_year is prefilled from the list, so only name_th should error.
        await dialog.locator('#plan-name').fill('');
        await dialog.getByRole('button', { name: 'บันทึก' }).click();
        await expect(dialog.getByText('กรุณาระบุชื่อแผนงาน/โครงการ')).toBeVisible();
        await dialog.getByRole('button', { name: 'ยกเลิก' }).click();
    });

    test('create shows toast and row appears', async ({ page }) => {
        await loginAsAdmin(page);
        await page.goto('/plans');

        await page.getByRole('button', { name: 'เพิ่มแผนงาน' }).click();
        const dialog = page.getByRole('dialog');
        await dialog.locator('#plan-name').fill(TEST_NAME);
        await dialog.locator('#plan-code').fill(TEST_CODE);
        // Leave the prefilled fiscal_year Select default as-is.
        await dialog.getByRole('button', { name: 'บันทึก' }).click();

        await expect(page.getByText('เพิ่มแผนงานสำเร็จ')).toBeVisible({ timeout: 10000 });
        await expect(page.getByRole('cell', { name: TEST_CODE })).toBeVisible();
    });

    test('edit shows success toast', async ({ page }) => {
        await loginAsAdmin(page);
        await page.goto('/plans');

        const row = page.getByRole('row', { name: new RegExp(TEST_CODE) });
        await row.getByRole('button', { name: 'แก้ไข' }).click();

        const dialog = page.getByRole('dialog');
        await dialog.locator('#plan-name-en').fill('Test Plan EN');
        await dialog.getByRole('button', { name: 'บันทึก' }).click();

        await expect(page.getByText('แก้ไขแผนงานสำเร็จ')).toBeVisible();
    });

    test('delete with confirm removes the row', async ({ page }) => {
        await loginAsAdmin(page);
        await page.goto('/plans');

        const row = page.getByRole('row', { name: new RegExp(TEST_CODE) });
        await row.getByRole('button', { name: 'ลบ' }).click();

        const confirmDialog = page.getByRole('alertdialog');
        await confirmDialog.getByRole('button', { name: 'ลบ' }).click();

        await expect(page.getByText('ลบแผนงานสำเร็จ')).toBeVisible();
        await expect(page.getByRole('cell', { name: TEST_CODE })).toBeHidden();
    });
});
