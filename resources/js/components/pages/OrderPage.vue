<template>
  <!-- Main responsive container -->
  <div class="max-w-5xl mx-auto p-4 sm:p-6 bg-white dark:bg-gray-900 shadow rounded-lg transition-colors duration-300">
    
    <!-- ================= HEADER ================= -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
      <!-- Dynamic title depending on mode -->
      <h1 class="text-xl sm:text-2xl font-semibold text-gray-800 dark:text-gray-100">
        {{ isViewMode ? 'Visualizar' : 'Novo' }} Pedido
      </h1>

      <!-- Back button (router navigation) -->
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

    <!-- ================= CUSTOMER AND DELIVERY DATA ================= -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 mb-6">
      <!-- Customer name field -->
      <div>
        <label class="block font-medium mb-1 text-gray-700 dark:text-gray-200">Nome do Cliente</label>
        <InputText
          type="text"
          fluid
          v-model="customerName"
          :invalid="submitted && !customerName"
          placeholder="Nome do Cliente"
          :disabled="isViewMode"
          maxlength="120"
        />
        <!-- Error message when customer name is missing -->
        <Message
          v-if="submitted && !customerName"
          severity="error"
          size="small"
          variant="simple"
        >
          O nome é obrigatório
        </Message>
      </div>

      <!-- Delivery date picker -->
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
        <!-- Error message when date is missing -->
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

    <!-- ================= ORDER ITEMS ================= -->
    <h2 class="text-lg font-semibold mb-2 text-gray-800 dark:text-gray-100">Itens do Pedido</h2>

    <!-- Responsive table for order items -->
    <div class="overflow-x-auto rounded-md border dark:border-gray-700">
      <table class="w-full min-w-[600px] border-collapse text-sm sm:text-base dark:text-gray-100">
        <thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-b dark:border-gray-700">
          <tr>
            <th class="text-left p-2">Produto</th>
            <th class="text-center p-2 w-32">Quantidade</th>
            <th class="text-right p-2 w-32">Subtotal</th>
            <th class="p-2 w-10" v-if="!isViewMode"></th>
          </tr>
        </thead>

        <tbody>
          <!-- Loop through all order items -->
          <tr
            v-for="(item, index) in items"
            :key="index"
            class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
          >
            <!-- Product selector (AutoComplete) -->
            <td class="p-2 align-top min-w-[200px]">
              <IconField class="w-full">
                <AutoComplete
                  v-model="item.product"
                  :key="`ac-${index}-${acKeys[index]}`"
                  ref="autocompleteRefs"
                  :suggestions="productSuggestions[index]"
                  optionLabel="name"
                  forceSelection
                  placeholder="Digite para buscar..."
                  @complete="(e) => completeProduct(index, e)"
                  @item-select="(e) => onProductSelect(index, e)"
                  inputClass="border rounded p-2 w-full"
                  :delay="250"
                  :minLength="2"
                  :disabled="isViewMode"
                  fluid
                >
                    <!-- Chip template for selected product -->
                  <template #chip="slotProps">
                    <span>{{ slotProps.value?.name || '' }}</span>
                  </template>
                </AutoComplete>
                <!-- Loading spinner while fetching products -->
                <Transition name="fade">
                  <InputIcon v-if="loadingProducts" class="pi pi-spin pi-spinner" />
                </Transition>
              </IconField>
            </td>

            <!-- Quantity field -->
            <td
              class="p-2 text-center align-top"
              @mousedown.capture="(e) => checkStock(e, item)"
              @keydown.capture="(e) => checkStock(e, item)"
            >
              <Transition name="fade">
                <div v-if="item.product_id" class="flex flex-col items-center">
                  <InputNumber
                    v-model="item.qty"
                    showButtons
                    buttonLayout="horizontal"
                    incrementButtonIcon="pi pi-plus"
                    decrementButtonIcon="pi pi-minus"
                    inputClass="text-center w-16"
                    :min="1"
                    :max="item.product?.qty_stock"
                    :disabled="isViewMode"
                  />
                  <!-- Error message shown when quantity is missing or zero -->
                  <Message
                    v-if="submitted && Number(item.qty) <= 0"
                    severity="error"
                    size="small"
                    variant="simple"
                  >
                    Informe a quantidade
                  </Message>
                  <!-- Shows stock info for user reference -->
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

            <!-- Subtotal column -->
            <td class="p-2 text-right">
              {{ item?.price ? formatPrice(getSubtotal(item)) : '' }}
              <Message v-if="!isViewMode" size="small" variant="simple" class="mt-2">&nbsp;</Message>
            </td>

            <!-- Remove item button -->
            <td
              v-if="!isViewMode"
              class="px-6 py-2 text-center cursor-pointer"
              :class="item.product_id !== null ? 'cursor-pointer' : ''"
              @click="item.product_id !== null && removeItem(index)"
              :title="item.product_id !== null ? 'Excluir' : ''"
            >
              <Transition name="fade">
                <i v-if="item.product_id !== null" class="pi pi-times text-red-500 dark:text-red-400"></i>
              </Transition>
              <Message size="small" variant="simple" class="mt-2">&nbsp;</Message>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- ================= VALIDATION MESSAGE ================= -->
    <Message v-if="noProductSelected" severity="error" size="small" variant="simple" class="mt-3">
      É necessário selecionar pelo menos um produto
    </Message>

    <!-- ================= ADD PRODUCT BUTTON ================= -->
    <button
      v-if="!isViewMode"
      @click="addItem"
      class="bg-blue-500 text-white px-4 py-2 rounded mt-3 cursor-pointer flex justify-center sm:justify-start gap-2 items-center hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-500 disabled:opacity-50 w-full sm:w-auto"
      :disabled="saving"
    >
      <i class="pi pi-plus"></i>
      Adicionar Produto
    </button>

    <!-- ================= ORDER TOTAL ================= -->
    <div class="text-right mt-4 text-base sm:text-lg font-semibold text-gray-800 dark:text-gray-100">
      Total: {{ formatPrice(total) }}
    </div>

    <!-- ================= SAVE BUTTON ================= -->
    <div v-if="!isViewMode" class="text-right mt-6">
      <button
        @click="saveOrder"
        :disabled="saving"
        class="bg-green-600 text-white px-6 py-2 rounded cursor-pointer hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600 disabled:opacity-50 w-full sm:w-auto"
      >
        {{ saving ? 'Salvando...' : 'Salvar Pedido' }}
      </button>
    </div>
  </div>
</template>

<script setup>
/* ===== Imports ===== */
import { formatDate, formatPrice } from '@/helpers'; // Utility functions for formatting
import { createOrder, getOrderById } from '@/services/ordersService'; // API calls for orders
import { fetchProducts } from '@/services/productsService'; // API call for product suggestions

/* PrimeVue components */
import AutoComplete from 'primevue/autocomplete'
import DatePicker from 'primevue/datepicker'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'
import InputNumber from 'primevue/inputnumber'
import InputText from 'primevue/inputtext'
import Message from 'primevue/message'
import { useToast } from 'primevue/usetoast'

/* Vue core utilities */
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

/* ===== Setup ===== */
const router = useRouter()
const route = useRoute()
const toast = useToast()

/* Determines if component is in view-only mode */
const isViewMode = ref(false)

/* Current order ID (used when viewing an existing order) */
const orderId = ref(null)

/* ===== Main state ===== */
const customerName = ref('')
const deliveryDate = ref('')
const total = ref(0)
const autocompleteRefs = ref([])
const submitted = ref(false)

/**
 * Returns a default empty order item.
 * @returns {{ product_id: number|null, qty: number, price: number|null, product: object|null }}
 */
const defaultItem = () => ({
  product_id: null,
  qty: 1,
  price: null,
  product: null
})

/* Reactive list of items */
const items = ref([defaultItem()])

/* Loading and control flags */
const loadingProducts = ref(false)
const saving = ref(false)

/* Keys to force re-render AutoComplete when clearing inputs */
const acKeys = ref([0])

/* Product suggestions per row (index-based array of arrays) */
const productSuggestions = ref([])

/* ===== Add and remove items ===== */

/**
 * Adds a new empty item and updates suggestion arrays.
 * @returns {void}
 */
const addItem = () => {
  items.value.push(defaultItem())
  productSuggestions.value.push([])
  acKeys.value.push(0)
}

/**
 * Removes an item and synchronizes related arrays.
 * @param {number} i - Index of the item to remove.
 * @returns {void}
 */
const removeItem = (i) => {
  items.value.splice(i, 1)
  productSuggestions.value.splice(i, 1)
  acKeys.value.splice(i, 1)
}

/**
 * Fetches product suggestions from API for a specific row.
 * @param {number} index - Index of the row.
 * @param {{ query: string }} e - AutoComplete event containing search query.
 * @returns {Promise<void>}
 */
const completeProduct = async (index, { query }) => {
  loadingProducts.value = true;
  const list = await fetchProducts(query)  // sua função já existente
  loadingProducts.value = false;
  productSuggestions.value[index] = list
}

/**
 * Handles selection of a product from suggestions.
 * @param {number} index - Row index.
 * @param {{ value: object }} event - Selected product event.
 * @returns {void}
 */
const onProductSelect = (index, event) => {
  const selected = event?.value
  if (!selected) return

  /* Warns if product is out of stock */
  if (selected.qty_stock === 0) {
    toast.add({
      severity: 'warn',
      summary: 'Estoque indisponível',
      detail: `O produto "${selected.name}" está sem estoque.`
    })

    /* Clears invalid selection */
    const it = items.value[index]
    it.product = null
    it.product_id = null
    it.price = null

    /* Clears suggestions and forces input reset */
    productSuggestions.value[index] = []
    acKeys.value[index]++ 
    return
  }

  /* Valid selection */
  const it = items.value[index]
  it.product = selected
  it.product_id = selected.id
  it.price = parseFloat(selected.price)
}

/* ===== Watchers ===== */

/* Automatically updates order total whenever item list changes */
watch(items, (newItems) => {
  if (!isViewMode.value) {
    total.value = newItems.reduce((sum, item) => sum + getSubtotal(item), 0)
  }
}, { deep: true })

/**
 * Computes the item subtotal.
 * - In view mode, uses the precomputed `subtotal` coming from the backend.
 * - In edit mode, multiplies `price * qty`.
 *
 * @param {{ price?: number|null, qty?: number|null, subtotal?: number|null }} item - Order item.
 * @returns {number} Subtotal value for the given item.
 */
const getSubtotal = (item) => {
  if (isViewMode.value) {
    return Number(item?.subtotal) || 0
  }

  const price = Number(item?.price) || 0
  const qty = Number(item?.qty) || 0
  return price * qty
}

/* ===== Stock validation ===== */

/**
 * Checks if the user tried to exceed available stock.
 * @param {MouseEvent|KeyboardEvent} e - Event from input interaction.
 * @param {object} item - Current item being edited.
 * @returns {void}
 */
const checkStock = (e, item) => {
  const wrapper = e.target.closest('.p-inputnumber')
  if (!wrapper) return

  const plusButton = wrapper.querySelector('.p-inputnumber-increment-button')

  const qty = item?.qty;
  const qty_stock = item.product?.qty_stock;

  /* If increment button is disabled, user hit stock limit */
  if (plusButton && plusButton.classList.contains('p-disabled') && qty >= qty_stock) {
    toast.add({
      severity: 'warn',
      summary: 'Estoque máximo atingido',
      detail: `Disponível: ${qty_stock} unidade${qty_stock > 1 ? 's' : ''}`
    })
  }
}

/* Computed flag for missing product selection */
const noProductSelected = computed(() =>
  submitted.value && items.value.filter(i => i.product !== null).length === 0
)

/**
 * Saves the order to the backend.
 * Performs validation and handles API response.
 * @returns {Promise<void>}
 */
const saveOrder = async () => {
  submitted.value = true
  if (!customerName.value || !deliveryDate.value || noProductSelected.value) return;
  
  const payload = {
    customer_name: customerName.value,
    delivery_date: deliveryDate.value,
    items: items.value.filter(i => i.product?.id),
  }

  saving.value = true
  try {
    await createOrder(payload)
    toast.add({
      severity: 'success',
      summary: 'Pedido salvo com sucesso'
    })
    router.push({ name: 'orders.list'}).catch(_ => {})
  } catch (err) {
    /* Validation error (e.g. insufficient stock) */
    if (err.response?.status === 422) {
      const msg = err.response.data.errors?.items?.[0] || 'Erro de validação.'
      toast.add({
        severity: 'warn',
        summary: 'Estoque insuficiente',
        detail: msg
      })
    } else {
      /* Generic error */
      toast.add({
        severity: 'error',
        summary: 'Erro ao salvar pedido',
        detail: 'Ocorreu um erro inesperado.'
      })
    }
  } finally {
    saving.value = false
  }
}

/* ===== Lifecycle: on mount ===== */
onMounted(async () => {
  /* If there is an ID in the route, open order in view mode */
  if (route.params.id) {
    isViewMode.value = true
    orderId.value = route.params.id

    try {
      showOverlay();
      const data = await getOrderById(orderId.value)

      /* Populate fields with loaded data */
      customerName.value = data.customer_name
      deliveryDate.value = formatDate(data.delivery_date)
      items.value = data.items.map(item => ({
        product_id: item?.product_id,
        qty: item?.qty,
        price: item?.unit_price,
        subtotal: item?.subtotal,
        product: item?.product
      }));
      total.value = data.total
    } catch (err) {
      toast.add({
        severity: 'error',
        summary: 'Erro ao carregar pedido',
        detail: err.message
      })
    } finally {
      hideOverlay()
    }
  }
})
</script>