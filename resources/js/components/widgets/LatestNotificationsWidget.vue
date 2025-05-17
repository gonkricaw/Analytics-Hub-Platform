<!-- resources/js/components/widgets/LatestNotificationsWidget.vue -->
<template>
  <v-card class="widget-card" elevation="2" v-gsap-hover="{ scale: 1.03, duration: 0.3 }">
    <v-card-title class="widget-title d-flex align-center">
      <v-icon icon="mdi-bell" size="small" class="mr-2" color="primary" />
      Latest Notifications
      <v-spacer></v-spacer>
      <v-tooltip location="top" text="Refresh data">
        <template v-slot:activator="{ props }">
          <v-btn
            v-bind="props"
            variant="text"
            icon="mdi-refresh"
            size="small"
            color="primary"
            :loading="isLoading"
            :disabled="isLoading"
            @click="refreshData"
          ></v-btn>
        </template>
      </v-tooltip>
    </v-card-title>

    <v-divider></v-divider>

    <v-card-text class="pa-0">
      <v-list density="compact">
        <template v-if="notifications.length">
          <v-list-item
            v-for="(notification, index) in notifications"
            :key="notification.id"
            :title="notification.title"
            :subtitle="truncateText(notification.body, 50)"
            lines="two"
            v-gsap-fade-in="{ delay: index * 0.1 }"
          >
            <template v-slot:prepend>
              <v-icon
                :icon="getIconForType(notification.type)"
                :color="getColorForType(notification.type)"
                size="small"
              ></v-icon>
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

        <v-list-item v-if="!notifications.length">
          <div class="pa-4 text-center text-medium-emphasis">
            <v-icon icon="mdi-bell-off" size="large" class="mb-2"></v-icon>
            <div>No notifications available</div>
          </div>
        </v-list-item>
      </v-list>

      <v-divider></v-divider>

      <div class="text-center pa-2">
        <v-btn
          variant="text"
          color="primary"
          size="small"
          prepend-icon="mdi-arrow-right"
          @click="viewAllNotifications"
        >
          View All Notifications
        </v-btn>
      </div>
    </v-card-text>
  </v-card>
</template>

<script>
import { mapState, mapActions } from 'pinia';
import { useDashboardStore } from '@/stores/dashboardStore';
import { ref } from 'vue';
import { useRouter } from 'vue-router';

export default {
  name: 'LatestNotificationsWidget',

  setup() {
    const isLoading = ref(false);
    const router = useRouter();

    return {
      isLoading,
      router
    };
  },

  computed: {
    ...mapState(useDashboardStore, ['latestNotifications']),

    notifications() {
      return this.latestNotifications || [];
    }
  },

  methods: {
    ...mapActions(useDashboardStore, ['fetchDashboardData']),

    async refreshData() {
      this.isLoading = true;
      try {
        await this.fetchDashboardData();
      } finally {
        this.isLoading = false;
      }
    },

    truncateText(text, length = 50) {
      if (!text) return '';
      return text.length > length ? text.substring(0, length) + '...' : text;
    },

    formatTime(timestamp) {
      if (!timestamp) return '';

      const date = new Date(timestamp);
      const now = new Date();
      const diffHours = Math.floor((now - date) / (1000 * 60 * 60));

      if (diffHours < 1) {
        const diffMinutes = Math.floor((now - date) / (1000 * 60));
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
    },

    viewAllNotifications() {
      // Navigate to notifications page when implemented
      // this.router.push({ name: 'notifications' });
      console.log('View all notifications clicked');
    }
  }
};
</script>

<style lang="scss" scoped>
.widget-card {
  height: 100%;
  display: flex;
  flex-direction: column;
  transition: transform 0.3s ease, box-shadow 0.3s ease;

  .widget-title {
    font-size: 1rem;
    font-weight: 500;
    padding: 12px 16px;
    background-color: rgba(var(--v-theme-primary), 0.05);
  }
}
</style>
