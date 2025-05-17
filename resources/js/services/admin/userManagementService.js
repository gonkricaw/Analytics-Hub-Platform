// resources/js/services/admin/userManagementService.js
import axios from '@/plugins/axios';

/**
 * User Management Service
 * Handles API calls related to user management in the admin panel
 */
const userManagementService = {
  /**
   * Get list of users with pagination and filters
   * @param {Object} params - Query parameters
   * @returns {Promise} - API response
   */
  async getUsers(params = {}) {
    return await axios.get('/api/users', { params });
  },

  /**
   * Get user details by ID
   * @param {Number} userId - User ID
   * @returns {Promise} - API response
   */
  async getUser(userId) {
    return await axios.get(`/api/users/${userId}`);
  },

  /**
   * Create a new user
   * @param {Object} userData - User data
   * @returns {Promise} - API response
   */
  async createUser(userData) {
    return await axios.post('/api/users', userData);
  },

  /**
   * Update an existing user
   * @param {Number} userId - User ID
   * @param {Object} userData - Updated user data
   * @returns {Promise} - API response
   */
  async updateUser(userId, userData) {
    return await axios.put(`/api/users/${userId}`, userData);
  },

  /**
   * Delete a user
   * @param {Number} userId - User ID
   * @returns {Promise} - API response
   */
  async deleteUser(userId) {
    return await axios.delete(`/api/users/${userId}`);
  },

  /**
   * Send invitation to a new user
   * @param {Object} invitationData - Invitation data
   * @returns {Promise} - API response
   */
  async inviteUser(invitationData) {
    return await axios.post('/api/users/invite', invitationData);
  },

  /**
   * Get list of all roles
   * @returns {Promise} - API response
   */
  async getRoles() {
    return await axios.get('/api/roles');
  },

  /**
   * Get list of blocked IP addresses
   * @returns {Promise} - API response
   */
  async getBlockedIps() {
    return await axios.get('/api/admin/security/blocked-ips');
  },

  /**
   * Unblock an IP address
   * @param {String} ipAddress - IP address to unblock
   * @returns {Promise} - API response
   */
  async unblockIp(ipAddress) {
    return await axios.delete(`/api/admin/security/blocked-ips/${ipAddress}`);
  }
};

export default userManagementService;
