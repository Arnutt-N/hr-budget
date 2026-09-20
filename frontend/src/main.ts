import { createApp } from 'vue'
import { createPinia } from 'pinia'
import PrimeVue from 'primevue/config'
import { definePreset } from '@primeuix/themes'
import Aura from '@primeuix/themes/aura'
import ToastService from 'primevue/toastservice'
import ConfirmationService from 'primevue/confirmationservice'
import { VueQueryPlugin } from '@tanstack/vue-query'
import { queryClient } from '@/lib/queryClient'
import App from './App.vue'
import router from './router'
import './style.css'

// Sky primary — matches the legacy dark theme (#0ea5e9 family)
const HrBudgetPreset = definePreset(Aura, {
  semantic: {
    primary: {
      50: '{sky.50}',
      100: '{sky.100}',
      200: '{sky.200}',
      300: '{sky.300}',
      400: '{sky.400}',
      500: '{sky.500}',
      600: '{sky.700}', // #0369a1 — 5.93:1 white/AA (was sky.600 #0284c7 4.10)
      700: '{sky.800}', // #075985 — solid-hover target (D6)
      800: '{sky.800}',
      900: '{sky.900}',
      950: '{sky.950}',
    },
  },
  components: {
    message: {
      colorScheme: {
        light: { info: { color: '{sky.400}' } },
        dark: { info: { color: '{sky.400}' } },
      },
    },
  },
})

const app = createApp(App)
app.use(createPinia()) // Pinia MUST be installed before router (guards use stores)
app.use(router)
app.use(PrimeVue, {
  theme: {
    preset: HrBudgetPreset,
    options: { darkModeSelector: '.app-dark' },
  },
})
app.use(ToastService)
app.use(ConfirmationService)
app.use(VueQueryPlugin, { queryClient })
app.mount('#app')
