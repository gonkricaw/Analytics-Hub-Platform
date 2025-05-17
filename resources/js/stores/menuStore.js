// resources/js/stores/menuStore.js
import { defineStore } from 'pinia';
import axios from '@plugins/axios';
import { useLayoutStore } from './layoutStore';

export const useMenuStore = defineStore('menu', {
  state: () => ({
    menus: [],
    isLoading: false,
    error: null,
  }),

  actions: {
    async fetchMenus() {
      const layoutStore = useLayoutStore();

      try {
        this.isLoading = true;
        layoutStore.startLoading();

        // Get menus from API - this should return a structure with parent/child relationships
        const response = await axios.get('/api/menu/structure');

        // Format menu items if needed
        this.menus = response.data.data || [];

        return this.menus;
      } catch (error) {
        this.error = error;
        layoutStore.handleError(error, 'Failed to load menu structure');
        return [];
      } finally {
        this.isLoading = false;
        layoutStore.stopLoading();
      }
    },

    // Track menu clicks for popularity metrics
    trackMenuClick(menuId) {
      try {
        // This would typically send an API request to track the click
        // For now we'll just implement logging
        console.log('Menu click tracked:', menuId);
      } catch (error) {
        console.error('Error tracking menu click:', error);
      }
    },

    // Clear menu data (for logout)
    clearMenus() {
      this.menus = [];
      this.error = null;
    }
  }
});
