<template>
  <!-- Main layout wrapper -->
  <div class="p-6">
    <!-- Global loading overlay component -->
    <LoadingOverlay />

    <!-- Global toast notifications -->
    <Toast position="top-right" />

    <!-- Router outlet: renders the active page component -->
    <router-view />
  </div>
</template>

<script setup>
// No additional logic required for this layout
</script>