<!-- resources/js/components/NotificationBell.vue -->
<template>
  <div class="notification-bell-wrapper">
    <v-btn
      icon
      class="notification-bell"
      v-gsap-pulse="{
        duration: 2,
        scale: 1.1,
        active: hasUnreadNotifications
      }"
      @click="toggleNotificationPanel"
    >
      <v-badge
        :content="unreadNotificationCount"
        :value="unreadNotificationCount > 0"
        color="error"
        dot-color="error"
        offset-x="8"
        offset-y="8"
      >
        <v-icon>mdi-bell</v-icon>
      </v-badge>
    </v-btn>

    <v-menu
      v-model="showMenu"
      :close-on-content-click="false"
      transition="slide-y-transition"
      offset-y
      max-width="400"
      min-width="350"
      left
      top
      location="bottom"
    >
      <template v-slot:activator="{ props }">
        <div v-bind="props" class="d-none"></div>
      </template>

      <v-card class="notification-panel">
        <v-card-title class="d-flex align-center justify-space-between px-4 pb-0">
          <h6 class="text-h6">Notifications</h6>
          <v-btn
            v-if="hasUnreadNotifications"
            variant="text"
            size="small"
            color="primary"
            @click="handleMarkAllAsRead"
          >
            Mark all as read
          </v-btn>
        </v-card-title>

        <v-divider class="mt-2"></v-divider>

        <v-card-text class="notification-list pa-0">
          <v-list lines="two">
            <template v-if="isLoading">
              <div class="d-flex justify-center align-center pa-4">
                <v-progress-circular indeterminate color="primary"></v-progress-circular>
              </div>
            </template>

            <template v-else-if="!notifications.length">
              <div class="text-center pa-4">
                <v-icon class="mb-2" color="grey" size="large">mdi-bell-off</v-icon>
                <div class="text-subtitle-1 text-medium-emphasis">No notifications found</div>
              </div>
            </template>

            <template v-else>
              <v-list-item
                v-for="(notification, index) in sortedNotifications.slice(0, 5)"
                :key="notification.id"
                :title="notification.title"
                :subtitle="truncateText(notification.message, 80)"
                lines="two"
                v-gsap-fade-in="{ delay: index * 0.1 }"
                :class="{ 'unread-notification': !notification.is_read }"
                @click="handleNotificationClick(notification)"
              >
                <template v-slot:prepend>
                  <v-icon :icon="getIconForType(notification.type)" :color="getColorForType(notification.type)" class="me-3"></v-icon>
                </template>
                <template v-slot:append>
                  <div class="d-flex flex-column align-end">
                    <span class="text-caption text-medium-emphasis">{{ formatTime(notification.created_at) }}</span>
                    <v-badge
                      v-if="!notification.is_read"
                      color="primary"
                      dot
                      location="start"
                      class="mt-1"
                    >
                      <span class="text-caption">New</span>
                    </v-badge>
                  </div>
                </template>
              </v-list-item>
            </template>
          </v-list>
        </v-card-text>

        <v-divider></v-divider>

        <v-card-actions class="pa-3 d-flex justify-center">
          <v-btn
            variant="tonal"
            color="primary"
            @click="viewAllNotifications"
            block
            v-gsap-hover="{ scale: 1.05 }"
          >
            View All Notifications
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-menu>
  </div>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'pinia';
import { useNotificationStore } from '@/stores/notificationStore';
import { useRouter } from 'vue-router';

export default {
  name: 'NotificationBell',

  data() {
    return {
      showMenu: false,
    };
  },

  computed: {
    ...mapState(useNotificationStore, ['notifications', 'unreadNotificationCount', 'isLoading']),
    ...mapGetters(useNotificationStore, ['sortedNotifications', 'hasUnreadNotifications']),

    router() {
      return useRouter();
    }
  },

  methods: {
    ...mapActions(useNotificationStore, ['fetchNotifications', 'markAsRead', 'markAllAsRead']),

    toggleNotificationPanel() {
      this.showMenu = !this.showMenu;

      if (this.showMenu) {
        this.fetchNotifications();
      }
    },

    async handleMarkAllAsRead() {
      await this.markAllAsRead();
    },

    async handleNotificationClick(notification) {
      // If notification has not been read, mark it as read
      if (!notification.is_read) {
        await this.markAsRead(notification.id);
      }

      // If notification has a link, navigate to it
      if (notification.link) {
        this.showMenu = false;
        this.router.push(notification.link);
      }
    },

    viewAllNotifications() {
      this.showMenu = false;
      this.router.push('/notifications');
    },

    truncateText(text, maxLength) {
      if (!text) return '';
      return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    },

    formatTime(timestamp) {
      if (!timestamp) return '';

      const date = new Date(timestamp);
      const now = new Date();

      const diffMs = now - date;
      const diffMinutes = Math.floor(diffMs / 60000);
      const diffHours = Math.floor(diffMinutes / 60);
      const diffDays = Math.floor(diffHours / 24);

      if (diffMinutes < 60) {
        if (diffMinutes < 1) return 'Just now';
        if (diffMinutes === 1) return '1 min ago';
        return `${diffMinutes} mins ago`;
      }

      if (diffHours < 24) {
        return `${diffHours}h ago`;
      }

      if (diffHours < 48) {
        return 'Yesterday';
      }

      return date.toLocaleDateString();
    },

    getIconForType(type) {
      switch (type) {
        case 'success': return 'mdi-check-circle';
        case 'warning': return 'mdi-alert';
        case 'error': return 'mdi-alert-circle';
        case 'info': return 'mdi-information';
        default: return 'mdi-bell';
      }
    },

    getColorForType(type) {
      switch (type) {
        case 'success': return 'success';
        case 'warning': return 'warning';
        case 'error': return 'error';
        case 'info': return 'info';
        default: return 'primary';
      }
    }
  },

  created() {
    // Initial fetch for unread count
    this.fetchNotifications();
  }
};
</script>

<style lang="scss" scoped>
.notification-bell-wrapper {
  position: relative;
}

.notification-bell {
  position: relative;
  z-index: 1;
}

.notification-panel {
  max-height: 400px;
  overflow-y: auto;
}

.notification-list {
  max-height: 300px;
  overflow-y: auto;
}

.unread-notification {
  background-color: rgba(var(--v-theme-primary), 0.04);
}
</style>
