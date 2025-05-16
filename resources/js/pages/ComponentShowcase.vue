<!-- resources/js/pages/ComponentShowcase.vue -->
<template>
  <DefaultLayout>
    <v-container>
      <v-row>
        <v-col cols="12">
          <v-card class="mb-6" v-gsap-fade-in>
            <v-card-title class="text-h5 bg-primary text-white">
              Component Showcase
            </v-card-title>
            <v-card-text class="pa-4">
              <p>This page showcases the various components available in the Indonet Analytics Hub Platform.</p>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Jumbotron Component -->
      <v-row>
        <v-col cols="12">
          <v-card class="mb-6" v-gsap-fade-in="{ delay: 0.1 }">
            <v-card-title class="bg-grey-darken-3">Jumbotron Carousel</v-card-title>
            <v-card-text class="pa-0">
              <JumbotronCarousel />
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Digital Clock Component -->
      <v-row>
        <v-col cols="12" md="6">
          <v-card class="mb-6" v-gsap-fade-in="{ delay: 0.2 }">
            <v-card-title class="bg-grey-darken-3">Digital Clock</v-card-title>
            <v-card-text class="pa-4">
              <DigitalClock />
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Rich Text Editor Component -->
        <v-col cols="12" md="6">
          <v-card class="mb-6" v-gsap-fade-in="{ delay: 0.3 }">
            <v-card-title class="bg-grey-darken-3">Rich Text Editor</v-card-title>
            <v-card-text class="pa-4">
              <QuillEditor theme="snow" placeholder="Write something..." />
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Chart Component -->
      <v-row>
        <v-col cols="12">
          <v-card class="mb-6" v-gsap-fade-in="{ delay: 0.4 }">
            <v-card-title class="bg-grey-darken-3">ApexCharts Line Chart</v-card-title>
            <v-card-text class="pa-4">
              <apexchart
                type="line"
                height="350"
                :options="chartOptions"
                :series="series"
              ></apexchart>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Animation Showcase -->
      <v-row>
        <v-col cols="12">
          <v-card class="mb-6" v-gsap-fade-in="{ delay: 0.5 }">
            <v-card-title class="bg-grey-darken-3">GSAP Animation Examples</v-card-title>
            <v-card-text>
              <v-row>
                <v-col cols="12" md="4" class="d-flex justify-center">
                  <v-btn color="primary" size="large" v-gsap-hover>
                    Hover Me
                  </v-btn>
                </v-col>
                <v-col cols="12" md="4" class="d-flex justify-center">
                  <v-icon size="36" color="error" v-gsap-pulse>
                    mdi-bell
                  </v-icon>
                </v-col>
                <v-col cols="12" md="4" class="d-flex justify-center">
                  <v-chip color="success" v-gsap-fade-in="{ duration: 2, delay: 1 }">
                    Fade In Effect
                  </v-chip>
                </v-col>
              </v-row>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </DefaultLayout>
</template>

<script>
import DefaultLayout from '@/layouts/DefaultLayout.vue';
import JumbotronCarousel from '@/components/JumbotronCarousel.vue';
import DigitalClock from '@/components/DigitalClock.vue';

export default {
  name: 'ComponentShowcase',
  components: {
    DefaultLayout,
    JumbotronCarousel,
    DigitalClock
  },
  data() {
    return {
      chartOptions: {
        chart: {
          id: 'login-chart',
          toolbar: {
            show: false
          },
          zoom: {
            enabled: false
          },
          foreColor: '#ccc'
        },
        stroke: {
          curve: 'smooth',
          width: 3
        },
        colors: ['#8C3EFF'],
        xaxis: {
          categories: Array.from({ length: 15 }, (_, i) => {
            const date = new Date();
            date.setDate(date.getDate() - (14 - i));
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
          })
        },
        yaxis: {
          title: {
            text: 'Login Count'
          }
        },
        tooltip: {
          theme: 'dark'
        },
        grid: {
          borderColor: '#404040'
        }
      },
      series: [{
        name: 'Logins',
        data: [30, 25, 36, 30, 45, 35, 40, 60, 35, 25, 30, 45, 32, 40, 55]
      }]
    };
  }
};
</script>

<style lang="scss" scoped>
</style>
