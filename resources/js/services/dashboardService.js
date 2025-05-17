// resources/js/services/dashboardService.js
import axios from '@/plugins/axios';

/**
 * Dashboard Service
 *
 * Handles API calls related to dashboard data and widgets
 */
class DashboardService {
  /**
   * Get all dashboard data for the home page
   * @returns {Promise} - Response from API containing widgets and configuration
   */
  getDashboardData() {
    return axios.get('/api/dashboard');
  }

  /**
   * Refresh a specific widget's data
   * @param {String} widgetType - Type of widget to refresh (online_users, login_history, etc.)
   * @returns {Promise} - Response from API containing widget data
   */
  refreshWidget(widgetType) {
    return axios.get(`/api/dashboard/widget/${widgetType}`);
  }
}

export default new DashboardService();
