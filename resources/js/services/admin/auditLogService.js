// resources/js/services/admin/auditLogService.js
import axios from '@/plugins/axios';

/**
 * Audit Log Service
 * Handles API calls related to audit log management in the admin panel
 */
const auditLogService = {
  /**
   * Get list of audit logs with pagination and filters
   * @param {Object} params - Query parameters
   * @returns {Promise} - API response
   */
  async getLogs(params = {}) {
    return await axios.get('/api/admin/audit-logs', { params });
  },

  /**
   * Get a single audit log by ID
   * @param {Number} id - Log ID
   * @returns {Promise} - API response
   */
  async getLog(id) {
    return await axios.get(`/api/admin/audit-logs/${id}`);
  },

  /**
   * Get filter options for audit logs
   * @returns {Promise} - API response with users, actions, and model types
   */
  async getFilterOptions() {
    return await axios.get('/api/admin/audit-logs/filter-options');
  },

  /**
   * Export audit logs to file
   * @param {Object} params - Export parameters and filters
   * @returns {Promise} - API response with file data
   */
  async exportLogs(params = {}) {
    return await axios.get('/api/admin/audit-logs/export', {
      params,
      responseType: 'blob'
    });
  }
};

export default auditLogService;
