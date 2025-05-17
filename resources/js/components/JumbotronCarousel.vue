<!-- resources/js/components/JumbotronCarousel.vue -->
<template>
  <div class="jumbotron-carousel">
    <v-carousel
      cycle
      height="300"
      hide-delimiter-background
      show-arrows="hover"
      interval="5000"
    >
      <v-carousel-item
        v-for="(slide, i) in carouselSlides"
        :key="i"
        :src="slide.src"
        cover
        v-gsap-fade-in="{ delay: i * 0.2 }"
      >
        <div class="carousel-caption d-flex flex-column align-center justify-center">
          <h2 class="text-h4 font-weight-bold mb-4" v-gsap-fade-in="{ delay: 0.3 + (i * 0.1) }">
            {{ slide.title }}
          </h2>
          <p class="text-subtitle-1" v-gsap-fade-in="{ delay: 0.5 + (i * 0.1) }">
            {{ slide.text }}
          </p>
        </div>
      </v-carousel-item>
    </v-carousel>
  </div>
</template>

<script>
import { useDashboardStore } from '@/stores/dashboardStore';
import { computed, onMounted } from 'vue';
import gsap from 'gsap';

export default {
  name: 'JumbotronCarousel',

  setup() {
    const dashboardStore = useDashboardStore();
    const carouselSlides = computed(() => dashboardStore.carouselSlides);

    onMounted(() => {
      // Apply additional animations if needed
      gsap.from('.jumbotron-carousel', {
        opacity: 0,
        y: 20,
        duration: 0.8,
        ease: 'power2.out'
      });
    });

    return {
      carouselSlides
    };
  }
};
</script>

<style lang="scss" scoped>
.jumbotron-carousel {
  margin-bottom: 24px;

  .carousel-caption {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0, 0, 0, 0.6);
    padding: 20px;
    color: white;
    text-align: center;
    width: 100%;
    height: 100%;
  }
}
</style>
