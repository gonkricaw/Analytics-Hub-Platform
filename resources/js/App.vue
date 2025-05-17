<!-- resources/js/App.vue -->
<template>
  <v-app>
    <router-view />

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
  </v-app>
</template>

<script>
import { mapState } from 'pinia';
import { useLayoutStore } from './stores/layoutStore';
import { useSystemConfigStore } from './stores/systemConfigStore';
import { useAuthStore } from './stores/authStore';
import GlobalLoadingIndicator from './components/GlobalLoadingIndicator.vue';

export default {
  name: 'App',
  components: {
    GlobalLoadingIndicator
  },
  computed: {
    ...mapState(useLayoutStore, [
      'showSnackbar',
      'snackbarText',
      'snackbarColor',
      'snackbarTimeout'
    ])
  },  created() {
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
