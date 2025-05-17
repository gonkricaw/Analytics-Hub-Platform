<!-- resources/js/components/PopularMenuWidget.vue -->
<template>
  <v-card class="popular-menu-widget elevation-3" min-height="200">
    <v-card-title class="d-flex align-center">
      <v-icon icon="mdi-chart-bar" class="mr-2 primary--text" />
      Popular Navigation
      <v-spacer></v-spacer>
      <v-btn
        icon="mdi-refresh"
        variant="text"
        size="small"
        @click="loadPopularMenuItems"
        :loading="isLoading"
      ></v-btn>
    </v-card-title>

    <v-card-text class="pt-2">
      <div v-if="isLoading" class="d-flex justify-center align-center" style="min-height: 100px">
        <v-progress-circular indeterminate></v-progress-circular>
      </div>
      <div v-else-if="popularMenuItems.length === 0" class="text-center py-4 text-grey">
        <v-icon icon="mdi-information-outline" size="large" class="mb-2"></v-icon>
        <div>No menu analytics data available yet.</div>
      </div>
      <v-list v-else density="compact" class="bg-transparent">
        <v-list-item
          v-for="(item, index) in popularMenuItems"
          :key="`popular-${index}`"
          :to="getItemRoute(item)"
          link
          :prepend-icon="getItemIcon(item)"
          class="popular-menu-item"
        >
          <v-list-item-title>
            {{ getItemName(item) }}
          </v-list-item-title>
          <template v-slot:append>
            <v-chip size="small" color="primary" class="font-weight-medium">
              {{ item.total_count }} views
            </v-chip>
          </template>
        </v-list-item>
      </v-list>
    </v-card-text>
  </v-card>
</template>

<script>
import { useMenuStore } from '@stores/menuStore';

export default {
  name: 'PopularMenuWidget',

  props: {
    limit: {
      type: Number,
      default: 5
    }
  },

  data() {
    return {
      popularMenuItems: [],
      isLoading: false
    };
  },

  mounted() {
    this.loadPopularMenuItems();
  },

  methods: {
    async loadPopularMenuItems() {
      this.isLoading = true;

      try {
        const menuStore = useMenuStore();
        this.popularMenuItems = await menuStore.getPopularMenuItems(this.limit);
      } catch (error) {
        console.error('Failed to load popular menu items', error);
      } finally {
        this.isLoading = false;
      }
    },

    getItemName(item) {
      return item.menu_item?.title || 'Unnamed Menu';
    },

    getItemRoute(item) {
      const menuItem = item.menu_item;
      if (!menuItem) return '';

      return menuItem.route_name ? { name: menuItem.route_name } : menuItem.url || '';
    },

    getItemIcon(item) {
      return item.menu_item?.icon || 'mdi-link';
    }
  }
};
</script>

<style lang="scss" scoped>
.popular-menu-widget {
  .popular-menu-item {
    border-radius: 4px;
    margin-bottom: 2px;

    &:hover {
      background-color: rgba(var(--v-theme-primary), 0.05);
    }
  }
}
</style>
