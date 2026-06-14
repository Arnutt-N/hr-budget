import { test, expect, type Page } from '@playwright/test';

/**
 * Phase 3 — Notifications migrated from a Pinia store to TanStack Query.
 *
 * The seeded test DB gives the admin user (id 2) one unread notification, so we
 * can exercise: the unread badge, the bell dropdown, the dedicated list page,
 * and mark-all-read clearing the badge.
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

test('bell badge, dropdown, list page, and mark-all-read', async ({ page }) => {
    await loginAsAdmin(page);

    // Unread badge from the seeded notification.
    const bell = page.getByRole('button', { name: 'การแจ้งเตือน' });
    await expect(bell).toBeVisible();
    await expect(bell.getByText('1', { exact: true })).toBeVisible();

    // Dropdown lists the seeded notification.
    await bell.click();
    await expect(page.getByText('คำขอได้รับการอนุมัติ').first()).toBeVisible();

    // Footer link navigates to the full list page.
    await page.getByText('ดูการแจ้งเตือนทั้งหมด').click();
    await page.waitForURL('**/notifications');
    await expect(page.getByRole('heading', { name: 'การแจ้งเตือน', level: 1 })).toBeVisible();
    await expect(page.getByText('คำขอได้รับการอนุมัติ').first()).toBeVisible();

    // Mark all read clears the unread badge (TanStack invalidates unread-count).
    await page.getByRole('button', { name: 'อ่านทั้งหมด' }).click();
    await expect(bell.getByText('1', { exact: true })).toBeHidden();
});
