import dayjs from "dayjs";

const iso_date_pattern = /^\d{4}-\d{2}-\d{2}$/;

const resolveProductId = (item) => {
    const productId = Number(item?.product_id ?? item?.product?.id);

    return Number.isInteger(productId) && productId > 0 ? productId : null;
};

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

export const normalizeDeliveryDate = (deliveryDate) => {
    if (!deliveryDate) {
        return "";
    }

    if (deliveryDate instanceof Date) {
        return dayjs(deliveryDate).format("YYYY-MM-DD");
    }

    if (typeof deliveryDate === "string" && iso_date_pattern.test(deliveryDate)) {
        return deliveryDate;
    }

    const normalizedDate = dayjs(deliveryDate);

    return normalizedDate.isValid() ? normalizedDate.format("YYYY-MM-DD") : "";
};

export const parseDeliveryDateFromApi = (deliveryDate) => {
    if (!deliveryDate || typeof deliveryDate !== "string" || !iso_date_pattern.test(deliveryDate)) {
        return "";
    }

    const [year, month, day] = deliveryDate.split("-").map(Number);

    return new Date(year, month - 1, day);
};

export const buildOrderPayload = ({ customerName, deliveryDate, items }) => {
    return {
        customer_name: customerName.trim(),
        delivery_date: normalizeDeliveryDate(deliveryDate),
        items: items
            .map((item) => {
                const productId = resolveProductId(item);

                if (!productId) {
                    return null;
                }

                return {
                    product_id: productId,
                    qty: Number(item.qty),
                };
            })
            .filter(Boolean),
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
