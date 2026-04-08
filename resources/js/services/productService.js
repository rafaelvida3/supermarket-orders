/* ===== Products Service ===== */

import apiClient from './apiClient';

/**
 * Fetches products from the API based on a search query.
 *
 * @async
 * @function fetchProducts
 * @param {string} query - Search term used to filter products.
 * @returns {Promise<Array>} A promise that resolves with the list of matching products.
 * @throws {Error} Throws an error if the API request fails.
 */
export const fetchProducts = async query => {
    const response = await apiClient.get('/products', {
        params: {
            q: query,
        },
    });

    return response.data;
};