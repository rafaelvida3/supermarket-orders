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

    <!-- Responsive table container -->
    <div class="overflow-x-auto rounded-md border dark:border-gray-700">
      <!-- Orders table -->
      <DataTable
        class="min-w-full text-sm sm:text-base dark:text-gray-200 dark:bg-gray-800 dark:border-gray-700"
        :value="orders"
        stripedRows
        paginator
        :rows="10"
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
              @click="
                router.push({
                  name: 'orders.view',
                  params: { id: slotProps.data.id },
                })
              "
            >
              <i class="pi pi-eye" title="Ver"></i>
            </button>
          </template>
        </Column>
      </DataTable>
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
import Column from 'primevue/column'
import DataTable from 'primevue/datatable'
import { useToast } from 'primevue/usetoast'
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'

/* ===== Setup ===== */
const router = useRouter()
const toast = useToast()

/* Reactive state */
const orders = ref([])     // Stores fetched orders
const loading = ref(true)  // Loading state for DataTable

/* ===== Lifecycle: on mount ===== */
onMounted(async () => {
  try {
    showOverlay()                      // Displays global loading overlay
    loading.value = true               // Activates table loading indicator
    orders.value = await fetchOrders() // Fetches all orders from API
  } catch(e) {
    // Handles API or network errors
    toast.add({
      severity: 'error',
      summary: 'Erro ao carregar pedidos',
      detail: e.message
    })
  } finally {
    loading.value = false             // Disables table loading indicator
    hideOverlay()                     // Hides global loading overlay
  }
})
</script>