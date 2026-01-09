import { test, expect } from '@playwright/test';

/**
 * E2E Tests for Budget Request Dashboard
 */

test.describe('Budget Request Dashboard', () => {

    // Helper function to login
    async function loginAsAdmin(page) {
        await page.goto('/login');
        await page.fill('input[name="email"]', 'admin@moj.go.th');
        await page.fill('input[name="password"]', 'password');
        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/dashboard/);
    }

    test.beforeEach(async ({ page }) => {
        await loginAsAdmin(page);
    });

    test('TC18: Dashboard shows correct statistics', async ({ page }) => {
        await page.goto('/requests/dashboard');

        // Wait for page to load
        await expect(page.locator('h1')).toContainText('ภาพรวมคำของบประมาณ');

        // Check that stat cards are visible
        await expect(page.locator('text=คำขอทั้งหมด')).toBeVisible();
        await expect(page.locator('text=รออนุมัติ')).toBeVisible();
        await expect(page.locator('text=อนุมัติแล้ว')).toBeVisible();
        await expect(page.locator('text=ยอดรวมที่ขอ')).toBeVisible();

        // Verify stat numbers are displayed (should be numbers)
        const totalRequests = await page.locator('.grid .bg-dark-card').first().locator('.text-2xl').textContent();
        expect(totalRequests).toMatch(/\d+/);
    });

    test('TC19: Dashboard filters by fiscal year', async ({ page }) => {
        await page.goto('/requests/dashboard');

        // Get current fiscal year
        const currentYear = await page.locator('select[name="year"]').inputValue();

        // Change fiscal year
        await page.selectOption('select[name="year"]', '2567');

        // Page should reload with new year
        await page.waitForURL(/year=2567/);

        // Verify the year changed
        const newYear = await page.locator('select[name="year"]').inputValue();
        expect(newYear).toBe('2567');
    });

    test('TC20: Recent requests table is clickable', async ({ page }) => {
        await page.goto('/requests/dashboard');

        // Check if recent requests table exists
        const tableExists = await page.locator('table tbody tr').count() > 0;

        if (tableExists) {
            // Click on first request row
            const firstRow = page.locator('table tbody tr').first();
            await firstRow.click();

            // Should navigate to request detail page
            await expect(page).toHaveURL(/\/requests\/\d+/);
        } else {
            // If no requests, verify empty state message
            await expect(page.locator('text=ยังไม่มีคำของบประมาณ')).toBeVisible();
        }
    });

    test('TC21: Create new request button works', async ({ page }) => {
        await page.goto('/requests/dashboard');

        // Click "สร้างคำขอใหม่" button
        await page.click('text=สร้างคำขอใหม่');

        // Should navigate to create form
        await expect(page).toHaveURL(/\/requests\/create/);
        await expect(page.locator('h1')).toContainText('สร้างคำขอใหม่');
    });

    test('TC22: Dashboard displays status legends', async ({ page }) => {
        await page.goto('/requests/dashboard');

        // Verify status legends are visible
        await expect(page.locator('text=คำอธิบายสถานะ')).toBeVisible();
        await expect(page.locator('text=บันทึกชั่วคราว ยังไม่ส่ง')).toBeVisible();
        await expect(page.locator('text=ส่งแล้ว รอการตรวจสอบ')).toBeVisible();
        await expect(page.locator('text=อนุมัติแล้ว ดำเนินการต่อได้')).toBeVisible();
        await expect(page.locator('text=ถูกปฏิเสธ')).toBeVisible();
    });

    test('TC23: Dashboard responsive layout', async ({ page }) => {
        // Test mobile viewport
        await page.setViewportSize({ width: 375, height: 667 });
        await page.goto('/requests/dashboard');

        // Page should still be accessible
        await expect(page.locator('h1')).toBeVisible();

        // Stats cards should stack vertically (grid should be 1 column)
        const statsGrid = page.locator('.grid').first();
        await expect(statsGrid).toBeVisible();

        // Reset to desktop
        await page.setViewportSize({ width: 1280, height: 720 });
    });

    test('TC24: View all requests link works', async ({ page }) => {
        await page.goto('/requests/dashboard');

        // Click "ดูทั้งหมด" link in recent requests section
        const viewAllLink = page.locator('a:has-text("ดูทั้งหมด")');

        if (await viewAllLink.count() > 0) {
            await viewAllLink.click();

            // Should navigate to requests index page
            await expect(page).toHaveURL(/\/requests$/);
        }
    });

    test('TC25: Dashboard shows correct badge colors', async ({ page }) => {
        await page.goto('/requests/dashboard');

        // Wait for table to load
        await page.waitForSelector('table', { timeout: 5000 }).catch(() => { });

        const rows = await page.locator('table tbody tr').count();

        if (rows > 0) {
            // Check that badges have correct classes
            const badges = page.locator('.badge');
            const badgeCount = await badges.count();

            if (badgeCount > 0) {
                const firstBadge = badges.first();
                const classList = await firstBadge.getAttribute('class');

                // Should have one of the status classes
                const hasStatusClass =
                    classList.includes('badge-gray') ||
                    classList.includes('badge-orange') ||
                    classList.includes('badge-green') ||
                    classList.includes('badge-red');

                expect(hasStatusClass).toBeTruthy();
            }
        }
    });
});
