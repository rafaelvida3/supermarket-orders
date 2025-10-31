/* ===== Imports ===== */
import LoadingOverlayPlugin from '@/plugins/loadingOverlay.js'; // Custom plugin for global loading overlay
import Lara from '@primevue/themes/lara'; // PrimeVue Lara theme preset
import 'primeicons/primeicons.css'; // PrimeIcons icon set
import PrimeVue from 'primevue/config'; // PrimeVue configuration plugin
import Toast from 'primevue/toast'; // Toast component
import ToastService from 'primevue/toastservice'; // Toast service for global notifications
import { createApp } from 'vue'
import App from './components/App.vue'; // Root application component
import router from './router'; // Vue Router instance

/* ===== Create Vue app instance ===== */
const app = createApp(App)

/* ===== Register plugins and global components ===== */
app
  .use(router)                    // Enables routing
  .use(PrimeVue, {                // Configures PrimeVue with theme and locale
    theme: {
      preset: Lara
    },
    locale: {
      emptySearchMessage: 'Nenhum produto encontrado',
      emptyMessage: ''
    }
  })
  .use(ToastService)              // Enables global toast notifications
  .use(LoadingOverlayPlugin)      // Registers global loading overlay plugin
  .component('Toast', Toast)      // Registers Toast component globally

/* ===== Customize default Toast behavior ===== */
const toast = app.config.globalProperties.$toast
const originalAdd = toast.add
// Adds a default display time (life) for all toast messages
toast.add = (msg) => originalAdd({ life: 2500, ...msg })

/* ===== Mount app to DOM ===== */
app.mount('#app')