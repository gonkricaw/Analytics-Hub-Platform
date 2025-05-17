// resources/js/stores/dashboardStore.js
import { defineStore } from 'pinia';
import dashboardService from '../services/dashboardService';
import { useLayoutStore } from './layoutStore';

export const useDashboardStore = defineStore('dashboard', {
  state: () => ({
    dashboardData: {
      widgets: {
        online_users: [],
        login_history: [],
        login_chart: { labels: [], data: [] },
        latest_notifications: [],
        popular_menus: []
      },
      configs: {},
      announcements: []
    },
    isLoading: false,
    error: null
  }),

  getters: {
    /**
     * Get top online users currently active on the platform
     */
    topOnlineUsers: (state) => state.dashboardData.widgets.online_users || [],

    /**
     * Get users with most frequent logins
     */
    frequentLoginUsers: (state) => state.dashboardData.widgets.login_history || [],

    /**
     * Get login chart data for visualization
     */
    loginChartData: (state) => state.dashboardData.widgets.login_chart || { labels: [], data: [] },

    /**
     * Get latest system notifications
     */
    latestNotifications: (state) => state.dashboardData.widgets.latest_notifications || [],

    /**
     * Get most popular menu items based on user activity
     */
    popularMenus: (state) => state.dashboardData.widgets.popular_menus || [],

    /**
     * Get system announcements for marquee text
     */
    announcements: (state) => {
      if (state.dashboardData.configs && state.dashboardData.configs.system_announcements) {
        return state.dashboardData.configs.system_announcements;
      }

      return state.dashboardData.announcements || [];
    },

    /**
     * Get jumbotron carousel slides
     */
    carouselSlides: (state) => {
      if (state.dashboardData.configs && state.dashboardData.configs.jumbotron_slides) {
        return state.dashboardData.configs.jumbotron_slides;
      }

      // Default slides if not provided by backend
      return [
        {
          src: 'https://picsum.photos/1920/300?random=1',
          title: 'Welcome to Indonet Analytics Hub',
          text: 'Your central platform for analytics and insights'
        },
        {
          src: 'https://picsum.photos/1920/300?random=2',
          title: 'Powerful Data Visualization',
          text: 'Transform your data into meaningful insights'
        },
        {
          src: 'https://picsum.photos/1920/300?random=3',
          title: 'Stay Updated',
          text: 'Get real-time updates and notifications'
        }
      ];
    }
  },

  actions: {
    /**
     * Fetch all dashboard data from the API
     */
    async fetchDashboardData() {
      const layoutStore = useLayoutStore();

      try {
        this.isLoading = true;
        layoutStore.startLoading();

        const response = await dashboardService.getDashboardData();

        if (response.data) {
          this.dashboardData = response.data;

          // Make sure we have reasonable defaults if API doesn't provide certain data
          if (!this.dashboardData.widgets) {
            this.dashboardData.widgets = {
              online_users: [],
              login_history: [],
              login_chart: { labels: [], data: [] },
              latest_notifications: [],
              popular_menus: []
            };
          }

          if (!this.dashboardData.announcements) {
            this.dashboardData.announcements = [];
          }
        }

        return this.dashboardData;
      } catch (error) {
        this.error = error;
        layoutStore.handleError(error, 'Failed to load dashboard data');
        return null;
      } finally {
        this.isLoading = false;
        layoutStore.stopLoading();
      }
    },

    /**
     * Clear dashboard data
     */
    clearDashboardData() {
      this.dashboardData = {
        widgets: {
          online_users: [],
          login_history: [],
          login_chart: { labels: [], data: [] },
          latest_notifications: [],
          popular_menus: []
        },
        configs: {},
        announcements: []
      };
      this.error = null;
    }
  }
});
