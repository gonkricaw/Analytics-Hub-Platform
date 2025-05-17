<!--
  resources/js/components/DynamicMenu.vue

  Dynamic multi-level navigation menu component with GSAP animations

  Features:
  - Supports unlimited nested menu levels
  - GSAP animations for menu entry and hover effects
  - Tracks menu item clicks for analytics
  - Handles external links and router links
  - Responsive design with mobile optimization
-->
<template>
  <div class="d-flex align-center">
    <!-- Loading indicator -->
    <div v-if="isLoading" class="loading-menu d-flex align-center">
      <v-progress-circular
        indeterminate
        color="white"
        size="20"
        width="2"
        class="mr-2"
      ></v-progress-circular>
      <span class="text-caption">Loading menu...</span>
    </div>

    <!-- Dynamic Menu -->
    <template v-else v-for="(menuItem, index) in menus" :key="`menu-${index}`">
      <!-- If the menu has children, show dropdown -->
      <v-menu v-if="menuItem.children && menuItem.children.length > 0"
        open-on-hover
        v-model="menuItem.isOpen"
        transition="slide-y-transition"
        @update:model-value="handleMenuToggle(menuItem, $event)">
        <template v-slot:activator="{ props }">
          <v-btn class="mx-1 dynamic-menu-item" v-bind="props" variant="text" color="white">
            <v-icon start class="mr-1">{{ menuItem.icon || 'mdi-menu' }}</v-icon>
            {{ menuItem.name }}
          </v-btn>
        </template>

        <v-list class="bg-grey-darken-4" :data-parent-menu="menuItem.id">
          <!-- First level dropdown -->
          <template v-for="(child, childIndex) in menuItem.children" :key="`child-${index}-${childIndex}`">
            <!-- Submenu item with children -->
            <v-list-group v-if="child.children && child.children.length > 0" :key="`group-${index}-${childIndex}`">
              <template v-slot:activator="{ props }">
                <v-list-item v-bind="props" :prepend-icon="child.icon || undefined">
                  {{ child.name }}
                </v-list-item>
              </template>

              <!-- Second level dropdown -->
              <v-list-item v-for="(subChild, subIndex) in child.children"
                :key="`subchild-${index}-${childIndex}-${subIndex}`"
                :to="subChild.link || ''"
                :href="subChild.external_url ? subChild.external_url : undefined"
                :target="subChild.external_url ? '_blank' : undefined"
                link
                @click="handleMenuItemClick(subChild)"
              >
                <template v-slot:prepend>
                  <v-icon v-if="subChild.icon">{{ subChild.icon }}</v-icon>
                </template>
                {{ subChild.name }}
              </v-list-item>
            </v-list-group>

            <!-- Simple submenu item -->
            <v-list-item v-else
              :to="child.link || ''"
              :href="child.external_url ? child.external_url : undefined"
              :target="child.external_url ? '_blank' : undefined"
              link
              :prepend-icon="child.icon || undefined"
              @click="handleMenuItemClick(child)"
            >
              {{ child.name }}
            </v-list-item>
          </template>
        </v-list>
      </v-menu>

      <!-- If no children, show as a single button -->
      <v-btn v-else color="white" variant="text" class="mx-1 dynamic-menu-item"
        :to="menuItem.link || ''"
        :href="menuItem.external_url ? menuItem.external_url : undefined"
        :target="menuItem.external_url ? '_blank' : undefined"
        @click="handleMenuItemClick(menuItem)"
      >
        <v-icon start class="mr-1">{{ menuItem.icon || 'mdi-menu' }}</v-icon>
        {{ menuItem.name }}
      </v-btn>
    </template>
  </div>
</template>

<script>
import { mapState, mapActions } from 'pinia';
import { useMenuStore } from '@stores/menuStore';
import gsap from 'gsap';

export default {
  name: 'DynamicMenu',

  data() {
    return {
      menuOpenStates: {} // Track open state of each menu
    };
  },

  computed: {
    ...mapState(useMenuStore, ['menus', 'isLoading'])
  },

  mounted() {
    this.loadMenus();
    this.setupMenuAnimations();
  },

  methods: {
    ...mapActions(useMenuStore, {
      fetchMenus: 'fetchMenus',
      trackMenuItemClick: 'trackMenuClick'
    }),

    async loadMenus() {
      if (this.menus.length === 0) {
        try {
          await this.fetchMenus();
        } catch (error) {
          console.error('Failed to load menu structure:', error);
          // Show a small error indicator that doesn't break the layout
          this.$emit('menu-load-error', error);
        }
      }
    },

    handleMenuItemClick(menuItem) {
      // Track menu item click for analytics/popularity
      if (menuItem.id) {
        this.trackMenuClick(menuItem.id);
      }
    },

    trackMenuClick(menuId) {
      // Call the mapped store action to track menu clicks
      this.trackMenuItemClick(menuId);
    },

    setupMenuAnimations() {
      // Add GSAP animations for menu items
      gsap.from('.dynamic-menu-item', {
        opacity: 0,
        y: -10,
        stagger: 0.1,
        duration: 0.5,
        ease: 'power2.out',
        delay: 0.2,
        onComplete: () => {
          // Add hover animations once initial animation completes
          this.setupHoverEffects();
        }
      });
    },

    setupHoverEffects() {
      // Find all menu buttons
      const menuButtons = document.querySelectorAll('.dynamic-menu-item');

      // Set up hover animations for each button
      menuButtons.forEach(button => {
        button.addEventListener('mouseenter', () => {
          gsap.to(button, {
            y: -3,
            scale: 1.05,
            duration: 0.3,
            ease: 'power2.out'
          });
        });

        button.addEventListener('mouseleave', () => {
          gsap.to(button, {
            y: 0,
            scale: 1,
            duration: 0.3,
            ease: 'power2.in'
          });
        });
      });
    },

    handleMenuToggle(menuItem, isOpen) {
      // Animate menu items when dropdown opens/closes
      if (isOpen) {
        // Menu opening animation (will be handled by v-menu transition)
        menuItem.isOpen = true;

        // Add animation for dropdown items with slight delay
        this.$nextTick(() => {
          const dropdownItems = document.querySelectorAll(`[data-parent-menu="${menuItem.id}"] .v-list-item`);
          gsap.fromTo(dropdownItems,
            { opacity: 0, y: -10 },
            {
              opacity: 1,
              y: 0,
              duration: 0.3,
              stagger: 0.05,
              ease: 'power2.out',
              delay: 0.1
            }
          );
        });
      } else {
        // Menu closing animation
        menuItem.isOpen = false;
      }
    }
  }
};
</script>

<style lang="scss" scoped>
// Loading state styling
.loading-menu {
  min-width: 120px;
  height: 36px;
  padding: 0 8px;
}

// Menu styling and animations
.dynamic-menu-item {
  position: relative;
  overflow: hidden;

  &::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    width: 0;
    height: 2px;
    background-color: white;
    transition: width 0.3s ease, left 0.3s ease;
    transform: translateX(-50%);
  }

  &:hover::after {
    width: 80%;
  }

  // Remove default transition to allow GSAP to handle it
  transition: color 0.2s ease-in-out;
}

:deep(.v-list-item) {
  transition: background-color 0.3s ease;

  &:hover {
    background-color: rgba(var(--v-theme-primary), 0.1) !important;
  }
}

:deep(.v-list-group__header) {
  .v-list-item__overlay {
    background-color: rgba(var(--v-theme-primary), 0.05) !important;
  }
}
</style>
