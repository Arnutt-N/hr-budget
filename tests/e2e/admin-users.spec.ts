import { test, expect, type Page } from '@playwright/test';

/** Phase 2 — admin users CRUD (DataTable + Dialog; password only on create). */

const ADMIN_EMAIL = 'admin@moj.go.th';
const ADMIN_PASSWORD = 'admin123';

const STAMP = Date.now().toString(36).slice(-6);
const TEST_EMAIL = `e2e-${STAMP}@moj.go.th`;
const TEST_NAME = `ผู้ใช้ทดสอบ ${STAMP}`;

async function loginAsAdmin(page: Page): Promise<void> {
    await page.goto('/login');
    await page.fill('input[name="email"]', ADMIN_EMAIL);
    await page.fill('input[name="password"]', ADMIN_PASSWORD);
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard');
}

test.describe.serial('Admin users CRUD', () => {
    test('list renders and empty form shows Thai zod errors', async ({ page }) => {
        await loginAsAdmin(page);
        await page.goto('/users');

        await expect(page.getByRole('heading', { level: 1, name: 'จัดการผู้ใช้' })).toBeVisible();

        await page.getByRole('button', { name: 'เพิ่มผู้ใช้' }).click();
        const dialog = page.getByRole('dialog');
        await dialog.getByRole('button', { name: 'บันทึก' }).click();

        await expect(dialog.getByText('กรุณากรอกอีเมล')).toBeVisible();
        await expect(dialog.getByText('กรุณากรอกรหัสผ่าน')).toBeVisible();
        await expect(dialog.getByText('กรุณากรอกชื่อ')).toBeVisible();
        await dialog.getByRole('button', { name: 'ยกเลิก' }).click();
    });

    test('create user shows toast and row appears', async ({ page }) => {
        await loginAsAdmin(page);
        await page.goto('/users');

        await page.getByRole('button', { name: 'เพิ่มผู้ใช้' }).click();
        const dialog = page.getByRole('dialog');
        await dialog.locator('#user-email').fill(TEST_EMAIL);
        await dialog.locator('#user-password').fill('e2e-password-123');
        await dialog.locator('#user-name').fill(TEST_NAME);
        await dialog.getByRole('button', { name: 'บันทึก' }).click();

        await expect(page.getByText('เพิ่มผู้ใช้สำเร็จ')).toBeVisible({ timeout: 10000 });
        await expect(page.getByRole('cell', { name: TEST_EMAIL })).toBeVisible();
    });

    test('edit without password keeps account working', async ({ page }) => {
        await loginAsAdmin(page);
        await page.goto('/users');

        const row = page.getByRole('row', { name: new RegExp(TEST_EMAIL.replace('.', '\\.')) });
        await row.getByRole('button', { name: 'แก้ไข' }).click();

        const dialog = page.getByRole('dialog');
        // Leave password blank — must not be sent
        await dialog.locator('#user-name').fill(`${TEST_NAME} (แก้ไข)`);
        await dialog.getByRole('button', { name: 'บันทึก' }).click();

        await expect(page.getByText('แก้ไขผู้ใช้สำเร็จ')).toBeVisible();
    });

    test('delete with confirm removes the row', async ({ page }) => {
        await loginAsAdmin(page);
        await page.goto('/users');

        const row = page.getByRole('row', { name: new RegExp(TEST_EMAIL.replace('.', '\\.')) });
        await row.getByRole('button', { name: 'ลบ' }).click();

        const confirmDialog = page.getByRole('alertdialog');
        await confirmDialog.getByRole('button', { name: 'ลบ' }).click();

        await expect(page.getByText('ลบผู้ใช้สำเร็จ')).toBeVisible();
        await expect(page.getByRole('cell', { name: TEST_EMAIL })).toBeHidden();
    });
});
