import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url'

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')
  const apiTarget = env.VITE_API_URL || 'http://hr_budget.test'

  // Deploy build is gated ONLY behind an explicit VITE_BASE env var — NOT on
  // `mode === 'production'` (CI's `npm run build` IS production mode and must
  // keep base '/' + outDir 'dist' so the CI frontend/e2e jobs stay green).
  // Do NOT add a committed frontend/.env.production — it would flip every
  // plain `npm run build` into a deploy build and break that invariant.
  //   - Default build (no VITE_BASE):   base '/'       → dist
  //   - Deploy build  (VITE_BASE set):  base VITE_BASE → ../public/app
  // Shell env wins over frontend/.env.production.local (local Laragon paths),
  // so always pass BOTH vars explicitly for a production deploy build:
  //   Local Laragon smoke:  VITE_BASE=/hr_budget/public/app/ VITE_API_BASE_URL=/hr_budget/public npm run build
  //   Production (Plesk):   VITE_BASE=/hr-budget/public/app/ VITE_API_BASE_URL=/hr-budget/public npm run build
  const deployBase = env.VITE_BASE || ''
  const base = deployBase || '/'

  return {
    base,
    plugins: [vue()],

    resolve: {
      alias: {
        '@': fileURLToPath(new URL('./src', import.meta.url)),
      },
    },

    server: {
      port: 5174,
      strictPort: true,
      // Proxy /api/* to the PHP backend (Laragon) so dev runs same-origin
      // and CORS headers are exercised end-to-end without extra config.
      proxy: {
        '/api': {
          target: apiTarget,
          changeOrigin: true,
        },
      },
    },

    build: {
      // Deploy build emits into the tracked `public/app/` dir served by PHP;
      // default build keeps `dist` (the CI artifact + vite preview target).
      outDir: deployBase ? '../public/app' : 'dist',
      emptyOutDir: true,
      sourcemap: mode !== 'production',
      // Avoid Vite 6 + Rollup "modulepreload-polyfill" bundling bug on Windows
      modulePreload: { polyfill: false },
    },
  }
})
