import { createRouter, createWebHistory } from "vue-router";

import NotFound from "@/pages/NotFound.vue";
import OrderPage from "@/pages/OrderPage.vue";
import OrdersList from "@/pages/OrdersList.vue";
import StockPage from "@/pages/StockPage.vue";

const routes = [
  {
    path: "/orders",
    component: OrdersList,
    name: "orders.list",
    meta: { title: "Pedidos" }
  },
  {
    path: "/orders/new",
    component: OrderPage,
    name: "orders.new",
    meta: { title: "Novo Pedido" }
  },
  {
    path: "/orders/:id",
    component: OrderPage,
    name: "orders.view",
    meta: { title: "Visualizar Pedido" }
  },
  {
    path: "/stock",
    component: StockPage,
    name: "stock.index",
    meta: { title: "Estoque" }
  },
  {
    path: "/",
    redirect: "/orders"
  },
  {
    path: "/:pathMatch(.*)*",
    name: "NotFound",
    component: NotFound
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.afterEach((to) => {
  const defaultTitle = "Pedidos";
  document.title = to.meta.title ? to.meta.title : defaultTitle;
});

export default router;