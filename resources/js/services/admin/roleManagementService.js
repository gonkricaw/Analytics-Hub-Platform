// resources/js/services/admin/roleManagementService.js
import axios from '@/plugins/axios';

/**
 * Role Management Service
 * Handles API calls related to role management in the admin panel
 */
const roleManagementService = {
  /**
   * Get list of roles with pagination and filters
   * @param {Object} params - Query parameters
   * @returns {Promise} - API response
   */
  async getRoles(params = {}) {
    return await axios.get('/api/roles', { params });
  },

  /**
   * Get role details by ID
   * @param {Number} roleId - Role ID
   * @returns {Promise} - API response
   */
  async getRole(roleId) {
    return await axios.get(`/api/roles/${roleId}`);
  },

  /**
   * Get list of all permissions
   * @param {Object} params - Optional query parameters (e.g. module)
   * @returns {Promise} - API response
   */
  async getPermissions(params = {}) {
    return await axios.get('/api/permissions', { params });
  },

  /**
   * Get permissions grouped by module
   * @returns {Promise} - API response
   */
  async getPermissionsByModule() {
    return await axios.get('/api/permissions/by-module');
  },

  /**
   * Create a new role
   * @param {Object} roleData - Role data
   * @returns {Promise} - API response
   */
  async createRole(roleData) {
    return await axios.post('/api/roles', roleData);
  },

  /**
   * Update an existing role
   * @param {Number} roleId - Role ID
   * @param {Object} roleData - Updated role data
   * @returns {Promise} - API response
   */
  async updateRole(roleId, roleData) {
    return await axios.put(`/api/roles/${roleId}`, roleData);
  },

  /**
   * Delete a role
   * @param {Number} roleId - Role ID
   * @returns {Promise} - API response
   */
  async deleteRole(roleId) {
    return await axios.delete(`/api/roles/${roleId}`);
  },

  /**
   * Assign permissions to a role
   * @param {Number} roleId - Role ID
   * @param {Array} permissionIds - Array of permission IDs
   * @returns {Promise} - API response
   */
  async assignPermissions(roleId, permissionIds) {
    return await axios.post(`/api/roles/${roleId}/permissions`, {
      permissions: permissionIds
    });
  }
};

export default roleManagementService;
