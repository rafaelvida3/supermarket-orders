import axios from 'axios'

/* ===== Products Service ===== */

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
  try {
    const { data } = await axios.get(`${import.meta.env.VITE_API_URL}/produtos?q=${query}`)
    return data
  } catch (error) {
    console.error('Error fetching products:', error)
    throw error
  }
}