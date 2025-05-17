// resources/js/services/admin/contentManagementService.js
import axios from '@/plugins/axios';

/**
 * Content Management Service
 * Handles API calls related to content management in the admin panel
 */
const contentManagementService = {
  /**
   * Get list of contents with pagination and filters
   * @param {Object} params - Query parameters
   * @returns {Promise} - API response
   */
  async getContents(params = {}) {
    return await axios.get('/api/content', { params });
  },

  /**
   * Get content details by ID
   * @param {Number} contentId - Content ID
   * @returns {Promise} - API response
   */
  async getContent(contentId) {
    return await axios.get(`/api/content/${contentId}`);
  },

  /**
   * Get content by slug
   * @param {String} slug - Content slug
   * @returns {Promise} - API response
   */
  async getContentBySlug(slug) {
    return await axios.get(`/api/content/by-slug/${slug}`);
  },

  /**
   * Create new content
   * @param {FormData|Object} contentData - Content data
   * @returns {Promise} - API response
   */
  async createContent(contentData) {
    const headers = contentData instanceof FormData
      ? { 'Content-Type': 'multipart/form-data' }
      : {};

    return await axios.post('/api/content', contentData, { headers });
  },

  /**
   * Update existing content
   * @param {Number} contentId - Content ID
   * @param {FormData|Object} contentData - Updated content data
   * @returns {Promise} - API response
   */
  async updateContent(contentId, contentData) {
    const headers = contentData instanceof FormData
      ? { 'Content-Type': 'multipart/form-data' }
      : {};

    if (contentData instanceof FormData) {
      // For FormData, we'll use POST with _method=PUT
      return await axios.post(`/api/content/${contentId}`, contentData, { headers });
    } else {
      // For regular JSON, we'll use PUT directly
      return await axios.put(`/api/content/${contentId}`, contentData, { headers });
    }
  },

  /**
   * Delete content
   * @param {Number} contentId - Content ID
   * @returns {Promise} - API response
   */
  async deleteContent(contentId) {
    return await axios.delete(`/api/content/${contentId}`);
  },

  /**
   * Get contents by type
   * @param {String} type - Content type
   * @param {Object} params - Additional query parameters
   * @returns {Promise} - API response
   */
  async getContentsByType(type, params = {}) {
    return await axios.get(`/api/content/by-type/${type}`, { params });
  },

  /**
   * Upload file for content
   * @param {FormData} fileData - File form data
   * @returns {Promise} - API response
   */
  async uploadFile(fileData) {
    return await axios.post('/api/content/upload', fileData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
  }
};

export default contentManagementService;
