import { test, expect, type Page } from '@playwright/test';

/** Phase 2 — admin target-types CRUD (DataTable + Dialog). */

const ADMIN_EMAIL = 'admin@moj.go.th';
const ADMIN_PASSWORD = 'admin123';

const STAMP = Date.now().toString(36).slice(-6);
const TEST_CODE = `E2E-${STAMP}`;
const TEST_NAME = `ประเภททดสอบ ${STAMP}`;

async function loginAsAdmin(page: Page): Promise<void> {
    await page.goto('/login');
    await page.fill('input[name="email"]', ADMIN_EMAIL);
    await page.fill('input[name="password"]', ADMIN_PASSWORD);
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard');
}

test.describe.serial('Admin target-types CRUD', () => {
    test('list renders and empty form shows Thai zod errors', async ({ page }) => {
        await loginAsAdmin(page);
        await page.goto('/target-types');

        await expect(page.getByRole('heading', { level: 1, name: 'ประเภทเป้าหมาย' })).toBeVisible({ timeout: 15000 });

        await page.getByRole('button', { name: 'เพิ่มประเภทเป้าหมาย' }).click();
        const dialog = page.getByRole('dialog');
        await expect(dialog).toBeVisible();

        await dialog.getByRole('button', { name: 'บันทึก' }).click();
        await expect(dialog.getByText('กรุณาระบุรหัสประเภทเป้าหมาย')).toBeVisible();
        await expect(dialog.getByText('กรุณาระบุชื่อประเภทเป้าหมาย')).toBeVisible();
        await dialog.getByRole('button', { name: 'ยกเลิก' }).click();
    });

    test('create shows toast and row appears', async ({ page }) => {
        await loginAsAdmin(page);
        await page.goto('/target-types');

        await page.getByRole('button', { name: 'เพิ่มประเภทเป้าหมาย' }).click();
        const dialog = page.getByRole('dialog');
        await dialog.locator('#tt-code').fill(TEST_CODE);
        await dialog.locator('#tt-name').fill(TEST_NAME);
        await dialog.getByRole('button', { name: 'บันทึก' }).click();

        await expect(page.getByText('เพิ่มประเภทเป้าหมายสำเร็จ')).toBeVisible({ timeout: 10000 });
        await expect(page.getByRole('cell', { name: TEST_CODE })).toBeVisible();
    });

    test('edit shows success toast', async ({ page }) => {
        await loginAsAdmin(page);
        await page.goto('/target-types');

        const row = page.getByRole('row', { name: new RegExp(TEST_CODE) });
        await row.getByRole('button', { name: 'แก้ไข' }).click();

        const dialog = page.getByRole('dialog');
        await dialog.locator('#tt-desc').fill('คำอธิบายทดสอบ');
        await dialog.getByRole('button', { name: 'บันทึก' }).click();

        await expect(page.getByText('แก้ไขประเภทเป้าหมายสำเร็จ')).toBeVisible();
    });

    test('delete with confirm removes the row', async ({ page }) => {
        await loginAsAdmin(page);
        await page.goto('/target-types');

        const row = page.getByRole('row', { name: new RegExp(TEST_CODE) });
        await row.getByRole('button', { name: 'ลบ' }).click();

        const confirmDialog = page.getByRole('alertdialog');
        await confirmDialog.getByRole('button', { name: 'ลบ' }).click();

        await expect(page.getByText('ลบประเภทเป้าหมายสำเร็จ')).toBeVisible();
        await expect(page.getByRole('cell', { name: TEST_CODE })).toBeHidden();
    });
});
