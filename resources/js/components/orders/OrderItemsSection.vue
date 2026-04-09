<template>
    <section>
        <h2 class="text-lg font-semibold mb-2 text-gray-800 dark:text-gray-100">Itens do Pedido</h2>

        <div class="hidden md:block overflow-x-auto rounded-md border dark:border-gray-700">
            <table
                class="w-full min-w-[600px] border-collapse text-sm sm:text-base dark:text-gray-100"
            >
                <thead
                    class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-b dark:border-gray-700"
                >
                    <tr>
                        <th class="text-left p-2">Produto</th>
                        <th class="text-center p-2 w-32">Quantidade</th>
                        <th class="text-right p-2 w-32">Subtotal</th>
                        <th v-if="!isViewMode" class="p-2 w-10" />
                    </tr>
                </thead>

                <tbody>
                    <tr
                        v-for="(item, index) in items"
                        :key="`desktop-${index}`"
                        class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
                    >
                        <td class="p-2 align-top min-w-[200px]">
                            <template v-if="isViewMode">
                                <div class="border rounded p-2 bg-gray-50 dark:bg-gray-800/60">
                                    {{ getProductName(item) }}
                                </div>
                            </template>

                            <IconField v-else class="w-full">
                                <AutoComplete
                                    :key="`desktop-ac-${index}-${acKeys[index]}`"
                                    v-model="item.product"
                                    :suggestions="productSuggestions[index]"
                                    option-label="name"
                                    force-selection
                                    placeholder="Digite para buscar..."
                                    input-class="border rounded p-2 w-full"
                                    :delay="250"
                                    :min-length="2"
                                    :disabled="isViewMode"
                                    fluid
                                    @complete="(event) => emit('complete-product', index, event)"
                                    @item-select="(event) => emit('select-product', index, event)"
                                >
                                    <template #chip="slotProps">
                                        <span>{{ slotProps.value?.name || "" }}</span>
                                    </template>
                                </AutoComplete>

                                <Transition name="fade">
                                    <InputIcon
                                        v-if="loadingProducts"
                                        class="pi pi-spin pi-spinner"
                                    />
                                </Transition>
                            </IconField>
                        </td>

                        <td
                            class="p-2 text-center align-top"
                            @mousedown.capture="(event) => handleStockLimitClick(event, item)"
                            @keydown.capture="(event) => handleStockLimitClick(event, item)"
                        >
                            <Transition name="fade">
                                <div v-if="item.product_id" class="flex flex-col items-center">
                                    <template v-if="isViewMode">
                                        <div
                                            class="border rounded p-2 min-w-16 text-center bg-gray-50 dark:bg-gray-800/60"
                                        >
                                            {{ item.qty }}
                                        </div>
                                    </template>

                                    <InputNumber
                                        v-else
                                        v-model="item.qty"
                                        show-buttons
                                        button-layout="horizontal"
                                        increment-button-icon="pi pi-plus"
                                        decrement-button-icon="pi pi-minus"
                                        input-class="text-center w-16"
                                        :min="1"
                                        :max="item.product?.qty_stock || 1"
                                        :disabled="isViewMode"
                                    />

                                    <Message
                                        v-if="submitted && Number(item.qty) <= 0"
                                        severity="error"
                                        size="small"
                                        variant="simple"
                                    >
                                        Informe a quantidade
                                    </Message>

                                    <Message
                                        v-if="item.product_id && !isViewMode"
                                        severity="secondary"
                                        size="small"
                                        variant="simple"
                                        class="mt-2"
                                    >
                                        Estoque total: {{ item.product?.qty_stock }}
                                    </Message>
                                </div>
                            </Transition>
                        </td>

                        <td class="p-2 text-right">
                            {{ item?.price ? formatPrice(getSubtotal(item)) : "" }}
                            <Message v-if="!isViewMode" size="small" variant="simple" class="mt-2">
                                &nbsp;
                            </Message>
                        </td>

                        <td v-if="!isViewMode" class="px-6 py-2 text-center">
                            <button
                                v-if="item.product_id !== null"
                                type="button"
                                class="cursor-pointer text-red-500 dark:text-red-400"
                                title="Excluir"
                                aria-label="Excluir item"
                                @click="emit('remove-item', index)"
                            >
                                <i class="pi pi-times" aria-hidden="true" />
                            </button>
                            <Message size="small" variant="simple" class="mt-2"> &nbsp; </Message>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="md:hidden space-y-3">
            <div
                v-for="(item, index) in items"
                :key="`mobile-${index}`"
                class="rounded-md border p-3 space-y-3 dark:border-gray-700"
            >
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div
                            class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400"
                        >
                            Item {{ index + 1 }}
                        </div>
                    </div>

                    <button
                        v-if="!isViewMode && item.product_id !== null"
                        type="button"
                        class="text-red-500 dark:text-red-400 cursor-pointer"
                        aria-label="Remover item"
                        @click="emit('remove-item', index)"
                    >
                        <i class="pi pi-times" aria-hidden="true" />
                    </button>
                </div>

                <div>
                    <label class="block font-medium mb-1 text-gray-700 dark:text-gray-200"
                        >Produto</label
                    >

                    <template v-if="isViewMode">
                        <div
                            class="border rounded p-2 bg-gray-50 dark:bg-gray-800/60 dark:border-gray-700"
                        >
                            {{ getProductName(item) }}
                        </div>
                    </template>

                    <IconField v-else class="w-full">
                        <AutoComplete
                            :key="`mobile-ac-${index}-${acKeys[index]}`"
                            v-model="item.product"
                            :suggestions="productSuggestions[index]"
                            option-label="name"
                            force-selection
                            placeholder="Digite para buscar..."
                            input-class="border rounded p-2 w-full"
                            :delay="250"
                            :min-length="2"
                            :disabled="isViewMode"
                            fluid
                            @complete="(event) => emit('complete-product', index, event)"
                            @item-select="(event) => emit('select-product', index, event)"
                        >
                            <template #chip="slotProps">
                                <span>{{ slotProps.value?.name || "" }}</span>
                            </template>
                        </AutoComplete>

                        <Transition name="fade">
                            <InputIcon v-if="loadingProducts" class="pi pi-spin pi-spinner" />
                        </Transition>
                    </IconField>
                </div>

                <div v-if="item.product_id" class="grid grid-cols-1 gap-3">
                    <div
                        @mousedown.capture="(event) => handleStockLimitClick(event, item)"
                        @keydown.capture="(event) => handleStockLimitClick(event, item)"
                    >
                        <label class="block font-medium mb-1 text-gray-700 dark:text-gray-200"
                            >Quantidade</label
                        >

                        <template v-if="isViewMode">
                            <div
                                class="border rounded p-2 text-center bg-gray-50 dark:bg-gray-800/60 dark:border-gray-700"
                            >
                                {{ item.qty }}
                            </div>
                        </template>

                        <InputNumber
                            v-else
                            v-model="item.qty"
                            show-buttons
                            button-layout="horizontal"
                            increment-button-icon="pi pi-plus"
                            decrement-button-icon="pi pi-minus"
                            input-class="text-center w-full"
                            class="w-full"
                            :min="1"
                            :max="item.product?.qty_stock || 1"
                            :disabled="isViewMode"
                            fluid
                        />

                        <Message
                            v-if="submitted && Number(item.qty) <= 0"
                            severity="error"
                            size="small"
                            variant="simple"
                        >
                            Informe a quantidade
                        </Message>

                        <Message
                            v-if="item.product_id && !isViewMode"
                            severity="secondary"
                            size="small"
                            variant="simple"
                            class="mt-2"
                        >
                            Estoque total: {{ item.product?.qty_stock }}
                        </Message>
                    </div>

                    <div>
                        <label class="block font-medium mb-1 text-gray-700 dark:text-gray-200"
                            >Subtotal</label
                        >
                        <div
                            class="border rounded p-2 text-right bg-gray-50 dark:bg-gray-800/60 dark:border-gray-700"
                        >
                            {{ item?.price ? formatPrice(getSubtotal(item)) : "-" }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Message
            v-if="noProductSelected"
            severity="error"
            size="small"
            variant="simple"
            class="mt-3"
        >
            É necessário selecionar pelo menos um produto
        </Message>

        <button
            v-if="!isViewMode"
            class="bg-blue-500 text-white px-4 py-2 rounded mt-3 cursor-pointer flex justify-center sm:justify-start gap-2 items-center hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-500 disabled:opacity-50 w-full sm:w-auto"
            :disabled="saving"
            @click="emit('add-item')"
        >
            <i class="pi pi-plus" />
            Adicionar Produto
        </button>

        <div
            class="text-right mt-4 text-base sm:text-lg font-semibold text-gray-800 dark:text-gray-100"
        >
            Total: {{ formatPrice(total) }}
        </div>
    </section>
</template>

<script setup>
import { useAppToast } from "@/composables/useAppToast";
import { formatPrice } from "@/helpers";
import AutoComplete from "primevue/autocomplete";
import IconField from "primevue/iconfield";
import InputIcon from "primevue/inputicon";
import InputNumber from "primevue/inputnumber";
import Message from "primevue/message";

defineProps({
    acKeys: {
        type: Array,
        required: true,
    },
    getProductName: {
        type: Function,
        required: true,
    },
    getSubtotal: {
        type: Function,
        required: true,
    },
    isViewMode: {
        type: Boolean,
        required: true,
    },
    items: {
        type: Array,
        required: true,
    },
    loadingProducts: {
        type: Boolean,
        required: true,
    },
    noProductSelected: {
        type: Boolean,
        required: true,
    },
    productSuggestions: {
        type: Array,
        required: true,
    },
    saving: {
        type: Boolean,
        required: true,
    },
    submitted: {
        type: Boolean,
        required: true,
    },
    total: {
        type: Number,
        required: true,
    },
});

const emit = defineEmits(["add-item", "complete-product", "remove-item", "select-product"]);

const { addToast } = useAppToast();

const handleStockLimitClick = (event, item) => {
    const wrapper = event.currentTarget?.querySelector(".p-inputnumber");

    if (!wrapper) {
        return;
    }

    const plusButton = wrapper.querySelector(".p-inputnumber-increment-button");
    const qty = Number(item?.qty) || 0;
    const qtyStock = Number(item?.product?.qty_stock) || 0;

    if (plusButton && plusButton.classList.contains("p-disabled") && qty >= qtyStock) {
        addToast({
            severity: "warn",
            summary: "Estoque máximo atingido",
            detail: `Disponível: ${qtyStock} unidade${qtyStock > 1 ? "s" : ""}.`,
        });
    }
};
</script>
