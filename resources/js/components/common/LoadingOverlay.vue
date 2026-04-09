<template>
  <div
    v-if="isVisible"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
  >
    <ProgressSpinner
      style="width: 60px; height: 60px"
      strokeWidth="4"
      animationDuration=".8s"
    />
  </div>
</template>

<script setup>
import { useLoadingOverlay } from "@/composables/useLoadingOverlay";
import ProgressSpinner from "primevue/progressspinner";

const { isVisible } = useLoadingOverlay();
</script>
