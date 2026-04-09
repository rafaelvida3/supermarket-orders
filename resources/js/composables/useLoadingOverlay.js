import { readonly, ref } from "vue";

const isVisible = ref(false);

const showOverlay = () => {
    isVisible.value = true;
};

const hideOverlay = () => {
    isVisible.value = false;
};

export const useLoadingOverlay = () => {
    return {
        isVisible: readonly(isVisible),
        showOverlay,
        hideOverlay,
    };
};
