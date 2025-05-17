// resources/js/services/menuService.js
import axios from '@/plugins/axios';

/**
 * Menu Service
 *
 * Handles API calls related to menu structure and interactions
 */
class MenuService {
  /**
   * Get full menu structure based on user's permissions
   * @returns {Promise} - Response from API containing menu structure
   */
  getMenuStructure() {
    return axios.get('/api/menu/structure');
  }

  /**
   * Track menu item click for analytics
   * @param {Number} menuId - ID of the clicked menu item
   * @returns {Promise} - Response from API
   */
  trackMenuClick(menuId) {
    return axios.post('/api/menu/track-click', { menu_id: menuId });
  }

  /**
   * Get popular menu items based on analytics
   * @param {Number} limit - Maximum number of menu items to return
   * @returns {Promise} - Response from API containing popular menu items
   */
  getPopularMenuItems(limit = 5) {
    return axios.get('/api/menu/popular', { params: { limit } });
  }
}

export default new MenuService();
