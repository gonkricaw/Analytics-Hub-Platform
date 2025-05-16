<!-- resources/js/layouts/DefaultLayout.vue -->
<template>
  <v-app>
    <!-- App Bar -->
    <v-app-bar app color="primary" elevation="1">
      <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>
      <v-app-bar-title>Indonet Analytics Hub</v-app-bar-title>

      <v-spacer></v-spacer>

      <!-- Notifications Button with pulse animation -->
      <v-btn icon class="notification-btn" v-gsap-pulse="{ duration: 2, scale: 1.1 }">
        <v-badge
          :content="unreadNotificationsCount"
          :value="unreadNotificationsCount"
          color="error"
          offset-x="15"
          offset-y="15"
        >
          <v-icon>mdi-bell</v-icon>
        </v-badge>
      </v-btn>

      <!-- User Menu -->
      <v-menu transition="slide-y-transition" :close-on-content-click="false">
        <template v-slot:activator="{ props }">
          <v-btn class="ml-2" icon v-bind="props">
            <v-avatar color="primary" size="40">
              <v-icon v-if="!currentUser.avatar" icon="mdi-account"></v-icon>
              <img v-else :src="currentUser.avatar" alt="User Avatar" />
            </v-avatar>
          </v-btn>
        </template>

        <v-card min-width="200">
          <v-card-text>
            <div class="text-h6 pb-2">{{ currentUser.name || 'Guest' }}</div>
            <div class="text-subtitle-2 pb-4">{{ currentUser.email || '' }}</div>

            <v-list density="compact" nav>
              <v-list-item link prepend-icon="mdi-account-cog">
                Profile Settings
              </v-list-item>
              <v-list-item @click="logout" link prepend-icon="mdi-logout">
                Logout
              </v-list-item>
            </v-list>
          </v-card-text>
        </v-card>
      </v-menu>
    </v-app-bar>

    <!-- Navigation Drawer -->
    <v-navigation-drawer v-model="drawer" temporary>
      <v-list>
        <v-list-item
          title="Home"
          prepend-icon="mdi-home"
          to="/"
          :ripple="true"
        ></v-list-item>
        <v-list-item
          title="Dashboard"
          prepend-icon="mdi-view-dashboard"
          to="/dashboard"
          :ripple="true"
        ></v-list-item>
        <!-- Dynamic menu items will be added here in future phases -->
      </v-list>
    </v-navigation-drawer>

    <!-- Main Content -->
    <v-main>
      <!-- Page content -->
      <slot></slot>
    </v-main>

    <!-- Footer -->
    <v-footer app color="primary" class="text-center d-flex flex-column">
      <div class="pa-2 text-center w-100">
        <p class="mb-0 text-caption">
          &copy; {{ new Date().getFullYear() }} Indonet Analytics Hub. All rights reserved.
        </p>
      </div>
    </v-footer>
  </v-app>
</template>

<script>
export default {
  name: 'DefaultLayout',
  data() {
    return {
      drawer: false,
      unreadNotificationsCount: 5, // Placeholder for unread notifications
      currentUser: {
        name: 'Demo User',
        email: 'demo@example.com',
        avatar: null
      }
    };
  },
  methods: {
    logout() {
      // Logout logic will be implemented in Phase 2
      console.log('Logout clicked');
    }
  }
};
</script>

<style lang="scss" scoped>
.v-app-bar {
  .v-toolbar-title {
    font-weight: 500;
  }
}

.notification-btn {
  margin-right: 8px;
}
</style>
