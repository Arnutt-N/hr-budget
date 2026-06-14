import { test, expect, type Page } from '@playwright/test';

/**
 * Phase 5 — Disbursement tracking wizard on the Vue SPA.
 *
 * Drives the full 4-step flow as the admin:
 *   Step 1  pick org (org 3) + fiscal year 2569 + month 11  → create-or-fetch session
 *   Step 2  pick the first activity (radio)                 → create-or-fetch record
 *   Step 3  fill one item's allocated=100, disbursed=100    → live remaining updates
 *   Step 4  สรุป → บันทึก                                    → success toast + back to list
 *
 * create-or-fetch is idempotent (session keyed by org+fy+month, record by
 * session+activity), so the spec is rerun-safe locally; CI reseeds fresh.
 * Month 11 is used deliberately to avoid colliding with the seeded month-10
 * session for org 3 / fy 2569.
 */

const ADMIN_EMAIL = 'admin@moj.go.th';
const ADMIN_PASSWORD = 'admin123';

const FISCAL_YEAR = '2569';
const RECORD_MONTH = '11'; // value attribute on the month <option> (พฤศจิกายน)

async function loginAsAdmin(page: Page): Promise<void> {
  await page.goto('/login');
  await page.fill('input[name="email"]', ADMIN_EMAIL);
  await page.fill('input[name="password"]', ADMIN_PASSWORD);
  await page.click('button[type="submit"]');
  await page.waitForURL('**/dashboard');
}

test.describe('Disbursement tracking workflow (SPA)', () => {
  test('create session → activity → amounts → save', async ({ page }) => {
    await loginAsAdmin(page);

    // Open the list and start the wizard.
    await page.goto('/disbursements');
    await expect(
      page.getByRole('heading', { name: 'บันทึกการเบิกจ่ายงบประมาณ' }),
    ).toBeVisible();
    await page.getByRole('button', { name: '+ บันทึกการเบิกจ่าย' }).click();
    await page.waitForURL(/\/disbursements\/wizard$/);

    // --- Step 1: org + fiscal year + month ---
    await expect(page.getByRole('heading', { name: /ขั้นที่ 1/ })).toBeVisible();
    const selects = page.locator('select');
    // Admin sees three selects: org, fiscal year, month (in DOM order).
    await selects.nth(0).selectOption({ index: 1 }); // first real organization
    await selects.nth(1).selectOption(FISCAL_YEAR);
    await selects.nth(2).selectOption(RECORD_MONTH);
    await page.getByRole('button', { name: 'ถัดไป' }).click();

    // --- Step 2: pick the first activity ---
    await expect(page.getByRole('heading', { name: /ขั้นที่ 2/ })).toBeVisible();
    const firstActivity = page.locator('input[type="radio"][name="activity"]').first();
    await expect(firstActivity).toBeVisible();
    await firstActivity.check();
    await page.getByRole('button', { name: 'ถัดไป' }).click();

    // --- Step 3: fill one item's allocated + disbursed ---
    await expect(page.getByRole('heading', { name: /ขั้นที่ 3/ })).toBeVisible();
    const numberInputs = page.locator('input[type="number"]');
    await expect(numberInputs.first()).toBeVisible();
    // Field order per row: allocated, transfer, disbursed, pending, po.
    await numberInputs.nth(0).fill('100'); // allocated of the first item
    await numberInputs.nth(2).fill('100'); // disbursed of the first item
    await page.getByRole('button', { name: 'ถัดไป' }).click();

    // --- Step 4: summary + save ---
    await expect(page.getByRole('heading', { name: /ขั้นที่ 4/ })).toBeVisible();
    await page.getByRole('button', { name: 'บันทึก' }).click();

    // Success toast, then landed back on the list.
    await expect(page.getByText('บันทึกการเบิกจ่ายสำเร็จ')).toBeVisible();
    await page.waitForURL(/\/disbursements$/);
    await expect(
      page.getByRole('heading', { name: 'บันทึกการเบิกจ่ายงบประมาณ' }),
    ).toBeVisible();
  });
});
