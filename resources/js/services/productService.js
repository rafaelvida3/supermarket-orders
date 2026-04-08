/* ===== Products Service ===== */

import apiClient from "./apiClient";

const getResponseData = (response) => {
  return response.data?.data ?? response.data;
};

/**
 * Fetches products from the API based on a search query.
 *
 * @async
 * @function fetchProducts
 * @param {string} query - Search term used to filter products.
 * @returns {Promise<Array>} A promise that resolves with the list of matching products.
 * @throws {Error} Throws an error if the API request fails.
 */
export const fetchProducts = async (query) => {
  const response = await apiClient.get("/products", {
    params: {
      q: query,
    },
  });

  return getResponseData(response);
};

/**
 * Fetches the full inventory snapshot from the API.
 *
 * @async
 * @function fetchStockProducts
 * @returns {Promise<Array>} A promise that resolves with the full stock list.
 * @throws {Error} Throws an error if the API request fails.
 */
export const fetchStockProducts = async () => {
  const response = await apiClient.get("/products/stock");
  return getResponseData(response);
};
