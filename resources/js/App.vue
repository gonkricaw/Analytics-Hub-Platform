<!-- resources/js/App.vue -->
<template>
  <component :is="layout">
    <template v-if="layout !== 'blank-layout'">
      <!-- Global Loading Indicator -->
      <GlobalLoadingIndicator />

      <!-- Global Snackbar for notifications -->
      <v-snackbar
        v-model="showSnackbar"
        :color="snackbarColor"
        :timeout="snackbarTimeout"
        location="top"
      >
        {{ snackbarText }}
        <template v-slot:actions>
          <v-btn
            variant="text"
            @click="showSnackbar = false"
          >
            Close
          </v-btn>
        </template>
      </v-snackbar>
    </template>
  </component>
</template>

<script>
import { computed } from 'vue';
import { mapState } from 'pinia';
import { useRoute } from 'vue-router';
import { useLayoutStore } from './stores/layoutStore';
import { useSystemConfigStore } from './stores/systemConfigStore';
import { useAuthStore } from './stores/authStore';
import GlobalLoadingIndicator from './components/GlobalLoadingIndicator.vue';

// Import layouts
import DefaultLayout from '@/layouts/DefaultLayout.vue';
import BlankLayout from '@/layouts/BlankLayout.vue';

export default {
  name: 'App',
  components: {
    GlobalLoadingIndicator,
    DefaultLayout,
    BlankLayout
  },
  setup() {
    const route = useRoute();

    // Determine layout based on route metadata
    const layout = computed(() => {
      // Get layout from route meta, default to 'default-layout'
      const routeLayout = route.meta.layout || 'default';
      return `${routeLayout}-layout`;
    });

    return { layout };
  },
  computed: {
    ...mapState(useLayoutStore, [
      'showSnackbar',
      'snackbarText',
      'snackbarColor',
      'snackbarTimeout'
    ])
  },
  created() {
    // Set up global error handling with the layout store
    this.$store = useLayoutStore();

    // Initialize the system configuration store
    const systemConfig = useSystemConfigStore();
    systemConfig.init();

    // Initialize the auth store
    const authStore = useAuthStore();
    authStore.init();
  }
};
</script>

<style lang="scss">
/* Global styles that apply to the entire app */
@import '@scss/main.scss';
</style>
