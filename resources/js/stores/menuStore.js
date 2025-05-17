// resources/js/stores/menuStore.js
import { defineStore } from 'pinia';
import menuService from '../services/menuService';
import { useLayoutStore } from './layoutStore';

export const useMenuStore = defineStore('menu', {
  state: () => ({
    menus: [],
    isLoading: false,
    error: null
  }),

  getters: {
    /**
     * Get main navigation menu items
     */
    navigationMenus: (state) => state.menus || [],

    /**
     * Check if menus are loaded
     */
    hasMenus: (state) => state.menus.length > 0
  },

  actions: {
    /**
     * Fetch menu structure from the API
     */
    async fetchMenus() {
      const layoutStore = useLayoutStore();

      try {
        this.isLoading = true;
        layoutStore.startLoading();

        const response = await menuService.getMenuStructure();

        if (response.data && response.data.menu) {
          this.menus = response.data.menu;
        } else if (response.data && response.data.data) {
          // Alternative response format
          this.menus = response.data.data;
        } else {
          // Fallback to empty array if no data
          this.menus = [];
        }

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

    /**
     * Track menu item click for analytics/popularity
     * @param {Number} menuId - ID of the clicked menu
     */
    trackMenuClick(menuId) {
      try {
        // Send API request to track menu click
        menuService.trackMenuClick(menuId).catch(error => {
          console.error('Error tracking menu click:', error);
        });
      } catch (error) {
        console.error('Error tracking menu click:', error);
      }
    },

    /**
     * Get popular menu items for dashboards
     * @param {Number} limit - Maximum number of items to retrieve
     * @returns {Promise} - Popular menu items
     */
    async getPopularMenuItems(limit = 5) {
      try {
        const response = await menuService.getPopularMenuItems(limit);

        if (response.data && response.data.popular_menu_items) {
          return response.data.popular_menu_items;
        }

        return [];
      } catch (error) {
        console.error('Error fetching popular menu items:', error);
        return [];
      }
    },

    /**
     * Clear menu data (for logout)
     */
    clearMenus() {
      this.menus = [];
      this.error = null;
    }
  }
});
