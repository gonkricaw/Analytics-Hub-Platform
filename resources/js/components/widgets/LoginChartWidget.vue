<!-- resources/js/components/widgets/LoginChartWidget.vue -->
<template>
  <v-card class="widget-card" elevation="2" v-gsap-hover="{ scale: 1.03, duration: 0.3 }">
    <v-card-title class="widget-title d-flex align-center">
      <v-icon icon="mdi-chart-line" size="small" class="mr-2" color="primary" />
      Login Activity
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

    <v-card-text class="chart-container pa-4" ref="chartContainer">
      <div v-if="!hasData" class="no-data-placeholder text-center pa-4">
        <v-icon icon="mdi-chart-bell-curve" size="large" class="mb-2"></v-icon>
        <div class="text-medium-emphasis">No login data available</div>
      </div>

      <apexchart
        v-else
        type="line"
        height="280"
        :options="chartOptions"
        :series="series"
      ></apexchart>
    </v-card-text>
  </v-card>
</template>

<script>
import { mapState, mapActions } from 'pinia';
import { useDashboardStore } from '@/stores/dashboardStore';
import { ref, computed, onMounted, watch } from 'vue';
import gsap from 'gsap';

export default {
  name: 'LoginChartWidget',

  setup() {
    const isLoading = ref(false);
    const chartContainer = ref(null);

    // Chart configuration
    const chartOptions = {
      chart: {
        id: 'login-chart',
        toolbar: {
          show: false
        },
        zoom: {
          enabled: false
        },
        animations: {
          enabled: true,
          easing: 'easeinout',
          speed: 800,
          dynamicAnimation: {
            enabled: true,
            speed: 350
          }
        },
        foreColor: '#ccc', // Text color adapted to dark theme
        background: 'transparent'
      },
      stroke: {
        curve: 'smooth',
        width: 3
      },
      colors: ['#8C3EFF'], // Primary color from theme
      grid: {
        borderColor: 'rgba(255, 255, 255, 0.1)',
        strokeDashArray: 5,
        xaxis: {
          lines: {
            show: true
          }
        },
        padding: {
          top: 10,
          right: 10,
          bottom: 10,
          left: 10
        }
      },
      tooltip: {
        theme: 'dark',
        x: {
          show: true,
          format: 'dd MMM'
        },
        y: {
          formatter: (value) => `${value} logins`
        }
      },
      xaxis: {
        labels: {
          rotate: -45,
          rotateAlways: false,
          style: {
            fontSize: '10px'
          }
        },
        tooltip: {
          enabled: false
        }
      },
      yaxis: {
        min: 0,
        tickAmount: 4,
        labels: {
          formatter: (val) => val.toFixed(0)
        }
      }
    };

    return {
      isLoading,
      chartOptions,
      chartContainer
    };
  },

  computed: {
    ...mapState(useDashboardStore, ['loginChartData']),

    hasData() {
      return this.loginChartData &&
             this.loginChartData.data &&
             this.loginChartData.data.length > 0 &&
             this.loginChartData.data.some(value => value > 0);
    },

    series() {
      if (!this.hasData) return [];

      return [{
        name: 'Logins',
        data: this.loginChartData.data || []
      }];
    },

    chartLabels() {
      return this.loginChartData.labels || [];
    }
  },

  watch: {
    chartLabels: {
      handler(newLabels) {
        if (newLabels && newLabels.length) {
          this.chartOptions.xaxis.categories = newLabels;
        }
      },
      immediate: true
    }
  },

  mounted() {
    // Apply GSAP animation to chart container when mounted
    if (this.chartContainer) {
      gsap.from(this.chartContainer, {
        opacity: 0,
        y: 20,
        duration: 0.8,
        delay: 0.3,
        ease: 'power3.out'
      });
    }
  },

  methods: {
    ...mapActions(useDashboardStore, ['fetchDashboardData']),

    async refreshData() {
      this.isLoading = true;
      try {
        await this.fetchDashboardData();

        // Apply refresh animation
        if (this.chartContainer) {
          gsap.fromTo(
            this.chartContainer,
            { opacity: 0.5 },
            { opacity: 1, duration: 0.5, ease: 'power2.inOut' }
          );
        }
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

  .chart-container {
    flex: 1;
    min-height: 280px;
  }

  .no-data-placeholder {
    height: 280px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    opacity: 0.7;
  }
}
</style>
