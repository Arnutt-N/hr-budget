import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url'

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')
  const apiTarget = env.VITE_API_URL || 'http://hr_budget.test'

  return {
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
      outDir: 'dist',
      sourcemap: mode !== 'production',
      // Avoid Vite 6 + Rollup "modulepreload-polyfill" bundling bug on Windows
      modulePreload: { polyfill: false },
    },
  }
})
