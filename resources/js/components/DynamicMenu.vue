<!-- resources/js/components/DynamicMenu.vue -->
<template>
  <div class="d-flex align-center">
    <!-- Dynamic Menu -->
    <template v-for="(menuItem, index) in menus" :key="`menu-${index}`">
      <!-- If the menu has children, show dropdown -->
      <v-menu v-if="menuItem.children && menuItem.children.length > 0"
        open-on-hover transition="slide-y-transition">
        <template v-slot:activator="{ props }">
          <v-btn class="mx-1" v-bind="props" variant="text" color="white">
            <v-icon start class="mr-1">{{ menuItem.icon || 'mdi-menu' }}</v-icon>
            {{ menuItem.name }}
          </v-btn>
        </template>

        <v-list class="bg-grey-darken-4">
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
      <v-btn v-else color="white" variant="text" class="mx-1"
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

  computed: {
    ...mapState(useMenuStore, ['menus', 'isLoading'])
  },

  mounted() {
    this.loadMenus();
    this.setupMenuAnimations();
  },

  methods: {
    ...mapActions(useMenuStore, ['fetchMenus']),

    async loadMenus() {
      if (this.menus.length === 0) {
        await this.fetchMenus();
      }
    },

    handleMenuItemClick(menuItem) {
      // Track menu item click for analytics/popularity
      if (menuItem.id) {
        this.trackMenuClick(menuItem.id);
      }
    },

    trackMenuClick(menuId) {
      // This would be implemented in a future phase to track menu popularity
      console.log('Menu click tracked:', menuId);
    },

    setupMenuAnimations() {
      // Add GSAP animations for menu items
      gsap.from('.v-btn', {
        opacity: 0,
        y: -10,
        stagger: 0.1,
        duration: 0.5,
        ease: 'power1.out',
        delay: 0.2
      });
    }
  }
};
</script>

<style lang="scss" scoped>
// Menu hover animation
:deep(.v-btn) {
  transition: transform 0.2s ease-in-out;

  &:hover {
    transform: translateY(-2px);
  }
}
</style>
