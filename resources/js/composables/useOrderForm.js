import {
  buildOrderPayload,
  calculateItemSubtotal,
  createDefaultItem,
  mapOrderItemsToFormItems,
} from "@/composables/orderFormUtils";
import { useLoadingOverlay } from "@/composables/useLoadingOverlay";
import { formatDate } from "@/helpers";
import { createOrder, getOrderById } from "@/services/orderService";
import { fetchProducts } from "@/services/productService";
import { useToast } from "primevue/usetoast";
import { computed, ref } from "vue";
import { useRoute, useRouter } from "vue-router";

const { showOverlay, hideOverlay } = useLoadingOverlay();

export const useOrderForm = () => {
  const router = useRouter();
  const route = useRoute();
  const toast = useToast();

  const isViewMode = ref(false);
  const orderId = ref(null);
  const customerName = ref("");
  const deliveryDate = ref("");
  const items = ref([createDefaultItem()]);
  const submitted = ref(false);
  const loadingProducts = ref(false);
  const saving = ref(false);
  const acKeys = ref([0]);
  const productSuggestions = ref([[]]);

  const getProductName = (item) => {
    return item?.product?.name || "";
  };

  const getSubtotal = (item) => {
    return calculateItemSubtotal(item, isViewMode.value);
  };

  const total = computed(() => {
    return items.value.reduce((sum, item) => sum + getSubtotal(item), 0);
  });

  const noProductSelected = computed(() => {
    return submitted.value && items.value.every((item) => !item.product?.id);
  });

  const hasInvalidQuantity = computed(() => {
    return items.value.some((item) => item.product_id && Number(item.qty) <= 0);
  });

  const addItem = () => {
    items.value.push(createDefaultItem());
    productSuggestions.value.push([]);
    acKeys.value.push(0);
  };

  const removeItem = (index) => {
    items.value.splice(index, 1);
    productSuggestions.value.splice(index, 1);
    acKeys.value.splice(index, 1);

    if (items.value.length === 0 && !isViewMode.value) {
      addItem();
    }
  };

  const resetItemSelection = (index) => {
    const item = items.value[index];

    if (!item) {
      return;
    }

    item.product = null;
    item.product_id = null;
    item.price = null;
    item.qty = 1;

    productSuggestions.value[index] = [];
    acKeys.value[index] += 1;
  };

  const completeProduct = async (index, { query }) => {
    const normalizedQuery = query.trim();

    if (normalizedQuery.length < 2) {
      productSuggestions.value[index] = [];
      return;
    }

    loadingProducts.value = true;

    try {
      productSuggestions.value[index] = await fetchProducts(normalizedQuery);
    } catch {
      productSuggestions.value[index] = [];

      toast.add({
        severity: "error",
        summary: "Erro ao buscar produtos",
        detail: "Não foi possível carregar as sugestões.",
      });
    } finally {
      loadingProducts.value = false;
    }
  };

  const mergeDuplicatedItem = (index, selectedProduct) => {
    const duplicatedItemIndex = items.value.findIndex((item, itemIndex) => {
      return itemIndex !== index && item.product_id === selectedProduct.id;
    });

    if (duplicatedItemIndex === -1) {
      return false;
    }

    const currentItem = items.value[index];
    const duplicatedItem = items.value[duplicatedItemIndex];
    const nextQty = Number(duplicatedItem.qty) + Number(currentItem.qty || 1);
    const availableStock = Number(selectedProduct.qty_stock);

    if (nextQty > availableStock) {
      toast.add({
        severity: "warn",
        summary: "Estoque máximo atingido",
        detail: `O produto "${selectedProduct.name}" já foi adicionado e possui apenas ${availableStock} unidade${availableStock > 1 ? "s" : ""} em estoque.`,
      });

      resetItemSelection(index);
      return true;
    }

    duplicatedItem.qty = nextQty;
    duplicatedItem.product = selectedProduct;
    duplicatedItem.product_id = selectedProduct.id;
    duplicatedItem.price = Number(selectedProduct.price);

    removeItem(index);

    toast.add({
      severity: "info",
      summary: "Produto atualizado",
      detail: `A quantidade de "${selectedProduct.name}" foi somada no item existente.`,
    });

    return true;
  };

  const onProductSelect = (index, event) => {
    const selectedProduct = event?.value;

    if (!selectedProduct) {
      return;
    }

    if (selectedProduct.qty_stock === 0) {
      toast.add({
        severity: "warn",
        summary: "Estoque indisponível",
        detail: `O produto "${selectedProduct.name}" está sem estoque.`,
      });

      resetItemSelection(index);
      return;
    }

    if (mergeDuplicatedItem(index, selectedProduct)) {
      return;
    }

    const item = items.value[index];

    item.product = selectedProduct;
    item.product_id = selectedProduct.id;
    item.price = Number(selectedProduct.price);
  };

  const saveOrder = async () => {
    submitted.value = true;

    if (!customerName.value || !deliveryDate.value || noProductSelected.value || hasInvalidQuantity.value) {
      return;
    }

    saving.value = true;

    try {
      await createOrder(
        buildOrderPayload({
          customerName: customerName.value,
          deliveryDate: deliveryDate.value,
          items: items.value,
        })
      );

      toast.add({
        severity: "success",
        summary: "Pedido salvo com sucesso",
      });

      router.push({ name: "orders.list" }).catch(() => {});
    } catch (error) {
      if (error.response?.status === 422) {
        const message = error.response.data.errors?.items?.[0] || "Erro de validação.";

        toast.add({
          severity: "warn",
          summary: "Estoque insuficiente",
          detail: message,
        });
      } else {
        toast.add({
          severity: "error",
          summary: "Erro ao salvar pedido",
          detail: "Ocorreu um erro inesperado.",
        });
      }
    } finally {
      saving.value = false;
    }
  };

  const loadOrder = async () => {
    if (!route.params.id) {
      return;
    }

    isViewMode.value = true;
    orderId.value = route.params.id;

    try {
      showOverlay();

      const data = await getOrderById(orderId.value);

      customerName.value = data.customer_name;
      deliveryDate.value = formatDate(data.delivery_date);
      items.value = mapOrderItemsToFormItems(data.items);
      productSuggestions.value = data.items.map(() => []);
      acKeys.value = data.items.map(() => 0);
    } catch (error) {
      toast.add({
        severity: "error",
        summary: "Erro ao carregar pedido",
        detail: error.message,
      });
    } finally {
      hideOverlay();
    }
  };

  return {
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
  };
};
