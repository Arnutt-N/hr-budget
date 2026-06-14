import { test, expect, type Page } from '@playwright/test';

/** Phase 2 — admin budget categories CRUD + expandable items panel. */

const ADMIN_EMAIL = 'admin@moj.go.th';
const ADMIN_PASSWORD = 'admin123';

const STAMP = Date.now().toString(36).slice(-6);
const TEST_CODE = `E2${STAMP}`.slice(0, 10);
const TEST_NAME = `หมวดทดสอบ ${STAMP}`;
const TEST_ITEM = `รายการทดสอบ ${STAMP}`;

async function loginAsAdmin(page: Page): Promise<void> {
    await page.goto('/login');
    await page.fill('input[name="email"]', ADMIN_EMAIL);
    await page.fill('input[name="password"]', ADMIN_PASSWORD);
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard');
}

test.describe.serial('Admin categories CRUD', () => {
    test('list renders and empty form shows Thai zod errors', async ({ page }) => {
        await loginAsAdmin(page);
        await page.goto('/categories');

        await expect(page.getByRole('heading', { name: 'ประเภทรายจ่าย', exact: true })).toBeVisible();

        await page.getByRole('button', { name: 'เพิ่มหมวด' }).click();
        const dialog = page.getByRole('dialog');
        await dialog.getByRole('button', { name: 'บันทึก' }).click();

        await expect(dialog.getByText('กรุณาระบุรหัส', { exact: true })).toBeVisible();
        await expect(dialog.getByText('กรุณาระบุชื่อหมวด')).toBeVisible();
        await dialog.getByRole('button', { name: 'ยกเลิก' }).click();
    });

    test('create category shows toast and row appears', async ({ page }) => {
        await loginAsAdmin(page);
        await page.goto('/categories');

        await page.getByRole('button', { name: 'เพิ่มหมวด' }).click();
        const dialog = page.getByRole('dialog');
        await dialog.locator('#cat-code').fill(TEST_CODE);
        await dialog.locator('#cat-name-th').fill(TEST_NAME);
        await dialog.getByRole('button', { name: 'บันทึก' }).click();

        await expect(page.getByText('เพิ่มหมวดสำเร็จ')).toBeVisible();
        await expect(page.getByRole('cell', { name: TEST_CODE })).toBeVisible();
    });

    test('expand row, add item inside the category', async ({ page }) => {
        await loginAsAdmin(page);
        await page.goto('/categories');

        const row = page.getByRole('row', { name: new RegExp(TEST_CODE) });
        // Expander toggle is the first button in the row
        await row.getByRole('button').first().click();

        await expect(page.getByRole('heading', { name: 'รายการในหมวด' })).toBeVisible();
        await page.getByRole('button', { name: 'เพิ่มรายการ' }).click();

        const dialog = page.getByRole('dialog');
        await dialog.locator(`input[id^="item-name-"]`).fill(TEST_ITEM);
        await dialog.getByRole('button', { name: 'บันทึก' }).click();

        await expect(page.getByText('เพิ่มรายการสำเร็จ')).toBeVisible({ timeout: 10000 });
        await expect(page.getByRole('cell', { name: TEST_ITEM, exact: true })).toBeVisible();
    });

    test('delete category with confirm removes the row', async ({ page }) => {
        await loginAsAdmin(page);
        await page.goto('/categories');

        const row = page.getByRole('row', { name: new RegExp(TEST_CODE) });
        await row.getByRole('button', { name: 'ลบ' }).click();

        const confirmDialog = page.getByRole('alertdialog');
        await confirmDialog.getByRole('button', { name: 'ลบ' }).click();

        await expect(page.getByText('ลบหมวดสำเร็จ')).toBeVisible();
        await expect(page.getByRole('cell', { name: TEST_CODE })).toBeHidden();
    });
});
