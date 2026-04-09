<template>
    <div
        class="max-w-5xl mx-auto p-4 sm:p-6 bg-white dark:bg-gray-900 shadow rounded-lg transition-colors duration-300"
    >
        <div
            class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6"
        >
            <h1 class="text-xl sm:text-2xl font-semibold text-gray-800 dark:text-gray-100">
                Pedidos
            </h1>

            <div class="w-full sm:w-auto flex flex-col sm:flex-row gap-3">
                <RouterLink
                    :to="{ name: 'stock.index' }"
                    class="w-full sm:w-auto border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 px-4 py-2 rounded hover:text-blue-600 dark:hover:text-blue-400 transition flex justify-center gap-2 items-center"
                >
                    <i class="pi pi-box" aria-hidden="true" />
                    Ver Estoque
                </RouterLink>

                <RouterLink
                    :to="{ name: 'orders.new' }"
                    class="w-full sm:w-auto bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition flex justify-center sm:justify-between gap-2 items-center"
                >
                    <i class="pi pi-plus" aria-hidden="true" />
                    Novo Pedido
                </RouterLink>
            </div>
        </div>

        <div v-if="!loading && paginatedOrders.length > 0" class="md:hidden space-y-3">
            <article
                v-for="order in paginatedOrders"
                :key="order.id"
                class="border rounded-md p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800"
            >
                <div class="space-y-3">
                    <div>
                        <p
                            class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400"
                        >
                            Data do Pedido
                        </p>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ formatDate(order.created_at, "DD/MM/YYYY HH:mm") }}
                        </p>
                    </div>

                    <div>
                        <p
                            class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400"
                        >
                            Cliente
                        </p>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 break-words">
                            {{ order.customer_name }}
                        </p>
                    </div>

                    <div>
                        <p
                            class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400"
                        >
                            Data de Entrega
                        </p>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ formatDate(order.delivery_date) }}
                        </p>
                    </div>

                    <div>
                        <p
                            class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400"
                        >
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
                    <i class="pi pi-eye" aria-hidden="true" />
                    Ver pedido
                </button>
            </article>

            <div v-if="totalPages > 1" class="flex items-center justify-between gap-3 pt-2">
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

        <div class="hidden md:block overflow-x-auto rounded-md border dark:border-gray-700">
            <DataTable
                class="min-w-full text-sm sm:text-base dark:text-gray-200 dark:bg-gray-800 dark:border-gray-700"
                :value="orders"
                :loading="loading"
                striped-rows
                paginator
                :rows="rowsPerPage"
                sort-field="created_at"
                :sort-order="-1"
            >
                <Column field="created_at" sortable>
                    <template #header>
                        <span class="font-bold dark:text-gray-100 whitespace-nowrap"
                            >Data do Pedido</span
                        >
                    </template>
                    <template #body="slotProps">
                        {{ formatDate(slotProps.data.created_at, "DD/MM/YYYY HH:mm") }}
                    </template>
                </Column>

                <Column field="customer_name" sortable>
                    <template #header>
                        <span class="font-bold dark:text-gray-100">Cliente</span>
                    </template>
                    <template #body="slotProps">
                        <span class="whitespace-nowrap sm:whitespace-normal break-words">
                            {{ slotProps.data.customer_name }}
                        </span>
                    </template>
                </Column>

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

                <Column field="total" sortable>
                    <template #header>
                        <span class="font-bold dark:text-gray-100">Total</span>
                    </template>
                    <template #body="slotProps">
                        {{ formatPrice(slotProps.data.total) }}
                    </template>
                </Column>

                <Column>
                    <template #body="slotProps">
                        <button
                            type="button"
                            class="cursor-pointer text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition"
                            :aria-label="`Ver pedido ${slotProps.data.id}`"
                            @click="goToOrderDetails(slotProps.data.id)"
                        >
                            <i class="pi pi-eye" aria-hidden="true" />
                        </button>
                    </template>
                </Column>
            </DataTable>
        </div>

        <div v-if="loading" class="sm:hidden text-gray-500 dark:text-gray-400 text-center text-sm">
            Carregando pedidos...
        </div>

        <div
            v-if="orders.length === 0 && !loading"
            class="text-gray-500 dark:text-gray-400 mt-4 text-center text-sm sm:text-base"
        >
            Nenhum pedido encontrado.
        </div>
    </div>
</template>

<script setup>
import { useAppToast } from "@/composables/useAppToast";
import { useLoadingOverlay } from "@/composables/useLoadingOverlay";
import { formatDate, formatPrice } from "@/helpers";
import { fetchOrders } from "@/services/orderService";
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import { computed, onMounted, ref, watch } from "vue";
import { useRouter } from "vue-router";

const rowsPerPage = 10;

const { showOverlay, hideOverlay } = useLoadingOverlay();
const { addToast } = useAppToast();
const router = useRouter();

const orders = ref([]);
const loading = ref(true);
const currentPage = ref(1);

const totalPages = computed(() => {
    return Math.max(1, Math.ceil(orders.value.length / rowsPerPage));
});

const paginatedOrders = computed(() => {
    const startIndex = (currentPage.value - 1) * rowsPerPage;
    const endIndex = startIndex + rowsPerPage;

    return orders.value.slice(startIndex, endIndex);
});

const goToOrderDetails = (orderId) => {
    router.push({
        name: "orders.view",
        params: { id: orderId },
    });
};

const goToPreviousPage = () => {
    if (currentPage.value > 1) {
        currentPage.value -= 1;
    }
};

const goToNextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value += 1;
    }
};

watch(totalPages, (newTotalPages) => {
    if (currentPage.value > newTotalPages) {
        currentPage.value = newTotalPages;
    }
});

onMounted(async () => {
    try {
        showOverlay();
        loading.value = true;
        orders.value = await fetchOrders();
    } catch (error) {
        const errorMessage =
            error instanceof Error ? error.message : "Erro inesperado ao carregar pedidos.";

        addToast({
            severity: "error",
            summary: "Erro ao carregar pedidos",
            detail: errorMessage,
        });
    } finally {
        loading.value = false;
        hideOverlay();
    }
});
</script>
