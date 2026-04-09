import Lara from "@primeuix/themes/lara";
import "primeicons/primeicons.css";
import PrimeVue from "primevue/config";
import Toast from "primevue/toast";
import ToastService from "primevue/toastservice";
import { createApp } from "vue";

import App from "./App.vue";
import router from "./router";

const app = createApp(App);

app.use(router);
app.use(PrimeVue, {
  theme: {
    preset: Lara,
  },
  locale: {
    emptySearchMessage: "Nenhum produto encontrado",
    emptyMessage: "",
  },
});
app.use(ToastService);
app.component("Toast", Toast);

app.mount("#app");
