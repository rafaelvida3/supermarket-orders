import axios from 'axios'

/* ===== Orders Service ===== */

/**
 * Fetches all orders from the API.
 *
 * @async
 * @function fetchOrders
 * @returns {Promise<Array>} A promise that resolves with the list of orders.
 * @throws {Error} Throws an error if the API request fails.
 */
export const fetchOrders = async () => {
  try {
    const { data } = await axios.get('/api/pedidos')
    return data
  } catch (error) {
    console.error('Error fetching orders:', error)
    throw error
  }
}

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
  try {
    const { data } = await axios.post(`${import.meta.env.VITE_API_URL}/pedidos`, payload)
    return data
  } catch (error) {
    console.error('Error creating order:', error)
    throw error
  }
}

/**
 * Fetches a single order by ID.
 *
 * @async
 * @function getOrderById
 * @param {number|string} id - The order ID.
 * @returns {Promise<Object>} A promise that resolves with the order data.
 * @throws {Error} Throws an error if the API request fails.
 */
export const getOrderById = async (id) => {
  try {
    const { data } = await axios.get(`${import.meta.env.VITE_API_URL}/pedidos/${id}`)
    return data
  } catch (error) {
    console.error(`Error fetching order with ID ${id}:`, error)
    throw error
  }
}