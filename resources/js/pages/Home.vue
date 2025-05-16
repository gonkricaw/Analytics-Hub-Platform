<!-- resources/js/pages/Home.vue -->
<template>
  <DefaultLayout>
    <v-container fluid>
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
            <v-card-title class="primary text-h5 white--text">
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

      <!-- Widgets Row -->
      <v-row>
        <!-- Will be populated with actual widgets in Phase 2 -->
        <v-col v-for="i in 4" :key="i" cols="12" md="3" sm="6">
          <v-card
            class="mb-4 widget-card"
            elevation="2"
            height="200"
            v-gsap-hover="{ scale: 1.05 }"
            v-gsap-fade-in="{ duration: 0.8, delay: 0.4 + (i * 0.1) }"
          >
            <v-card-title>Widget {{ i }}</v-card-title>
            <v-card-text>
              This is a placeholder for the analytics widgets that will be implemented in Phase 2.
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
  name: 'HomePage',
  components: {
    DefaultLayout,
    JumbotronCarousel,
    DigitalClock
  }
};
</script>

<style lang="scss" scoped>
.widget-card {
  transition: transform 0.3s ease;

  &:hover {
    transform: translateY(-5px);
  }
}
</style>
