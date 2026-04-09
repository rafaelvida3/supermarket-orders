import apiClient from "./apiClient";

const unwrapData = (response) => {
    return response.data?.data ?? response.data;
};

export const fetchProducts = async (query) => {
    const response = await apiClient.get("/products", {
        params: {
            q: query,
        },
    });

    return unwrapData(response);
};

export const fetchStockProducts = async () => {
    const response = await apiClient.get("/products/stock");
    return unwrapData(response);
};
