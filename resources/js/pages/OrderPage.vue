<template>
  <div class="max-w-5xl mx-auto p-4 sm:p-6 bg-white dark:bg-gray-900 shadow rounded-lg transition-colors duration-300">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
      <h1 class="text-xl sm:text-2xl font-semibold text-gray-800 dark:text-gray-100">
        {{ isViewMode ? 'Visualizar' : 'Novo' }} Pedido
      </h1>

      <div class="w-full sm:w-auto flex flex-col sm:flex-row gap-3">
        <RouterLink :to="{ name: 'stock.index' }" class="w-full sm:w-auto flex justify-center gap-2 items-center">
          <button
            class="border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 px-4 py-2 rounded cursor-pointer hover:text-blue-600 dark:hover:text-blue-400 disabled:opacity-50 w-full sm:w-auto flex justify-center gap-2 items-center"
            :disabled="saving"
          >
            <i class="pi pi-box"></i>
            Estoque
          </button>
        </RouterLink>

        <RouterLink :to="{ name: 'orders.list' }" class="w-full sm:w-auto flex justify-center sm:justify-end gap-2 items-center">
          <button
            class="bg-gray-500 text-white px-4 py-2 rounded cursor-pointer hover:bg-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600 disabled:opacity-50 w-full sm:w-auto flex justify-center gap-2 items-center"
            :disabled="saving"
          >
            <i class="pi pi-arrow-left"></i>
            Voltar
          </button>
        </RouterLink>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 mb-6">
      <div>
        <label class="block font-medium mb-1 text-gray-700 dark:text-gray-200">Nome do Cliente</label>
        <InputText
          v-model="customerName"
          type="text"
          fluid
          :invalid="submitted && !customerName"
          placeholder="Nome do Cliente"
          :disabled="isViewMode"
          maxlength="120"
        />
        <Message
          v-if="submitted && !customerName"
          severity="error"
          size="small"
          variant="simple"
        >
          O nome é obrigatório
        </Message>
      </div>

      <div>
        <label class="block font-medium mb-1 text-gray-700 dark:text-gray-200">Data de Entrega</label>
        <DatePicker
          v-model="deliveryDate"
          fluid
          :minDate="new Date()"
          dateFormat="dd/mm/yy"
          placeholder="Data de Entrega"
          :disabled="isViewMode"
        />
        <Message
          v-if="submitted && !deliveryDate"
          severity="error"
          size="small"
          variant="simple"
        >
          A data é obrigatória
        </Message>
      </div>
    </div>

    <OrderItemsSection
      :ac-keys="acKeys"
      :get-product-name="getProductName"
      :get-subtotal="getSubtotal"
      :is-view-mode="isViewMode"
      :items="items"
      :loading-products="loadingProducts"
      :no-product-selected="noProductSelected"
      :product-suggestions="productSuggestions"
      :saving="saving"
      :submitted="submitted"
      :total="total"
      @add-item="addItem"
      @complete-product="completeProduct"
      @remove-item="removeItem"
      @select-product="onProductSelect"
    />

    <div v-if="!isViewMode" class="text-right mt-6">
      <button
        class="bg-green-600 text-white px-6 py-2 rounded cursor-pointer hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600 disabled:opacity-50 w-full sm:w-auto"
        :disabled="saving"
        @click="saveOrder"
      >
        {{ saving ? 'Salvando...' : 'Salvar Pedido' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import OrderItemsSection from '@/components/orders/OrderItemsSection.vue';
import { useOrderForm } from '@/composables/useOrderForm';
import DatePicker from 'primevue/datepicker';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import { onMounted } from 'vue';

const {
  acKeys,
  addItem,
  completeProduct,
  customerName,
  deliveryDate,
  getProductName,
  getSubtotal,
  isViewMode,
  items,
  loadOrder,
  loadingProducts,
  noProductSelected,
  onProductSelect,
  productSuggestions,
  removeItem,
  saveOrder,
  saving,
  submitted,
  total,
} = useOrderForm();

onMounted(() => {
  loadOrder();
});
</script>