// resources/js/stores/notificationStore.js
import { defineStore } from 'pinia';
import notificationService from '@/services/notificationService';
import { useLayoutStore } from './layoutStore';
import { gsap } from 'gsap';

export const useNotificationStore = defineStore('notification', {
  state: () => ({
    notifications: [],
    unreadNotificationCount: 0,
    isLoading: false,
    error: null,
    showNotificationPanel: false,
    lastFetchTime: null,
    refreshInterval: 60000, // 1 minute interval for auto-refresh
    notificationPollTimer: null,
    notificationSettings: {
      sound: true,
      desktop: true,
    }
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
    },

    notificationsByType: (state) => {
      const result = {
        info: 0,
        success: 0,
        warning: 0,
        error: 0
      };

      state.notifications.forEach(n => {
        if (result[n.type] !== undefined) {
          result[n.type]++;
        }
      });

      return result;
    }
  },

  actions: {
    async fetchNotifications() {
      const layoutStore = useLayoutStore();

      try {
        this.isLoading = true;

        const newNotifications = await notificationService.getNotifications();

        // Check if we have any new unread notifications
        if (this.lastFetchTime) {
          const newUnreadNotifications = newNotifications.filter(
            notification => !notification.is_read &&
            new Date(notification.created_at) > this.lastFetchTime &&
            !this.notifications.some(n => n.id === notification.id)
          );

          if (newUnreadNotifications.length > 0) {
            this.playNotificationSound();
            this.showDesktopNotification(newUnreadNotifications[0]);
          }
        }

        this.notifications = newNotifications;
        this.lastFetchTime = new Date();

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
        this.unreadNotificationCount = await notificationService.getUnreadCount();
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
        await notificationService.markAsRead(notificationId);

        // Update local state
        const notification = this.notifications.find(n => n.id === notificationId);
        if (notification) {
          notification.is_read = true;
          notification.read_at = new Date().toISOString();
          this.refreshUnreadCount();

          // Animate the notification badge decreasing
          this.animateBadgeChange();
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
        await notificationService.markAllAsRead();

        // Update local state
        const previousUnreadCount = this.unreadNotificationCount;
        this.notifications.forEach(notification => {
          notification.is_read = true;
          notification.read_at = new Date().toISOString();
        });

        this.unreadNotificationCount = 0;
        layoutStore.showSuccess('All notifications marked as read');

        // Animate the notification badge decreasing
        if (previousUnreadCount > 0) {
          this.animateBadgeChange();
        }

        return true;
      } catch (error) {
        layoutStore.handleError(error, 'Failed to mark all notifications as read');
        return false;
      }
    },

    animateBadgeChange() {
      // Find the badge element and animate it
      const badgeElement = document.querySelector('.v-badge__badge');
      if (badgeElement) {
        gsap.to(badgeElement, {
          scale: 1.5,
          duration: 0.2,
          onComplete: () => {
            gsap.to(badgeElement, {
              scale: 1,
              duration: 0.2
            });
          }
        });
      }
    },

    playNotificationSound() {
      if (this.notificationSettings.sound) {
        // Create a simple notification sound
        const audio = new Audio();
        audio.src = '/sounds/notification.mp3'; // Make sure this file exists
        audio.volume = 0.5;
        audio.play().catch(e => {
          // Browser might block autoplay, just log the error
          console.error('Could not play notification sound:', e);
        });
      }
    },

    showDesktopNotification(notification) {
      if (this.notificationSettings.desktop && 'Notification' in window) {
        if (Notification.permission === 'granted') {
          new Notification('Indonet Analytics Hub', {
            body: notification.title,
            icon: '/images/logo.png' // Make sure this file exists
          });
        } else if (Notification.permission !== 'denied') {
          Notification.requestPermission();
        }
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
    },

    startNotificationPolling() {
      // Clear any existing timer
      this.stopNotificationPolling();

      // Start a new polling timer
      this.notificationPollTimer = setInterval(() => {
        this.fetchUnreadCount();
      }, this.refreshInterval);
    },

    stopNotificationPolling() {
      if (this.notificationPollTimer) {
        clearInterval(this.notificationPollTimer);
        this.notificationPollTimer = null;
      }
    },

    updateNotificationSettings(settings) {
      this.notificationSettings = {
        ...this.notificationSettings,
        ...settings
      };
    }
  }
});
