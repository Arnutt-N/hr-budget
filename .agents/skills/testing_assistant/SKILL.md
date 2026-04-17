---
name: testing_assistant
description: Guide for testing with PHPUnit (Backend) and Playwright (E2E). Includes configuration, directory structure, and best practices.
---

# Testing Assistant

Guide for verifying application stability using PHPUnit and Playwright.

## 📑 Table of Contents
- [Overview](#-overview)
- [Unit & Integration Testing (PHPUnit)](#-unit--integration-testing-phpunit)
- [E2E Testing (Playwright)](#-e2e-testing-playwright)
- [Best Practices](#-best-practices)
- [Troubleshooting](#-troubleshooting)

## 🧪 Overview

| Type | Framework | Location | Purpose |
|:-----|:----------|:---------|:--------|
| **Backend** | PHPUnit 10 | `tests/Unit`, `tests/Integration` | Logic, DB, Controllers |
| **Frontend** | Playwright | `tests/e2e` | Browser automation, UI flows |

## 🐘 Unit & Integration Testing (PHPUnit)

### 1. Setup & Running
```bash
# Run all tests
./vendor/bin/phpunit

# Run specific file
./vendor/bin/phpunit tests/Unit/ExampleTest.php

# Run with coverage (requires xdebug/pcov)
./vendor/bin/phpunit --coverage-html coverage/
```

### 2. Base Class: `Tests\TestCase`
All tests should extend `Tests\TestCase`. It provides:
- **Automatic Transactions**: Database rolls back after each test.
- **Authentication**: `actingAs($user)`, `createAdmin()`.
- **Assertions**: `assertDatabaseHas($table, $data)`.

### 3. Example Test Class

```php
<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\BudgetRequest;

class BudgetRequestTest extends TestCase
{
    public function test_admin_can_create_request()
    {
        // Arrange
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $data = [
            'name' => 'New PC Request',
            'amount' => 50000
        ];

        // Act
        $response = $this->post('/requests', $data);

        // Assert
        // Check DB
        $this->assertDatabaseHas('budget_requests', ['name' => 'New PC Request']);
        
        // Check Redirect/Response
        $this->assertEquals(200, $response['status']);
    }

    public function test_guest_cannot_access()
    {
        $response = $this->get('/requests');
        // Expect redirect to login (implementation specific)
    }
}
```

## 🎭 E2E Testing (Playwright)

### 1. Setup & Running
```bash
# Run all tests (headless)
npx playwright test

# Run specific file
npx playwright test tests/e2e/login.spec.ts

# Run with UI (Interactive)
npx playwright test --ui

# Debug mode
npx playwright test --debug
```

### 2. Configuration
Config file: `playwright.config.ts`
- **Base URL**: `http://localhost/hr_budget/public`
- **Browsers**: Chromium, Firefox, WebKit
- **Screenshots**: Only on failure

### 3. Example Spec (`tests/e2e/login.spec.ts`)

```typescript
import { test, expect } from '@playwright/test';

test('user can login', async ({ page }) => {
  await page.goto('/login');

  // Fill form
  await page.fill('input[name="email"]', 'admin@moj.go.th');
  await page.fill('input[name="password"]', 'password');
  
  // Click login
  await page.click('button[type="submit"]');

  // Assert redirection to dashboard
  await expect(page).toHaveURL('/');
  
  // Assert text presence
  await expect(page.locator('h1')).toContainText('Dashboard');
});
```

## ✅ Best Practices

### PHPUnit
1. **Isolation**: Always rely on `TestCase` transaction rollback. Do not commit transactions manually in tests.
2. **Factories**: Use `createUser()`, `createAdmin()` helpers instead of raw inserts.
3. **Naming**: Use `test_feature_capability` pattern (snake_case) for methods.

### Playwright
1. **Selectors**: Use user-facing locators (text, role) over CSS classes.
   - ✅ `page.getByRole('button', { name: 'Save' })`
   - ❌ `page.locator('.btn-primary')`
2. **Independence**: Each test should handle its own data setup/teardown (or assume clean state).
3. **Stability**: Use `await expect(...)` which auto-retries.

## 🚨 Troubleshooting

### PHPUnit
- **"Table not found"**: Check `.env.testing` or your DB config.
- **"Transaction already active"**: Remove manual `beginTransaction()` calls in your code being tested.

### Playwright
- **"Target closed"**: Browser crashed or closed manually.
- **"Timeout"**: Application is slow. Increase timeout in `playwright.config.ts`.
- **Thai Fonts**: If screenshots look wrong, ensure Thai fonts are installed on the OS running tests.
