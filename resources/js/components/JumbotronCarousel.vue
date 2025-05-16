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
        v-for="(slide, i) in slides"
        :key="i"
        :src="slide.src"
        cover
        v-gsap-fade-in="{ delay: i * 0.2 }"
      >
        <div class="carousel-caption d-flex flex-column align-center justify-center">
          <h2 class="text-h4 font-weight-bold mb-4">{{ slide.title }}</h2>
          <p class="text-subtitle-1">{{ slide.text }}</p>
        </div>
      </v-carousel-item>
    </v-carousel>
  </div>
</template>

<script>
export default {
  name: 'JumbotronCarousel',
  data() {
    return {
      slides: [
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
      ]
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
