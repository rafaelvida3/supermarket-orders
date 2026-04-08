import { beforeEach, describe, expect, it, vi } from 'vitest';

const {
    pushMock,
    toastAddMock,
    createOrderMock,
    getOrderByIdMock,
    fetchProductsMock,
    showOverlayMock,
    hideOverlayMock,
} = vi.hoisted(() => {
    return {
        pushMock: vi.fn(),
        toastAddMock: vi.fn(),
        createOrderMock: vi.fn(),
        getOrderByIdMock: vi.fn(),
        fetchProductsMock: vi.fn(),
        showOverlayMock: vi.fn(),
        hideOverlayMock: vi.fn(),
    };
});

vi.mock('vue-router', () => ({
    useRoute: () => ({
        params: {},
    }),
    useRouter: () => ({
        push: pushMock,
    }),
}));

vi.mock('primevue/usetoast', () => ({
    useToast: () => ({
        add: toastAddMock,
    }),
}));

vi.mock('@/services/orderService', () => ({
    createOrder: createOrderMock,
    getOrderById: getOrderByIdMock,
}));

vi.mock('@/services/productService', () => ({
    fetchProducts: fetchProductsMock,
}));

vi.mock('@/composables/useLoadingOverlay', () => ({
    useLoadingOverlay: () => ({
        showOverlay: showOverlayMock,
        hideOverlay: hideOverlayMock,
    }),
}));

import { useOrderForm } from '@/composables/useOrderForm';

describe('useOrderForm', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('adds a new empty item', () => {
        const { items, addItem } = useOrderForm();

        addItem();

        expect(items.value.length).toBe(2);
    });

    it('removes an item', () => {
        const { items, addItem, removeItem } = useOrderForm();

        addItem();
        removeItem(0);

        expect(items.value.length).toBe(1);
    });

    it('calculates total correctly', () => {
        const { items, total } = useOrderForm();

        items.value = [
            {
                product_id: 1,
                qty: 2,
                price: 10,
                product: { id: 1, name: 'Rice' },
            },
            {
                product_id: 2,
                qty: 3,
                price: 5,
                product: { id: 2, name: 'Beans' },
            },
        ];

        expect(total.value).toBe(35);
    });

    it('loads product suggestions', async () => {
        fetchProductsMock.mockResolvedValue([{ id: 1, name: 'Rice' }]);

        const { completeProduct, productSuggestions } = useOrderForm();

        await completeProduct(0, { query: 'ri' });

        expect(fetchProductsMock).toHaveBeenCalledWith('ri');
        expect(productSuggestions.value[0]).toEqual([{ id: 1, name: 'Rice' }]);
    });

    it('creates an order with valid payload', async () => {
        createOrderMock.mockResolvedValue({ order_id: 1 });

        const { customerName, deliveryDate, items, saveOrder } = useOrderForm();

        customerName.value = 'Rafael';
        deliveryDate.value = '2026-04-10';
        items.value = [
            {
                product_id: 1,
                qty: 2,
                price: 10,
                product: { id: 1, name: 'Rice' },
            },
        ];

        await saveOrder();

        expect(createOrderMock).toHaveBeenCalledWith({
            customer_name: 'Rafael',
            delivery_date: '2026-04-10',
            items: [{ product_id: 1, qty: 2 }],
        });
    });
});