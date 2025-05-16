<!-- resources/js/components/DigitalClock.vue -->
<template>
  <v-card class="digital-clock-card pa-4" elevation="2">
    <div class="text-center">
      <div class="text-h4 primary--text">{{ timeString }}</div>
      <div class="text-subtitle-2">{{ dateString }}</div>
    </div>
  </v-card>
</template>

<script>
export default {
  name: 'DigitalClock',
  data() {
    return {
      time: new Date(),
      timer: null
    };
  },
  computed: {
    timeString() {
      return this.time.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    },
    dateString() {
      return this.time.toLocaleDateString([], { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    }
  },
  mounted() {
    this.timer = setInterval(() => {
      this.time = new Date();
    }, 1000);
  },
  beforeUnmount() {
    clearInterval(this.timer);
  }
};
</script>

<style lang="scss" scoped>
.digital-clock-card {
  min-height: 100px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease-in-out;

  &:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2) !important;
  }
}
</style>
