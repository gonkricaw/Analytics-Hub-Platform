// resources/js/stores/layoutStore.js
import { defineStore } from 'pinia';

export const useLayoutStore = defineStore('layout', {
  state: () => ({
    isLoading: false,
    loadingCounter: 0,
    showSnackbar: false,
    snackbarText: '',
    snackbarColor: 'success', // success, error, info, warning
    snackbarTimeout: 5000,
    windowWidth: window.innerWidth,
    windowHeight: window.innerHeight,
    currentPage: null,
    currentPageTitle: '',
  }),

  getters: {
    isMobile: (state) => state.windowWidth < 600,
    isTablet: (state) => state.windowWidth >= 600 && state.windowWidth < 960,
    isDesktop: (state) => state.windowWidth >= 960,
    screenSize: (state) => {
      if (state.windowWidth < 600) return 'xs';
      if (state.windowWidth < 960) return 'sm';
      if (state.windowWidth < 1264) return 'md';
      if (state.windowWidth < 1904) return 'lg';
      return 'xl';
    },
  },

  actions: {
    startLoading() {
      this.loadingCounter++;
      this.isLoading = true;
    },

    stopLoading() {
      this.loadingCounter--;
      if (this.loadingCounter <= 0) {
        this.loadingCounter = 0;
        this.isLoading = false;
      }
    },

    resetLoading() {
      this.loadingCounter = 0;
      this.isLoading = false;
    },

    showMessage(text, color = 'success', timeout = 5000) {
      this.snackbarText = text;
      this.snackbarColor = color;
      this.snackbarTimeout = timeout;
      this.showSnackbar = true;
    },

    hideMessage() {
      this.showSnackbar = false;
    },

    showSuccess(text, timeout = 5000) {
      this.showMessage(text, 'success', timeout);
    },

    showError(text, timeout = 8000) {
      this.showMessage(text, 'error', timeout);
    },

    showInfo(text, timeout = 5000) {
      this.showMessage(text, 'info', timeout);
    },

    showWarning(text, timeout = 6000) {
      this.showMessage(text, 'warning', timeout);
    },

    updateWindowSize() {
      this.windowWidth = window.innerWidth;
      this.windowHeight = window.innerHeight;
    },

    handleError(error, defaultMessage = 'An unexpected error occurred') {
      console.error('Error caught by layout store:', error);

      // Extract message from error
      let errorMessage = defaultMessage;
      if (error?.response?.data?.message) {
        errorMessage = error.response.data.message;
      } else if (error?.message) {
        errorMessage = error.message;
      }

      this.showError(errorMessage);
    }
  }
});
