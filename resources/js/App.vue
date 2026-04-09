<template>
    <div class="p-6">
        <LoadingOverlay />
        <Toast position="top-right" />
        <router-view />
    </div>
</template>

<script setup>
import LoadingOverlay from "@/components/common/LoadingOverlay.vue";
</script>
