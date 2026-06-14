import { createApp } from 'vue'
import { createPinia } from 'pinia'
import PrimeVue from 'primevue/config'
import { definePreset } from '@primeuix/themes'
import Aura from '@primeuix/themes/aura'
import ToastService from 'primevue/toastservice'
import ConfirmationService from 'primevue/confirmationservice'
import { VueQueryPlugin } from '@tanstack/vue-query'
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
      600: '{sky.600}',
      700: '{sky.700}',
      800: '{sky.800}',
      900: '{sky.900}',
      950: '{sky.950}',
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
app.use(VueQueryPlugin)
app.mount('#app')
