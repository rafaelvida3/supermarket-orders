/* ===== Orders Service ===== */

import apiClient from "./apiClient";

const getResponseData = (response) => {
  return response.data?.data ?? response.data;
};

/**
 * Fetches all orders from the API.
 *
 * @async
 * @function fetchOrders
 * @returns {Promise<Array>} A promise that resolves with the list of orders.
 * @throws {Error} Throws an error if the API request fails.
 */
export const fetchOrders = async () => {
  const response = await apiClient.get("/orders");
  return getResponseData(response);
};

/**
 * Creates a new order via API.
 *
 * @async
 * @function createOrder
 * @param {Object} payload - The order data to send to the API.
 * @returns {Promise<Object>} A promise that resolves with the created order data.
 * @throws {Error} Throws an error if the API request fails.
 */
export const createOrder = async (payload) => {
  const response = await apiClient.post("/orders", payload);
  return getResponseData(response);
};

/**
 * Fetches a single order by ID.
 *
 * @async
 * @function getOrderById
 * @param {number|string} orderId - The order ID.
 * @returns {Promise<Object>} A promise that resolves with the order data.
 * @throws {Error} Throws an error if the API request fails.
 */
export const getOrderById = async (orderId) => {
  const response = await apiClient.get(`/orders/${orderId}`);
  return getResponseData(response);
};
