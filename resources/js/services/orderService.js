import apiClient from "./apiClient";

const unwrapData = (response) => {
  return response.data?.data ?? response.data;
};

export const fetchOrders = async () => {
  const response = await apiClient.get("/orders");
  return unwrapData(response);
};

export const createOrder = async (payload) => {
  const response = await apiClient.post("/orders", payload);
  return unwrapData(response);
};

export const getOrderById = async (orderId) => {
  const response = await apiClient.get(`/orders/${orderId}`);
  return unwrapData(response);
};
