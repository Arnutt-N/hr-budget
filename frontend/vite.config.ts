import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url'

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')
  const apiTarget = env.VITE_API_URL || 'http://hr_budget.test'

  // Deploy build is gated ONLY behind an explicit VITE_BASE env var — NOT on
  // `mode === 'production'` (CI's `npm run build` IS production mode and must
  // keep base '/' + outDir 'dist' so the CI frontend/e2e jobs stay green).
  //   - Default build (no VITE_BASE):   base '/'                         → dist
  //   - Deploy build  (VITE_BASE set):  base '/hr_budget/public/app/'    → ../public/app
  // Run the deploy build with:  VITE_BASE=/hr_budget/public/app/ npm run build
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
