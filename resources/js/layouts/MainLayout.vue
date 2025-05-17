<!-- resources/js/layouts/MainLayout.vue -->
<template>
  <div>
    <!-- Navigation Drawer for Mobile -->
    <v-navigation-drawer
      v-model="drawer"
      temporary
      app
      class="mobile-nav-drawer"
    >
      <v-list>
        <v-list-item class="pa-4">
          <template v-slot:prepend>
            <v-avatar size="40">
              <img src="/images/logo.png" alt="Indonet Analytics Hub" />
            </v-avatar>
          </template>
          <v-list-item-title class="text-h6">Indonet Analytics Hub</v-list-item-title>
        </v-list-item>

        <v-divider></v-divider>

        <!-- Mobile menu content - will be populated from DynamicMenu data -->
        <template v-for="(menuItem, index) in flattenedMenu" :key="`mobile-menu-${index}`">
          <v-list-item
            :to="menuItem.link || ''"
            :href="menuItem.external_url ? menuItem.external_url : undefined"
            :target="menuItem.external_url ? '_blank' : undefined"
            link
            @click="handleMenuItemClick(menuItem)"
          >
            <template v-slot:prepend>
              <v-icon>{{ menuItem.icon || 'mdi-circle-small' }}</v-icon>
            </template>
            <v-list-item-title>{{ menuItem.name }}</v-list-item-title>
          </v-list-item>
        </template>
      </v-list>
    </v-navigation-drawer>

    <!-- App Bar - Fixed horizontal navigation -->
    <v-app-bar app color="primary" elevation="1" fixed>
      <!-- Hamburger menu for mobile -->
      <v-app-bar-nav-icon
        @click="drawer = !drawer"
        class="d-flex d-md-none"
      ></v-app-bar-nav-icon>

      <!-- Logo and title -->
      <v-avatar class="mr-2" size="36">
        <img src="/images/logo.png" alt="Indonet Analytics Hub" />
      </v-avatar>
      <v-app-bar-title class="text-white font-weight-bold d-none d-sm-flex">
        Indonet Analytics Hub
      </v-app-bar-title>

      <v-spacer></v-spacer>

      <!-- Dynamic Menu - hidden on mobile -->
      <div class="d-none d-md-flex">
        <DynamicMenu @menu-updated="updateMobileMenu" />
      </div>

      <v-spacer></v-spacer>

      <!-- Notification Bell Component -->
      <NotificationBell class="mr-2" />

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

        <v-card min-width="250" max-width="90vw" class="user-menu">
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
      <v-container fluid class="pt-4 pt-sm-6 pt-md-8">
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
          <v-col class="text-center py-2" cols="12">
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
import { useMenuStore } from '@stores/menuStore';
import gsap from 'gsap';
import DynamicMenu from '@components/DynamicMenu.vue';
import NotificationBell from '@components/NotificationBell.vue';

export default {
  name: 'MainLayout',

  components: {
    DynamicMenu,
    NotificationBell
  },

  data() {
    return {
      drawer: false,
      flattenedMenu: []
    }
  },

  computed: {
    ...mapState(useAuthStore, ['user', 'isAuthenticated']),
    ...mapState(useSystemConfigStore, ['footerText']),
    userDisplayName() {
      return this.user?.name || 'Guest';
    }
  },

  methods: {
    ...mapActions(useAuthStore, ['logout']),

    // Process menus from DynamicMenu for mobile navigation
    updateMobileMenu(menus) {
      this.flattenedMenu = this.flattenMenuStructure(menus || []);
    },

    // Flatten nested menu structure for mobile display
    flattenMenuStructure(menuItems) {
      let flatMenu = [];

      menuItems.forEach(item => {
        // Add the main menu item if it has a link
        if (item.link || item.external_url) {
          flatMenu.push({
            name: item.name,
            link: item.link,
            external_url: item.external_url,
            icon: item.icon
          });
        }

        // Process any children
        if (item.children && item.children.length > 0) {
          // Add a divider for the section if the parent doesn't have a link
          if (!item.link && !item.external_url) {
            flatMenu.push({
              name: item.name,
              icon: item.icon,
              isDivider: true
            });
          }

          // Process first level children
          item.children.forEach(child => {
            flatMenu.push({
              name: child.name,
              link: child.link,
              external_url: child.external_url,
              icon: child.icon
            });

            // Process second level children if any
            if (child.children && child.children.length > 0) {
              child.children.forEach(subChild => {
                flatMenu.push({
                  name: `${subChild.name}`,
                  link: subChild.link,
                  external_url: subChild.external_url,
                  icon: subChild.icon,
                  isSubMenu: true
                });
              });
            }
          });
        }
      });

      return flatMenu;
    },

    // Handle menu item click from mobile menu
    handleMenuItemClick(item) {
      if (item.link || item.external_url) {
        this.drawer = false; // Close drawer when an item is clicked

        // Track click for analytics if needed
        const menuStore = useMenuStore();
        if (item.id && menuStore.trackMenuClick) {
          menuStore.trackMenuClick(item.id);
        }
      }
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
  },

  mounted() {
    // Start notification polling when layout is mounted
    const notificationStore = useNotificationStore();
    notificationStore.startNotificationPolling();

    // Get initial menu structure for mobile
    const menuStore = useMenuStore();
    if (menuStore.menus && menuStore.menus.length > 0) {
      this.updateMobileMenu(menuStore.menus);
    }
  },

  beforeUnmount() {
    // Stop notification polling when layout is unmounted
    const notificationStore = useNotificationStore();
    notificationStore.stopNotificationPolling();
  }
};
</script>

<style lang="scss" scoped>
.user-menu {
  overflow: hidden;
}

.mobile-nav-drawer {
  :deep(.v-list-item) {
    min-height: 48px;
    padding-top: 4px;
    padding-bottom: 4px;

    &.isSubMenu {
      padding-left: 16px;
    }
  }
}
</style>
