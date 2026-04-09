import { useToast } from "primevue/usetoast";

const defaultLife = 2500;

export const useAppToast = () => {
    const toast = useToast();

    const addToast = (message) => {
        toast.add({
            life: defaultLife,
            ...message,
        });
    };

    return {
        addToast,
    };
};
