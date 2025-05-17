// resources/js/stores/systemConfigStore.js
import { defineStore } from 'pinia';

/**
 * System Configuration Store
 *
 * Manages system-wide configuration settings for the Analytics Hub Platform.
 * This includes application-level settings, theme preferences, and global configurations.
 */
export const useSystemConfigStore = defineStore('systemConfig', {
  state: () => ({
    // Theme settings
    theme: {
      isDarkMode: true,
      primaryColor: '#8C3EFF', // Primary color as defined in requirements
      skin: 'modern', // Using 'modern' skin as per MCP get-library-docs standard
      density: 'default', // Vuetify density setting
    },

    // Application settings
    appInfo: {
      name: 'Indonet Analytics Hub Platform',
      version: '1.0.0',
      logoUrl: '/images/indonet-logo.png',
      copyrightText: `© ${new Date().getFullYear()} Indonet. All rights reserved.`,
    },

    // Footer text for layouts
    footerText: `© ${new Date().getFullYear()} Indonet Analytics Hub Platform - All rights reserved.`,

    // System settings
    system: {
      maintenanceMode: false,
      debugMode: process.env.NODE_ENV === 'development',
      apiBaseUrl: '/api',
      showDevelopmentRoutes: process.env.NODE_ENV === 'development',
    },

    // Feature flags
    featureFlags: {
      enableNotifications: true,
      enableUserPreferences: true,
      enableRealTimeUpdates: true,
    },

    // Loading state
    isLoading: false,

    // Additional configurations that will be loaded from the backend
    backendConfig: null,
  }),

  getters: {
    /**
     * Get the current theme configuration
     */
    currentTheme: (state) => state.theme,

    /**
     * Check if the application is in debug/development mode
     */
    isDebugMode: (state) => state.system.debugMode,

    /**
     * Check if the application is in maintenance mode
     */
    isMaintenanceMode: (state) => state.system.maintenanceMode,

    /**
     * Get the copyright text with current year
     */
    copyright: (state) => state.appInfo.copyrightText,

    /**
     * Check if development routes should be shown in navigation
     */
    shouldShowDevRoutes: (state) => state.system.showDevelopmentRoutes,
  },

  actions: {
    /**
     * Toggle dark mode
     */
    toggleDarkMode() {
      this.theme.isDarkMode = !this.theme.isDarkMode;
      // Save preference to local storage
      localStorage.setItem('darkMode', this.theme.isDarkMode);

      // Apply theme change
      this.applyTheme();
    },

    /**
     * Apply current theme settings to Vuetify
     */
    applyTheme() {
      // This would interact with Vuetify's theme system
      // Implementation depends on how Vuetify is set up in the application

      // If using a global Vuetify instance in a plugin:
      // app.config.globalProperties.$vuetify.theme.dark = this.theme.isDarkMode;
    },

    /**
     * Load configuration from backend API
     */
    async loadBackendConfig() {
      this.isLoading = true;
      try {
        // This would be implemented to call the backend API for system settings
        // const response = await fetch(`${this.system.apiBaseUrl}/system/config`);
        // this.backendConfig = await response.json();

        // For now, we'll mock this behavior
        await new Promise(resolve => setTimeout(resolve, 500));
        this.backendConfig = {
          // Mock configuration from backend
          maintenanceScheduled: false,
          systemAnnouncements: [],
        };
      } catch (error) {
        console.error('Failed to load system configuration:', error);
      } finally {
        this.isLoading = false;
      }
    },

    /**
     * Initialize store and load saved preferences
     */
    init() {
      // Load saved dark mode preference
      const savedDarkMode = localStorage.getItem('darkMode');
      if (savedDarkMode !== null) {
        this.theme.isDarkMode = savedDarkMode === 'true';
      }

      // Apply the theme
      this.applyTheme();

      // Load backend configuration
      // Commented out for now as we don't have the backend endpoint yet
      // this.loadBackendConfig();
    },
  },
});
