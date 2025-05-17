// resources/js/stores/notificationStore.js
import { defineStore } from 'pinia';
import axios from '@/plugins/axios';
import { useLayoutStore } from './layoutStore';

export const useNotificationStore = defineStore('notification', {
  state: () => ({
    notifications: [],
    unreadNotificationCount: 0,
    isLoading: false,
    error: null,
    showNotificationPanel: false
  }),

  getters: {
    hasUnreadNotifications: (state) => state.unreadNotificationCount > 0,

    sortedNotifications: (state) => {
      return [...state.notifications].sort((a, b) => {
        // Sort by read status first (unread at top)
        if (a.is_read !== b.is_read) {
          return a.is_read ? 1 : -1;
        }
        // Then by date (newest at top)
        return new Date(b.created_at) - new Date(a.created_at);
      });
    }
  },

  actions: {
    async fetchNotifications() {
      const layoutStore = useLayoutStore();

      try {
        this.isLoading = true;

        const response = await axios.get('/api/notifications');
        this.notifications = response.data.data || [];

        // Count unread notifications
        this.refreshUnreadCount();

        return this.notifications;
      } catch (error) {
        this.error = error;
        layoutStore.handleError(error, 'Failed to load notifications');
        return [];
      } finally {
        this.isLoading = false;
      }
    },

    async fetchUnreadCount() {
      try {
        const response = await axios.get('/api/notifications/unread-count');
        this.unreadNotificationCount = response.data.count || 0;
        return this.unreadNotificationCount;
      } catch (error) {
        console.error('Failed to load unread notification count:', error);
        return 0;
      }
    },

    refreshUnreadCount() {
      this.unreadNotificationCount = this.notifications.filter(n => !n.is_read).length;
    },

    async markAsRead(notificationId) {
      const layoutStore = useLayoutStore();

      try {
        await axios.post(`/api/notifications/${notificationId}/mark-read`);

        // Update local state
        const notification = this.notifications.find(n => n.id === notificationId);
        if (notification) {
          notification.is_read = true;
          this.refreshUnreadCount();
        }

        return true;
      } catch (error) {
        layoutStore.handleError(error, 'Failed to mark notification as read');
        return false;
      }
    },

    async markAllAsRead() {
      const layoutStore = useLayoutStore();

      try {
        await axios.post('/api/notifications/mark-all-read');

        // Update local state
        this.notifications.forEach(notification => {
          notification.is_read = true;
        });

        this.unreadNotificationCount = 0;
        layoutStore.showSuccess('All notifications marked as read');

        return true;
      } catch (error) {
        layoutStore.handleError(error, 'Failed to mark all notifications as read');
        return false;
      }
    },

    openNotificationPanel() {
      this.showNotificationPanel = true;
    },

    closeNotificationPanel() {
      this.showNotificationPanel = false;
    },

    clearNotifications() {
      this.notifications = [];
      this.unreadNotificationCount = 0;
      this.error = null;
    }
  }
});
