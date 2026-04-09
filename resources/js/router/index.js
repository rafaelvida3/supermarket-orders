import { createRouter, createWebHistory } from "vue-router";

const OrdersListPage = () => import("@/pages/OrdersList.vue");
const OrderPage = () => import("@/pages/OrderPage.vue");
const StockPage = () => import("@/pages/StockPage.vue");
const NotFoundPage = () => import("@/pages/NotFound.vue");

const routes = [
    {
        path: "/orders",
        component: OrdersListPage,
        name: "orders.list",
        meta: { title: "Pedidos" },
    },
    {
        path: "/orders/new",
        component: OrderPage,
        name: "orders.new",
        meta: { title: "Novo Pedido" },
    },
    {
        path: "/orders/:id",
        component: OrderPage,
        name: "orders.view",
        meta: { title: "Visualizar Pedido" },
    },
    {
        path: "/stock",
        component: StockPage,
        name: "stock.index",
        meta: { title: "Estoque" },
    },
    {
        path: "/",
        redirect: "/orders",
    },
    {
        path: "/:pathMatch(.*)*",
        name: "NotFound",
        component: NotFoundPage,
        meta: { title: "Página não encontrada" },
    },
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
