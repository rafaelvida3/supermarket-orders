import {
  buildOrderPayload,
  calculateItemSubtotal,
  createDefaultItem,
  mapOrderItemsToFormItems,
  normalizeDeliveryDate,
  parseDeliveryDateFromApi,
} from "@/composables/orderFormUtils";
import { describe, expect, it } from "vitest";

describe("orderFormUtils", () => {
  it("creates a default empty item", () => {
    expect(createDefaultItem()).toEqual({
      product_id: null,
      qty: 1,
      price: null,
      product: null,
    });
  });

  it("calculates subtotal for editable items", () => {
    expect(calculateItemSubtotal({ price: 10.5, qty: 3 })).toBe(31.5);
  });

  it("uses the persisted subtotal in view mode", () => {
    expect(calculateItemSubtotal({ subtotal: "21.00" }, true)).toBe(21);
  });

  it("normalizes a date object before sending the payload", () => {
    expect(normalizeDeliveryDate(new Date(2026, 3, 10))).toBe("2026-04-10");
  });

  it("parses an API date into a local date object", () => {
    const deliveryDate = parseDeliveryDateFromApi("2026-04-10");

    expect(deliveryDate).toBeInstanceOf(Date);
    expect(deliveryDate.getFullYear()).toBe(2026);
    expect(deliveryDate.getMonth()).toBe(3);
    expect(deliveryDate.getDate()).toBe(10);
  });

  it("builds the API payload with selected products only", () => {
    expect(
      buildOrderPayload({
        customerName: " Rafael ",
        deliveryDate: new Date(2026, 3, 10),
        items: [
          {
            product_id: 1,
            qty: 2,
            product: { id: 1, name: "Rice" },
          },
          {
            qty: 4,
            product: null,
          },
        ],
      })
    ).toEqual({
      customer_name: "Rafael",
      delivery_date: "2026-04-10",
      items: [{ product_id: 1, qty: 2 }],
    });
  });

  it("maps persisted order items to form items", () => {
    expect(
      mapOrderItemsToFormItems([
        {
          product_id: 1,
          qty: 2,
          unit_price: "10.50",
          subtotal: "21.00",
          product: { id: 1, name: "Rice" },
        },
      ])
    ).toEqual([
      {
        product_id: 1,
        qty: 2,
        price: "10.50",
        subtotal: "21.00",
        product: { id: 1, name: "Rice" },
      },
    ]);
  });
});
