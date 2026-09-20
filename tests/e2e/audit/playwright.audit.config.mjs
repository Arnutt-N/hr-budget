// Dedicated Playwright config for the SPA design-audit sweep.
//
// The audit file uses a `.audit.mjs` extension (not `.spec.`/`.test.`), so the
// root config's default testMatch never collects it and the root
// `testIgnore: '**/audit/**'` keeps `npm run test:e2e` clean. This config
// selects it explicitly:
//
//   npm run test:e2e:audit            # mock API, zero backend needed
//   BASE_URL=http://<host>/hr_budget/public/app npm run test:e2e:audit  # deploy build
//
// Env: BASE_URL (default http://localhost:5174), AUDIT_MODE=mock|live,
//   AUDIT_ADMIN_EMAIL / AUDIT_ADMIN_PASSWORD (live mode).
import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: '.',
  testMatch: '**/*.audit.mjs',
  timeout: 60 * 1000,
  fullyParallel: false,
  workers: 1,
  reporter: [['list']],
  use: {
    baseURL: process.env.BASE_URL || 'http://localhost:5174',
  },
});
