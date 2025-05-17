// resources/js/services/admin/emailTemplateService.js
import axios from '@/plugins/axios';

/**
 * Email Template Service
 * Handles API calls related to email template management in the admin panel
 */
const emailTemplateService = {
  /**
   * Get list of email templates
   * @returns {Promise} - API response
   */
  async getTemplates() {
    return await axios.get('/api/email-templates');
  },

  /**
   * Get a single email template by ID
   * @param {Number} id - Template ID
   * @returns {Promise} - API response
   */
  async getTemplate(id) {
    return await axios.get(`/api/email-templates/${id}`);
  },

  /**
   * Create a new email template
   * @param {Object} templateData - Template data
   * @returns {Promise} - API response
   */
  async createTemplate(templateData) {
    return await axios.post('/api/email-templates', templateData);
  },

  /**
   * Update an existing email template
   * @param {Number} id - Template ID
   * @param {Object} templateData - Updated template data
   * @returns {Promise} - API response
   */
  async updateTemplate(id, templateData) {
    return await axios.put(`/api/email-templates/${id}`, templateData);
  },

  /**
   * Delete an email template
   * @param {Number} id - Template ID
   * @returns {Promise} - API response
   */
  async deleteTemplate(id) {
    return await axios.delete(`/api/email-templates/${id}`);
  }
};

export default emailTemplateService;
