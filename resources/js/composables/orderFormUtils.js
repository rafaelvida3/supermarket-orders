export const createDefaultItem = () => {
  return {
    product_id: null,
    qty: 1,
    price: null,
    product: null,
  };
};

export const calculateItemSubtotal = (item, isViewMode = false) => {
  if (isViewMode) {
    return Number(item?.subtotal) || 0;
  }

  const price = Number(item?.price) || 0;
  const qty = Number(item?.qty) || 0;

  return price * qty;
};

export const buildOrderPayload = ({ customerName, deliveryDate, items }) => {
  return {
    customer_name: customerName,
    delivery_date: deliveryDate,
    items: items
      .filter((item) => item.product?.id)
      .map((item) => ({
        product_id: Number(item.product.id),
        qty: Number(item.qty),
      })),
  };
};

export const mapOrderItemsToFormItems = (items = []) => {
  return items.map((item) => ({
    product_id: item?.product_id,
    qty: item?.qty,
    price: item?.unit_price,
    subtotal: item?.subtotal,
    product: item?.product,
  }));
};
