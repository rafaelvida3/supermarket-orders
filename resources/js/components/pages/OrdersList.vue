<template>
  <!-- Main responsive container -->
  <div
    class="max-w-5xl mx-auto p-4 sm:p-6 bg-white dark:bg-gray-900 shadow rounded-lg transition-colors duration-300"
  >
    <!-- Header section -->
    <div
      class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6"
    >
      <!-- Page title -->
      <h1
        class="text-xl sm:text-2xl font-semibold text-gray-800 dark:text-gray-100"
      >
        Pedidos
      </h1>

      <!-- Button to create a new order -->
      <RouterLink
        :to="{ name: 'orders.new' }"
        class="w-full sm:w-auto bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition flex justify-center sm:justify-between gap-2 items-center"
      >
        <i class="pi pi-plus"></i>
        Novo Pedido
      </RouterLink>
    </div>

    <!-- Mobile cards -->
    <div v-if="!loading && paginatedOrders.length > 0" class="sm:hidden space-y-3">
      <article
        v-for="order in paginatedOrders"
        :key="order.id"
        class="border rounded-md p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800"
      >
        <div class="space-y-3">
          <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
              Data do Pedido
            </p>
            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
              {{ formatDate(order.created_at, 'DD/MM/YYYY HH:mm') }}
            </p>
          </div>

          <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
              Cliente
            </p>
            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 break-words">
              {{ order.customer_name }}
            </p>
          </div>

          <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
              Data de Entrega
            </p>
            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
              {{ formatDate(order.delivery_date) }}
            </p>
          </div>

          <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
              Total
            </p>
            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
              {{ formatPrice(order.total) }}
            </p>
          </div>
        </div>

        <button
          type="button"
          class="mt-4 w-full cursor-pointer border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:text-blue-600 dark:hover:text-blue-400 rounded px-3 py-2 transition flex items-center justify-center gap-2"
          @click="goToOrderDetails(order.id)"
        >
          <i class="pi pi-eye"></i>
          Ver pedido
        </button>
      </article>

      <div
        v-if="totalPages > 1"
        class="flex items-center justify-between gap-3 pt-2"
      >
        <button
          type="button"
          class="flex-1 px-3 py-2 rounded border border-gray-300 dark:border-gray-600 text-sm text-gray-700 dark:text-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
          :disabled="currentPage === 1"
          @click="goToPreviousPage"
        >
          Anterior
        </button>

        <span class="text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
          Página {{ currentPage }} de {{ totalPages }}
        </span>

        <button
          type="button"
          class="flex-1 px-3 py-2 rounded border border-gray-300 dark:border-gray-600 text-sm text-gray-700 dark:text-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
          :disabled="currentPage === totalPages"
          @click="goToNextPage"
        >
          Próxima
        </button>
      </div>
    </div>

    <!-- Desktop table -->
    <div class="hidden sm:block overflow-x-auto rounded-md border dark:border-gray-700">
      <!-- Orders table -->
      <DataTable
        class="min-w-full text-sm sm:text-base dark:text-gray-200 dark:bg-gray-800 dark:border-gray-700"
        :value="orders"
        :loading="loading"
        stripedRows
        paginator
        :rows="rowsPerPage"
        sortField="created_at"
        :sortOrder="-1"
      >
        <!-- Order date column -->
        <Column field="created_at" sortable>
          <template #header>
            <span class="font-bold dark:text-gray-100 whitespace-nowrap"
              >Data do Pedido</span
            >
          </template>
          <template #body="slotProps">
            {{ formatDate(slotProps.data.created_at, 'DD/MM/YYYY HH:mm') }}
          </template>
        </Column>

        <!-- Customer name column -->
        <Column field="customer_name" sortable>
          <template #header>
            <span class="font-bold dark:text-gray-100">Cliente</span>
          </template>
          <template #body="slotProps">
            <span class="whitespace-nowrap sm:whitespace-normal break-words">{{
              slotProps.data.customer_name
            }}</span>
          </template>
        </Column>

        <!-- Delivery date column -->
        <Column field="delivery_date" sortable>
          <template #header>
            <span class="font-bold dark:text-gray-100 whitespace-nowrap"
              >Data de Entrega</span
            >
          </template>
          <template #body="slotProps">
            {{ formatDate(slotProps.data.delivery_date) }}
          </template>
        </Column>

        <!-- Total amount column -->
        <Column field="total" sortable>
          <template #header>
            <span class="font-bold dark:text-gray-100">Total</span>
          </template>
          <template #body="slotProps">
            {{ formatPrice(slotProps.data.total) }}
          </template>
        </Column>

        <!-- Action column (view order details) -->
        <Column>
          <template #body="slotProps">
            <button
              class="cursor-pointer text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition"
              @click="goToOrderDetails(slotProps.data.id)"
            >
              <i class="pi pi-eye" title="Ver"></i>
            </button>
          </template>
        </Column>
      </DataTable>
    </div>

    <!-- Mobile loading state -->
    <div
      v-if="loading"
      class="sm:hidden text-gray-500 dark:text-gray-400 text-center text-sm"
    >
      Carregando pedidos...
    </div>

    <!-- Empty state message -->
    <div
      v-if="orders.length === 0 && !loading"
      class="text-gray-500 dark:text-gray-400 mt-4 text-center text-sm sm:text-base"
    >
      Nenhum pedido encontrado.
    </div>
  </div>
</template>

<script setup>
/* ===== Imports ===== */
import { formatDate, formatPrice } from '@/helpers'; // Utility functions for formatting
import { fetchOrders } from '@/services/ordersService'; // API call to fetch order list
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import { useToast } from 'primevue/usetoast';
import { computed, onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';

/* ===== Setup ===== */
const router = useRouter()
const toast = useToast()

/* ===== Constants ===== */
const rowsPerPage = 10

/* Reactive state */
const orders = ref([]) // Stores fetched orders
const loading = ref(true) // Loading state for DataTable
const currentPage = ref(1)

/* ===== Computed values ===== */
const totalPages = computed(() => {
  return Math.max(1, Math.ceil(orders.value.length / rowsPerPage))
})

const paginatedOrders = computed(() => {
  const startIndex = (currentPage.value - 1) * rowsPerPage
  const endIndex = startIndex + rowsPerPage

  return orders.value.slice(startIndex, endIndex)
})

/* ===== Helpers ===== */
const sortOrdersByCreatedAtDesc = (ordersList) => {
  return [...ordersList].sort((firstOrder, secondOrder) => {
    return new Date(secondOrder.created_at).getTime() - new Date(firstOrder.created_at).getTime()
  })
}

const goToOrderDetails = (orderId) => {
  router.push({
    name: 'orders.view',
    params: { id: orderId },
  })
}

const goToPreviousPage = () => {
  if (currentPage.value > 1) {
    currentPage.value -= 1
  }
}

const goToNextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value += 1
  }
}

/* ===== Watchers ===== */
watch(totalPages, (newTotalPages) => {
  if (currentPage.value > newTotalPages) {
    currentPage.value = newTotalPages
  }
})

/* ===== Lifecycle: on mount ===== */
onMounted(async () => {
  try {
    showOverlay() // Displays global loading overlay
    loading.value = true // Activates table loading indicator

    const fetchedOrders = await fetchOrders()
    orders.value = sortOrdersByCreatedAtDesc(fetchedOrders)
  } catch (e) {
    const errorMessage = e instanceof Error ? e.message : 'Erro inesperado ao carregar pedidos.'

    // Handles API or network errors
    toast.add({
      severity: 'error',
      summary: 'Erro ao carregar pedidos',
      detail: errorMessage,
    })
  } finally {
    loading.value = false // Disables table loading indicator
    hideOverlay() // Hides global loading overlay
  }
})
</script>
