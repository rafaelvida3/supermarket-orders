import axios from 'axios';

const apiClient = axios.create({
    baseURL: import.meta.env.VITE_API_URL || '/api',
    timeout: 10000,
    headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

apiClient.interceptors.response.use(
    response => response,
    error => {
        const status = error.response?.status;

        if (status === 401) {
            console.error('Unauthorized request.');
        }

        if (status === 500) {
            console.error('Internal server error.');
        }

        return Promise.reject(error);
    }
);

export default apiClient;