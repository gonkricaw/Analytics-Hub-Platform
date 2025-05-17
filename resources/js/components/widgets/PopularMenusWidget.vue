<!-- resources/js/components/widgets/PopularMenusWidget.vue -->
<template>
  <v-card class="widget-card" elevation="2" v-gsap-hover="{ scale: 1.03, duration: 0.3 }">
    <v-card-title class="widget-title d-flex align-center">
      <v-icon icon="mdi-star" size="small" class="mr-2" color="primary" />
      Popular Features
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
        <v-list-subheader>Most accessed features (last 30 days)</v-list-subheader>

        <template v-if="menus.length">
          <v-list-item
            v-for="(menu, index) in menus"
            :key="menu.id"
            :title="menu.name"
            v-gsap-fade-in="{ delay: index * 0.1 }"
          >
            <template v-slot:prepend>
              <v-icon :icon="menu.icon || 'mdi-menu'" color="primary" size="small"></v-icon>
            </template>
            <template v-slot:append>
              <v-tooltip location="top" text="Times accessed">
                <template v-slot:activator="{ props }">
                  <v-chip
                    color="primary"
                    variant="tonal"
                    size="x-small"
                    class="access-count"
                    v-bind="props"
                  >
                    {{ menu.access_count }} views
                  </v-chip>
                </template>
              </v-tooltip>
            </template>
          </v-list-item>
        </template>

        <v-list-item v-if="!menus.length">
          <div class="pa-4 text-center text-medium-emphasis">
            <v-icon icon="mdi-star-off" size="large" class="mb-2"></v-icon>
            <div>No menu access data available</div>
          </div>
        </v-list-item>
      </v-list>
    </v-card-text>
  </v-card>
</template>

<script>
import { mapState, mapActions } from 'pinia';
import { useDashboardStore } from '@/stores/dashboardStore';
import { ref } from 'vue';

export default {
  name: 'PopularMenusWidget',

  setup() {
    const isLoading = ref(false);

    return {
      isLoading
    };
  },

  computed: {
    ...mapState(useDashboardStore, ['popularMenus']),

    menus() {
      return this.popularMenus || [];
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

  .access-count {
    font-size: 0.75rem;
  }
}
</style>
