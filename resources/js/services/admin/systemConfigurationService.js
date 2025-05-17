// resources/js/services/admin/systemConfigurationService.js
import axios from '@/plugins/axios';

/**
 * System Configuration Service
 * Handles API calls related to system configuration in the admin panel
 */
const systemConfigurationService = {
  /**
   * Get list of system configurations with optional filters
   * @param {Object} params - Query parameters
   * @returns {Promise} - API response
   */
  async getConfigurations(params = {}) {
    return await axios.get('/api/admin/system-configurations', { params });
  },

  /**
   * Get configuration groups
   * @returns {Promise} - API response
   */
  async getConfigurationGroups() {
    return await axios.get('/api/admin/system-configurations/groups');
  },

  /**
   * Get a single system configuration by key
   * @param {String} key - Configuration key
   * @returns {Promise} - API response
   */
  async getConfiguration(key) {
    return await axios.get(`/api/admin/system-configurations/${key}`);
  },

  /**
   * Create a new system configuration
   * @param {Object} configData - Configuration data
   * @returns {Promise} - API response
   */
  async createConfiguration(configData) {
    return await axios.post('/api/admin/system-configurations', configData);
  },

  /**
   * Update an existing system configuration
   * @param {String} key - Configuration key
   * @param {Object} configData - Updated configuration data
   * @returns {Promise} - API response
   */
  async updateConfiguration(key, configData) {
    return await axios.put(`/api/admin/system-configurations/${key}`, configData);
  },

  /**
   * Delete a system configuration
   * @param {String} key - Configuration key
   * @returns {Promise} - API response
   */
  async deleteConfiguration(key) {
    return await axios.delete(`/api/admin/system-configurations/${key}`);
  },

  /**
   * Update multiple system configurations at once
   * @param {Object} configData - Contains an array of configurations to update
   * @returns {Promise} - API response
   */
  async bulkUpdateConfigurations(configData) {
    return await axios.put('/api/admin/system-configurations/bulk-update', configData);
  },

  /**
   * Get public system configurations
   * @returns {Promise} - API response
   */
  async getPublicConfigurations() {
    return await axios.get('/api/system-configurations/public');
  }
};

export default systemConfigurationService;
