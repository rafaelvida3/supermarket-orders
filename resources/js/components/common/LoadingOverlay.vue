<template>
    <div v-if="isVisible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <ProgressSpinner
            style="width: 60px; height: 60px"
            stroke-width="4"
            animation-duration=".8s"
        />
    </div>
</template>

<script setup>
import { useLoadingOverlay } from "@/composables/useLoadingOverlay";
import ProgressSpinner from "primevue/progressspinner";

const { isVisible } = useLoadingOverlay();
</script>
