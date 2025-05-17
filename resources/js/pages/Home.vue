<!-- resources/js/pages/Home.vue -->
<template>
  <DefaultLayout>
    <v-container fluid class="home-container">
      <!-- Announcements Marquee -->
      <v-row>
        <v-col cols="12">
          <MarqueeText :announcements="announcements" v-gsap-fade-in="{ duration: 0.8 }" />
        </v-col>
      </v-row>

      <!-- Jumbotron and Digital Clock Section -->
      <v-row>
        <v-col cols="12" md="9">
          <JumbotronCarousel v-gsap-fade-in="{ duration: 0.8 }" />
        </v-col>
        <v-col cols="12" md="3">
          <DigitalClock v-gsap-fade-in="{ duration: 0.8, delay: 0.2 }" />
        </v-col>
      </v-row>

      <!-- Welcome Message -->
      <v-row>
        <v-col cols="12">
          <v-card class="mb-4" elevation="2" v-gsap-fade-in="{ duration: 0.8, delay: 0.4 }">
            <v-card-title class="primary text-h5 text-white">
              Welcome to Indonet Analytics Hub
            </v-card-title>
            <v-card-text class="pa-4">
              <p class="text-body-1">
                This platform provides access to critical analytics and tools for your organization.
                Use the navigation menu to access various features and functionalities.
              </p>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Analytics Widgets -->
      <h2 class="text-h6 mb-3" v-gsap-fade-in="{ duration: 0.8, delay: 0.6 }">
        <v-icon icon="mdi-chart-box" class="mr-2" color="primary"></v-icon>
        Dashboard Analytics
      </h2>

      <!-- Top Row Widgets -->
      <v-row>
        <v-col cols="12" md="6" lg="3">
          <TopOnlineUsersWidget class="mb-4" v-gsap-fade-in="{ duration: 0.8, delay: 0.7 }" />
        </v-col>
        <v-col cols="12" md="6" lg="3">
          <FrequentlyLoginUsersWidget class="mb-4" v-gsap-fade-in="{ duration: 0.8, delay: 0.8 }" />
        </v-col>
        <v-col cols="12" md="6" lg="3">
          <LatestNotificationsWidget class="mb-4" v-gsap-fade-in="{ duration: 0.8, delay: 0.9 }" />
        </v-col>
        <v-col cols="12" md="6" lg="3">
          <PopularMenusWidget class="mb-4" v-gsap-fade-in="{ duration: 0.8, delay: 1.0 }" />
        </v-col>
      </v-row>

      <!-- Chart Widget -->
      <v-row>
        <v-col cols="12">
          <LoginChartWidget v-gsap-fade-in="{ duration: 0.8, delay: 1.1 }" />
        </v-col>
      </v-row>
    </v-container>
  </DefaultLayout>
</template>

<script>
import { onMounted, onUnmounted, computed } from 'vue';
import { mapState, mapActions } from 'pinia';
import { useDashboardStore } from '@/stores/dashboardStore';
import DefaultLayout from '@/layouts/DefaultLayout.vue';
import JumbotronCarousel from '@/components/JumbotronCarousel.vue';
import DigitalClock from '@/components/DigitalClock.vue';
import MarqueeText from '@/components/MarqueeText.vue';
import TopOnlineUsersWidget from '@/components/widgets/TopOnlineUsersWidget.vue';
import FrequentlyLoginUsersWidget from '@/components/widgets/FrequentlyLoginUsersWidget.vue';
import LatestNotificationsWidget from '@/components/widgets/LatestNotificationsWidget.vue';
import LoginChartWidget from '@/components/widgets/LoginChartWidget.vue';
import PopularMenusWidget from '@/components/widgets/PopularMenusWidget.vue';

export default {
  name: 'HomePage',
  components: {
    DefaultLayout,
    JumbotronCarousel,
    DigitalClock,
    MarqueeText,
    TopOnlineUsersWidget,
    FrequentlyLoginUsersWidget,
    LatestNotificationsWidget,
    LoginChartWidget,
    PopularMenusWidget
  },

  setup() {
    const dashboardStore = useDashboardStore();
    let refreshInterval = null;

    // Fetch dashboard data when component mounts
    onMounted(() => {
      dashboardStore.fetchDashboardData();

      // Refresh dashboard data every 5 minutes
      refreshInterval = setInterval(() => {
        dashboardStore.fetchDashboardData();
      }, 5 * 60 * 1000);
    });

    // Clean up interval when component unmounts
    onUnmounted(() => {
      if (refreshInterval) {
        clearInterval(refreshInterval);
      }
    });

    return {
      // Map state from dashboard store
      ...mapState(useDashboardStore, ['announcements'])
    };
  }
};
</script>

<style lang="scss" scoped>
.home-container {
  padding-bottom: 24px;
}
</style>
