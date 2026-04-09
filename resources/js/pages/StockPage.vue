<template>
    <div
        class="max-w-5xl mx-auto p-4 sm:p-6 bg-white dark:bg-gray-900 shadow rounded-lg transition-colors duration-300"
    >
        <div
            class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6"
        >
            <div>
                <h1 class="text-xl sm:text-2xl font-semibold text-gray-800 dark:text-gray-100">
                    Estoque
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Visão atual dos produtos, preços e quantidades disponíveis.
                </p>
            </div>

            <RouterLink
                :to="{ name: 'orders.list' }"
                class="w-full sm:w-auto bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition flex justify-center gap-2 items-center"
            >
                <i class="pi pi-arrow-left" aria-hidden="true" />
                Voltar para pedidos
            </RouterLink>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
            <article class="border rounded-md p-4 dark:border-gray-700 dark:bg-gray-800">
                <p
                    class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400"
                >
                    Produtos
                </p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mt-2">
                    {{ summary.totalProducts }}
                </p>
            </article>

            <article class="border rounded-md p-4 dark:border-gray-700 dark:bg-gray-800">
                <p
                    class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400"
                >
                    Baixo estoque
                </p>
                <p class="text-2xl font-semibold text-amber-600 dark:text-amber-400 mt-2">
                    {{ summary.lowStockProducts }}
                </p>
            </article>

            <article class="border rounded-md p-4 dark:border-gray-700 dark:bg-gray-800">
                <p
                    class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400"
                >
                    Sem estoque
                </p>
                <p class="text-2xl font-semibold text-red-600 dark:text-red-400 mt-2">
                    {{ summary.outOfStockProducts }}
                </p>
            </article>
        </div>

        <div v-if="!loading && products.length > 0" class="md:hidden space-y-3">
            <article
                v-for="product in products"
                :key="product.id"
                class="border rounded-md p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800"
            >
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p
                            class="text-base font-semibold text-gray-900 dark:text-gray-100 break-words"
                        >
                            {{ product.name }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            {{ formatPrice(product.price) }}
                        </p>
                    </div>

                    <Tag
                        :severity="getStockSeverity(product.qty_stock)"
                        :value="getStockLabel(product.qty_stock)"
                        rounded
                    />
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div>
                        <p
                            class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400"
                        >
                            ID
                        </p>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ product.id }}
                        </p>
                    </div>

                    <div>
                        <p
                            class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400"
                        >
                            Quantidade
                        </p>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ product.qty_stock }}
                        </p>
                    </div>
                </div>
            </article>
        </div>

        <div class="hidden md:block overflow-x-auto rounded-md border dark:border-gray-700">
            <DataTable
                :value="products"
                :loading="loading"
                striped-rows
                removable-sort
                sort-field="qty_stock"
                :sort-order="1"
                class="min-w-full text-sm sm:text-base dark:text-gray-200 dark:bg-gray-800 dark:border-gray-700"
            >
                <Column field="id" header="ID" sortable />

                <Column field="name" sortable>
                    <template #header>
                        <span class="font-bold dark:text-gray-100">Produto</span>
                    </template>
                </Column>

                <Column field="price" sortable>
                    <template #header>
                        <span class="font-bold dark:text-gray-100">Preço</span>
                    </template>
                    <template #body="slotProps">
                        {{ formatPrice(slotProps.data.price) }}
                    </template>
                </Column>

                <Column field="qty_stock" sortable>
                    <template #header>
                        <span class="font-bold dark:text-gray-100 whitespace-nowrap"
                            >Quantidade</span
                        >
                    </template>
                </Column>

                <Column>
                    <template #header>
                        <span class="font-bold dark:text-gray-100">Status</span>
                    </template>
                    <template #body="slotProps">
                        <Tag
                            :severity="getStockSeverity(slotProps.data.qty_stock)"
                            :value="getStockLabel(slotProps.data.qty_stock)"
                            rounded
                        />
                    </template>
                </Column>
            </DataTable>
        </div>

        <div v-if="loading" class="sm:hidden text-gray-500 dark:text-gray-400 text-center text-sm">
            Carregando estoque...
        </div>

        <div
            v-if="products.length === 0 && !loading"
            class="text-gray-500 dark:text-gray-400 mt-4 text-center text-sm sm:text-base"
        >
            Nenhum produto encontrado.
        </div>
    </div>
</template>

<script setup>
import { useAppToast } from "@/composables/useAppToast";
import { useLoadingOverlay } from "@/composables/useLoadingOverlay";
import { formatPrice } from "@/helpers";
import { fetchStockProducts } from "@/services/productService";
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import Tag from "primevue/tag";
import { computed, onMounted, ref } from "vue";

const lowStockThreshold = 5;

const { hideOverlay, showOverlay } = useLoadingOverlay();
const { addToast } = useAppToast();

const loading = ref(true);
const products = ref([]);

const summary = computed(() => {
    const totalProducts = products.value.length;
    const outOfStockProducts = products.value.filter(
        (product) => Number(product.qty_stock) === 0,
    ).length;
    const lowStockProducts = products.value.filter((product) => {
        const stock = Number(product.qty_stock);

        return stock > 0 && stock <= lowStockThreshold;
    }).length;

    return {
        totalProducts,
        lowStockProducts,
        outOfStockProducts,
    };
});

const getStockLabel = (qtyStock) => {
    const stock = Number(qtyStock);

    if (stock === 0) {
        return "Sem estoque";
    }

    if (stock <= lowStockThreshold) {
        return "Baixo estoque";
    }

    return "Disponível";
};

const getStockSeverity = (qtyStock) => {
    const stock = Number(qtyStock);

    if (stock === 0) {
        return "danger";
    }

    if (stock <= lowStockThreshold) {
        return "warn";
    }

    return "success";
};

onMounted(async () => {
    try {
        showOverlay();
        loading.value = true;
        products.value = await fetchStockProducts();
    } catch (error) {
        const errorMessage =
            error instanceof Error ? error.message : "Erro inesperado ao carregar estoque.";

        addToast({
            severity: "error",
            summary: "Erro ao carregar estoque",
            detail: errorMessage,
        });
    } finally {
        loading.value = false;
        hideOverlay();
    }
});
</script>
