// resources/js/services/contentService.js
import axios from '@/plugins/axios';

/**
 * Content Service
 *
 * Handles API calls related to content display and retrieval
 */
class ContentService {
  /**
   * Get content by slug
   * @param {String} slug - Content slug
   * @returns {Promise} - Response from API containing content data
   */
  getContentBySlug(slug) {
    return axios.get(`/api/content/${slug}`);
  }

  /**
   * Get content by ID
   * @param {Number} id - Content ID
   * @returns {Promise} - Response from API containing content data
   */
  getContentById(id) {
    return axios.get(`/api/content/id/${id}`);
  }

  /**
   * Get content by type
   * @param {String} type - Content type (html, image, video, document, etc.)
   * @param {Number} page - Pagination page number
   * @param {Number} perPage - Number of items per page
   * @returns {Promise} - Response from API containing content data
   */
  getContentByType(type, page = 1, perPage = 10) {
    return axios.get(`/api/content/type/${type}`, {
      params: { page, per_page: perPage }
    });
  }

  /**
   * Get embed URL details by UUID
   * @param {String} uuid - UUID of the embed
   * @returns {Promise} - Response from API containing embed details
   */
  getEmbedUrl(uuid) {
    return axios.get(`/api/embed/${uuid}`);
  }

  /**
   * Create a new embedded URL (for administrators)
   * @param {Object} data - Embed data (target_url, description)
   * @returns {Promise} - Response from API
   */
  createEmbedUrl(data) {
    return axios.post('/api/admin/embed', data);
  }
}

export default new ContentService();
