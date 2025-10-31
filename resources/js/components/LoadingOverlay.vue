<template>
  <!-- Overlay container: covers the entire screen when visible -->
  <div
    v-if="visible"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
  >
    <!-- Loading spinner displayed at the center -->
    <ProgressSpinner
      style="width: 60px; height: 60px"
      strokeWidth="4"
      animationDuration=".8s"
    />
  </div>
</template>

<script setup>
import { ProgressSpinner } from 'primevue';
import { inject } from 'vue';

/* Reactive visibility state injected from the global provider */
const visible = inject('overlayVisible')
</script>