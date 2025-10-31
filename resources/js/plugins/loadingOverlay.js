import LoadingOverlay from '@/components/LoadingOverlay.vue'
import { ref } from 'vue'

export default {
  install(app) {
    /* ===== Global reactive state ===== */
    // Controls the visibility of the loading overlay
    const visible = ref(false)

    /* ===== Global control functions ===== */
    // Shows the overlay
    const showOverlay = () => (visible.value = true)
    // Hides the overlay
    const hideOverlay = () => (visible.value = false)

    /* ===== Dependency injection ===== */
    // Makes the reactive state available to any component via inject()
    app.provide('overlayVisible', visible)

    /* ===== Global component registration ===== */
    // Registers the LoadingOverlay component globally (no need to import in each file)
    app.component('LoadingOverlay', LoadingOverlay)

    /* ===== Global helper functions (window scope) ===== */
    // Exposes overlay control functions globally for direct use (e.g., showOverlay())
    window.showOverlay = showOverlay
    window.hideOverlay = hideOverlay
  },
}