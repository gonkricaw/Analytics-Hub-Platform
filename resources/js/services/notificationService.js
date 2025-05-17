// resources/js/services/notificationService.js
/**
 * Notification Service - Handles API calls related to system notifications
 * Centralizes all notification-related API requests
 */
import axios from '@/plugins/axios';

export const notificationService = {
  /**
   * Fetches all notifications for the current user
   * @returns {Promise<Array>} Array of notification objects
   */
  async getNotifications() {
    const response = await axios.get('/api/notifications');
    return response.data.data || [];
  },

  /**
   * Gets the count of unread notifications
   * @returns {Promise<Number>} Number of unread notifications
   */
  async getUnreadCount() {
    const response = await axios.get('/api/notifications/unread-count');
    return response.data.count || 0;
  },

  /**
   * Marks a specific notification as read
   * @param {Number} id - The ID of the notification to mark as read
   * @returns {Promise<Object>} Response from the server
   */
  async markAsRead(id) {
    const response = await axios.post(`/api/notifications/${id}/mark-read`);
    return response.data;
  },

  /**
   * Marks all notifications as read for the current user
   * @returns {Promise<Object>} Response from the server
   */
  async markAllAsRead() {
    const response = await axios.post('/api/notifications/mark-all-read');
    return response.data;
  },

  /**
   * Gets notifications by type
   * @param {String} type - Type of notifications to fetch (success, warning, error, info)
   * @returns {Promise<Array>} Array of notification objects of the specified type
   */
  async getNotificationsByType(type) {
    const response = await axios.get(`/api/notifications/type/${type}`);
    return response.data.data || [];
  },

  /**
   * Gets notification details by ID
   * @param {Number} id - The ID of the notification
   * @returns {Promise<Object>} Notification details
   */
  async getNotificationById(id) {
    const response = await axios.get(`/api/notifications/${id}`);
    return response.data.data || null;
  }
};

export default notificationService;
