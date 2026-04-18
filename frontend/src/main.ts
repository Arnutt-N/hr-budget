import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import './style.css'

const app = createApp(App)
app.use(createPinia()) // Pinia MUST be installed before router (guards use stores)
app.use(router)
app.mount('#app')
