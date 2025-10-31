import { createRouter, createWebHistory } from 'vue-router'

/* ===== Page components ===== */
import NotFound from '@/components/pages/NotFound.vue'
import OrderPage from '@/components/pages/OrderPage.vue'
import OrdersList from '@/components/pages/OrdersList.vue'

/* ===== Route definitions ===== */
const routes = [
  {
    // Orders list page
    path: '/pedidos',
    component: OrdersList,
    name: 'orders.list',
    meta: { title: 'Pedidos' }
  },
  {
    // Create new order page
    path: '/pedidos/novo',
    component: OrderPage,
    name: 'orders.new',
    meta: { title: 'Novo Pedido' }
  },
  {
    // View existing order by ID
    path: '/pedidos/:id',
    component: OrderPage,
    name: 'orders.view',      
    meta: { title: 'Visualizar Pedido' }
  },
  {
    // Redirect root path to orders list
    path: '/',
    redirect: '/pedidos'
  },
  {
    // Catch-all route for 404 not found
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: NotFound
  }
]

/* ===== Router instance ===== */
const router = createRouter({
  history: createWebHistory(), // Uses HTML5 history mode
  routes,                      // Registers defined routes
})

/* ===== Dynamic page title ===== */
router.afterEach((to) => {
  const defaultTitle = 'Pedidos'
  // Updates document title based on route meta
  document.title = to.meta.title ? to.meta.title : defaultTitle
})

/* ===== Export router instance ===== */
export default router