import { beforeEach, describe, expect, it, vi } from "vitest";

const { getMock, postMock } = vi.hoisted(() => {
  return {
    getMock: vi.fn(),
    postMock: vi.fn(),
  };
});

vi.mock("@/services/apiClient", () => ({
  default: {
    get: getMock,
    post: postMock,
  },
}));

import { createOrder, fetchOrders, getOrderById } from "@/services/orderService";

describe("orderService", () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it("fetches orders", async () => {
    getMock.mockResolvedValue({ data: { data: [{ id: 1 }] } });

    const result = await fetchOrders();

    expect(getMock).toHaveBeenCalledWith("/orders");
    expect(result).toEqual([{ id: 1 }]);
  });

  it("fetches order by id", async () => {
    getMock.mockResolvedValue({ data: { data: { id: 10 } } });

    const result = await getOrderById(10);

    expect(getMock).toHaveBeenCalledWith("/orders/10");
    expect(result).toEqual({ id: 10 });
  });

  it("creates order", async () => {
    const payload = {
      customer_name: "Rafael",
      delivery_date: "2026-04-10",
      items: [{ product_id: 1, qty: 2 }],
    };

    postMock.mockResolvedValue({ data: { data: { id: 1, total: "20.00" } } });

    const result = await createOrder(payload);

    expect(postMock).toHaveBeenCalledWith("/orders", payload);
    expect(result).toEqual({ id: 1, total: "20.00" });
  });
});
