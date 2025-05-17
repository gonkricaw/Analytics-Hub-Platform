<!-- resources/js/views/NotificationListView.vue -->
<template>
  <div class="notification-list-view">
    <v-container>
      <v-row>
        <v-col cols="12">
          <v-card class="mb-4">
            <v-card-title class="d-flex align-center justify-space-between flex-wrap">
              <div class="d-flex align-center">
                <v-icon icon="mdi-bell" color="primary" class="me-2" size="large"></v-icon>
                <h5 class="text-h5">Notifications</h5>
              </div>
              <div>
                <v-btn
                  v-if="hasUnreadNotifications"
                  color="primary"
                  variant="tonal"
                  prepend-icon="mdi-check-all"
                  @click="handleMarkAllAsRead"
                  v-gsap-hover="{ scale: 1.05 }"
                >
                  Mark all as read
                </v-btn>
              </div>
            </v-card-title>

            <v-divider></v-divider>

            <v-tabs v-model="activeTab" bg-color="transparent">
              <v-tab value="all">All Notifications</v-tab>
              <v-tab value="unread">
                Unread
                <v-badge
                  v-if="unreadNotificationCount > 0"
                  :content="unreadNotificationCount"
                  color="error"
                  inline
                  class="ms-2"
                ></v-badge>
              </v-tab>
            </v-tabs>

            <v-card-text class="pb-0">
              <v-text-field
                v-model="searchQuery"
                prepend-inner-icon="mdi-magnify"
                label="Search notifications"
                hide-details
                variant="outlined"
                density="compact"
                class="mb-4"
                clearable
              ></v-text-field>
            </v-card-text>

            <v-divider class="mt-3"></v-divider>

            <v-card-text class="notification-list-container pa-0">
              <template v-if="isLoading">
                <div class="d-flex justify-center align-center pa-8">
                  <v-progress-circular indeterminate color="primary" size="48"></v-progress-circular>
                </div>
              </template>

              <template v-else-if="!filteredNotifications.length">
                <div class="text-center pa-8">
                  <v-icon class="mb-4" color="grey" size="64">mdi-bell-off</v-icon>
                  <h6 class="text-h6 text-medium-emphasis">No notifications found</h6>
                  <p class="text-body-1 text-medium-emphasis">
                    {{ activeTab === 'unread' ? 'You have no unread notifications' : 'No notifications match your search' }}
                  </p>
                </div>
              </template>

              <template v-else>
                <v-list lines="three">
                  <v-list-item
                    v-for="(notification, index) in filteredNotifications"
                    :key="notification.id"
                    :title="notification.title"
                    :subtitle="notification.message"
                    lines="three"
                    v-gsap-fade-in="{ delay: index * 0.05 }"
                    :class="{ 'unread-notification': !notification.is_read }"
                    :data-notification-id="notification.id"
                  >
                    <template v-slot:prepend>
                      <v-avatar :color="getAvatarColorForType(notification.type)" class="mr-3">
                        <v-icon :icon="getIconForType(notification.type)" color="white"></v-icon>
                      </v-avatar>
                    </template>

                    <template v-slot:append>
                      <div class="d-flex flex-column align-end">
                        <span class="text-caption text-medium-emphasis">{{ formatDate(notification.created_at) }}</span>
                        <div class="mt-2 d-flex">
                          <v-btn
                            v-if="!notification.is_read"
                            variant="text"
                            color="primary"
                            size="small"
                            @click.stop="handleMarkAsRead(notification.id)"
                          >
                            Mark as read
                          </v-btn>
                          <v-btn
                            v-if="notification.link"
                            variant="text"
                            color="primary"
                            size="small"
                            @click.stop="navigateToLink(notification)"
                            class="ms-2"
                          >
                            View
                          </v-btn>
                        </div>
                      </div>
                    </template>
                  </v-list-item>
                </v-list>
              </template>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'pinia';
import { useNotificationStore } from '@/stores/notificationStore';
import { useRouter } from 'vue-router';
import { gsap } from 'gsap';

export default {
  name: 'NotificationListView',

  data() {
    return {
      activeTab: 'all',
      searchQuery: '',
      isComponentMounted: false,
    };
  },

  computed: {
    ...mapState(useNotificationStore, ['notifications', 'unreadNotificationCount', 'isLoading']),
    ...mapGetters(useNotificationStore, ['hasUnreadNotifications']),

    router() {
      return useRouter();
    },

    filteredNotifications() {
      let notifs = [...this.notifications];

      // Filter by tab
      if (this.activeTab === 'unread') {
        notifs = notifs.filter(n => !n.is_read);
      }

      // Filter by search query
      if (this.searchQuery.trim()) {
        const query = this.searchQuery.toLowerCase();
        notifs = notifs.filter(n =>
          n.title.toLowerCase().includes(query) ||
          n.message.toLowerCase().includes(query)
        );
      }

      return notifs;
    }
  },

  methods: {
    ...mapActions(useNotificationStore, ['fetchNotifications', 'markAsRead', 'markAllAsRead']),

    async handleMarkAsRead(notificationId) {
      await this.markAsRead(notificationId);

      // Add a nice animation effect on the row
      const element = document.querySelector(`[data-notification-id="${notificationId}"]`);
      if (element) {
        gsap.to(element, {
          backgroundColor: 'rgba(var(--v-theme-success), 0.1)',
          duration: 0.3,
          onComplete: () => {
            gsap.to(element, {
              backgroundColor: 'transparent',
              duration: 0.5,
              delay: 0.5
            });
          }
        });
      }
    },

    async handleMarkAllAsRead() {
      await this.markAllAsRead();

      // Add a nice ripple effect across all notifications
      const elements = document.querySelectorAll('.unread-notification');
      elements.forEach((el, index) => {
        gsap.to(el, {
          backgroundColor: 'rgba(var(--v-theme-success), 0.1)',
          duration: 0.3,
          delay: index * 0.05,
          onComplete: () => {
            gsap.to(el, {
              backgroundColor: 'transparent',
              duration: 0.5,
              delay: 0.5
            });
          }
        });
      });
    },

    navigateToLink(notification) {
      if (!notification.is_read) {
        this.markAsRead(notification.id);
      }

      if (notification.link) {
        this.router.push(notification.link);
      }
    },

    formatDate(timestamp) {
      if (!timestamp) return '';

      const date = new Date(timestamp);
      const now = new Date();

      const diffMs = now - date;
      const diffMinutes = Math.floor(diffMs / 60000);
      const diffHours = Math.floor(diffMinutes / 60);
      const diffDays = Math.floor(diffHours / 24);

      // Today
      if (diffDays === 0) {
        return `Today at ${date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
      }

      // Yesterday
      if (diffDays === 1) {
        return `Yesterday at ${date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
      }

      // Within the last week
      if (diffDays < 7) {
        return date.toLocaleDateString([], { weekday: 'long' }) +
               ` at ${date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
      }

      // Older
      return date.toLocaleDateString([], {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
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

    getAvatarColorForType(type) {
      switch (type) {
        case 'success': return 'success';
        case 'warning': return 'warning';
        case 'error': return 'error';
        case 'info': return 'info';
        default: return 'primary';
      }
    }
  },

  mounted() {
    this.isComponentMounted = true;
    this.fetchNotifications();

    // Add entrance animation for the page
    gsap.from('.notification-list-view', {
      opacity: 0,
      y: 20,
      duration: 0.5,
      ease: 'power2.out'
    });
  }
};
</script>

<style lang="scss" scoped>
.notification-list-container {
  min-height: 200px;
}

.unread-notification {
  background-color: rgba(var(--v-theme-primary), 0.04);
}

.v-list-item {
  &:not(:last-child) {
    border-bottom: 1px solid rgba(var(--v-theme-on-surface), 0.08);
  }
}
</style>
