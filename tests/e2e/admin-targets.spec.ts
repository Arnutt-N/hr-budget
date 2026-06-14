import { test, expect, type Page } from '@playwright/test';

/** Phase 2 — admin targets CRUD (DataTable + Dialog with FK Selects).
 *  Targets reference a target-type, so each run seeds a fresh target-type first. */

const ADMIN_EMAIL = 'admin@moj.go.th';
const ADMIN_PASSWORD = 'admin123';

const STAMP = Date.now().toString(36).slice(-6);
const TT_CODE = `TGX-${STAMP}`;
const TT_NAME = `ประเภทเป้าหมายสำหรับเทสต์ ${STAMP}`;

async function loginAsAdmin(page: Page): Promise<void> {
    await page.goto('/login');
    await page.fill('input[name="email"]', ADMIN_EMAIL);
    await page.fill('input[name="password"]', ADMIN_PASSWORD);
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard');
}

test.describe.serial('Admin targets CRUD', () => {
    test('list renders and empty form shows Thai zod validation error', async ({ page }) => {
        await loginAsAdmin(page);
        await page.goto('/targets');

        await expect(page.getByRole('heading', { level: 1, name: 'เป้าหมายงบประมาณ' })).toBeVisible({ timeout: 15000 });

        await page.getByRole('button', { name: 'เพิ่มเป้าหมาย' }).click();
        const dialog = page.getByRole('dialog');
        await expect(dialog).toBeVisible();

        await dialog.getByRole('button', { name: 'บันทึก' }).click();
        await expect(dialog.getByText('กรุณาเลือกประเภทเป้าหมาย')).toBeVisible();
        await dialog.getByRole('button', { name: 'ยกเลิก' }).click();
    });

    test('create target (seeds target-type first) shows toast', async ({ page }) => {
        await loginAsAdmin(page);

        // 1) Seed a target-type to reference.
        await page.goto('/target-types');
        await page.getByRole('button', { name: 'เพิ่มประเภทเป้าหมาย' }).click();
        const ttDialog = page.getByRole('dialog');
        await ttDialog.locator('#tt-code').fill(TT_CODE);
        await ttDialog.locator('#tt-name').fill(TT_NAME);
        await ttDialog.getByRole('button', { name: 'บันทึก' }).click();
        await expect(page.getByText('เพิ่มประเภทเป้าหมายสำเร็จ')).toBeVisible({ timeout: 10000 });

        // 2) Create a target referencing the seeded target-type.
        await page.goto('/targets');
        await page.getByRole('button', { name: 'เพิ่มเป้าหมาย' }).click();
        const dialog = page.getByRole('dialog');
        await expect(dialog).toBeVisible();

        // Open the target-type Select (first PrimeVue combobox in the dialog) and pick the seeded option.
        await dialog.getByRole('combobox').first().click();
        await page.getByRole('option', { name: new RegExp(STAMP) }).click();

        // fiscal_year keeps its default. Fill the percent InputNumber.
        await dialog.locator('#tgt-percent').fill('80');

        await dialog.getByRole('button', { name: 'บันทึก' }).click();
        await expect(page.getByText('เพิ่มเป้าหมายสำเร็จ')).toBeVisible({ timeout: 10000 });
    });

    test('delete target with confirm shows toast', async ({ page }) => {
        await loginAsAdmin(page);
        await page.goto('/targets');

        // Target the row whose target-type cell matches the seeded name; fall back to the first row.
        const matchedRow = page.getByRole('row', { name: new RegExp(STAMP) });
        const row = (await matchedRow.count()) > 0
            ? matchedRow.first()
            : page.getByRole('row').filter({ has: page.getByRole('button', { name: 'ลบ' }) }).first();

        await row.getByRole('button', { name: 'ลบ' }).click();

        const confirmDialog = page.getByRole('alertdialog');
        await confirmDialog.getByRole('button', { name: 'ลบ' }).click();

        await expect(page.getByText('ลบเป้าหมายสำเร็จ')).toBeVisible({ timeout: 10000 });
    });
});
