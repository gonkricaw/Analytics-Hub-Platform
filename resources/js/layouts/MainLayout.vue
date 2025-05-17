<!-- resources/js/layouts/MainLayout.vue -->
<template>
  <div>
    <!-- App Bar - Fixed horizontal navigation -->
    <v-app-bar app color="primary" elevation="1" fixed>
      <!-- Logo and title -->
      <v-avatar class="mr-3" size="36">
        <img src="@assets/images/logo.png" alt="Indonet Analytics Hub" />
      </v-avatar>
      <v-app-bar-title class="text-white font-weight-bold">
        Indonet Analytics Hub
      </v-app-bar-title>

      <v-spacer></v-spacer>

      <!-- Dynamic Menu -->
      <DynamicMenu />

      <v-spacer></v-spacer>

      <!-- Notification Bell with badge and pulse animation -->
      <v-btn icon class="notification-bell mr-2" v-gsap-pulse="{
        duration: 2,
        scale: 1.1,
        active: hasUnreadNotifications
      }" @click="openNotifications">
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

      <!-- User Menu -->
      <v-menu transition="slide-y-transition" :close-on-content-click="false">
        <template v-slot:activator="{ props }">
          <v-btn class="ml-2" icon v-bind="props">
            <v-avatar size="36">
              <v-img v-if="user && user.avatar" :src="user.avatar" alt="User Avatar"></v-img>
              <v-icon v-else>mdi-account-circle</v-icon>
            </v-avatar>
          </v-btn>
        </template>

        <v-card min-width="250" class="user-menu">
          <v-card-text class="text-center pb-0">
            <v-avatar size="80" class="mt-2 mb-2">
              <v-img v-if="user && user.avatar" :src="user.avatar" alt="User Avatar"></v-img>
              <v-icon v-else size="48">mdi-account-circle</v-icon>
            </v-avatar>
            <div class="text-h6 mt-2">{{ userDisplayName }}</div>
            <div class="text-subtitle-2 text-grey">{{ user?.email || '' }}</div>
            <div v-if="user?.role" class="text-caption text-primary mt-1">
              {{ user.role }}
            </div>
          </v-card-text>

          <v-divider class="mt-3"></v-divider>

          <v-list density="compact" nav>
            <v-list-item to="/profile" prepend-icon="mdi-account-cog" title="Profile" link></v-list-item>
            <v-list-item @click="logout" prepend-icon="mdi-logout" title="Logout" link></v-list-item>
          </v-list>
        </v-card>
      </v-menu>
    </v-app-bar>

    <!-- Main Content Area -->
    <v-main>
      <v-container fluid class="pt-8">
        <!-- Page transition wrapper -->
        <transition name="fade" mode="out-in" @enter="onEnter" @leave="onLeave">
          <slot></slot>
        </transition>
      </v-container>
    </v-main>

    <!-- Footer -->
    <v-footer color="primary" dark app>
      <v-container>
        <v-row justify="center" no-gutters>
          <v-col class="text-center" cols="12">
            {{ footerText }}
          </v-col>
        </v-row>
      </v-container>
    </v-footer>
  </div>
</template>

<script>
import { mapState, mapActions } from 'pinia';
import { useAuthStore } from '@stores/authStore';
import { useNotificationStore } from '@stores/notificationStore';
import { useSystemConfigStore } from '@stores/systemConfigStore';
import gsap from 'gsap';
import DynamicMenu from '@components/DynamicMenu.vue';

export default {
  name: 'MainLayout',

  components: {
    DynamicMenu
  },

  computed: {
    ...mapState(useAuthStore, ['user', 'isAuthenticated']),
    ...mapState(useNotificationStore, ['unreadNotificationCount']),
    ...mapState(useSystemConfigStore, ['footerText']),

    userDisplayName() {
      return this.user?.name || 'Guest';
    },

    hasUnreadNotifications() {
      return this.unreadNotificationCount > 0;
    }
  },

  methods: {
    ...mapActions(useAuthStore, ['logout']),
    ...mapActions(useNotificationStore, ['openNotificationPanel']),

    openNotifications() {
      this.openNotificationPanel();
    },

    // GSAP Animations for page transitions
    onEnter(el, done) {
      gsap.from(el, {
        opacity: 0,
        y: 20,
        duration: 0.5,
        onComplete: done
      });
    },

    onLeave(el, done) {
      gsap.to(el, {
        opacity: 0,
        y: -20,
        duration: 0.3,
        onComplete: done
      });
    }
  }
};
</script>

<style lang="scss" scoped>
.notification-bell {
  position: relative;
  z-index: 1;
}

.user-menu {
  overflow: hidden;
}
</style>
