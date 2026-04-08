import { beforeEach, describe, expect, it, vi } from 'vitest';

const { getMock } = vi.hoisted(() => {
    return {
        getMock: vi.fn(),
    };
});

vi.mock('@/services/apiClient', () => ({
    default: {
        get: getMock,
    },
}));

import { fetchProducts, fetchStockProducts } from '@/services/productService';

describe('productService', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('fetches products with query', async () => {
        getMock.mockResolvedValue({
            data: [{ id: 1, name: 'Rice' }],
        });

        const result = await fetchProducts('ri');

        expect(getMock).toHaveBeenCalledWith('/products', {
            params: { q: 'ri' },
        });

        expect(result).toEqual([{ id: 1, name: 'Rice' }]);
    });

    it('fetches the full stock list', async () => {
        getMock.mockResolvedValue({
            data: [{ id: 1, name: 'Rice', qty_stock: 2 }],
        });

        const result = await fetchStockProducts();

        expect(getMock).toHaveBeenCalledWith('/products/stock');
        expect(result).toEqual([{ id: 1, name: 'Rice', qty_stock: 2 }]);
    });

    it('rethrows request errors', async () => {
        const error = new Error('Network error');
        getMock.mockRejectedValue(error);

        await expect(fetchProducts('ri')).rejects.toThrow('Network error');
    });
});