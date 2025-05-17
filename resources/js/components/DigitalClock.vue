<!-- resources/js/components/DigitalClock.vue -->
<template>
  <v-card
    class="digital-clock-card"
    elevation="2"
    v-gsap-hover="{ scale: 1.03, duration: 0.3 }"
  >
    <v-card-title class="clock-title d-flex align-center">
      <v-icon icon="mdi-clock" size="small" class="mr-2" color="primary" />
      Current Time
    </v-card-title>

    <v-divider></v-divider>

    <v-card-text class="pa-4">
      <div class="text-center">
        <div class="time-container">
          <div class="text-h4 primary--text time-display" ref="timeDisplay">{{ timeString }}</div>
        </div>
        <div class="text-subtitle-2 date-display" ref="dateDisplay">{{ dateString }}</div>
      </div>
    </v-card-text>
  </v-card>
</template>

<script>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import gsap from 'gsap';

export default {
  name: 'DigitalClock',

  setup() {
    const time = ref(new Date());
    let timer = null;
    const timeDisplay = ref(null);
    const dateDisplay = ref(null);

    // Format time with leading zeros
    const timeString = computed(() => {
      return time.value.toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
      });
    });

    // Format date in a readable format
    const dateString = computed(() => {
      return time.value.toLocaleDateString([], {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    });

    onMounted(() => {
      // Update time every second
      timer = setInterval(() => {
        const oldTime = time.value;
        time.value = new Date();

        // Animate seconds change
        if (timeDisplay.value && oldTime.getSeconds() !== time.value.getSeconds()) {
          gsap.fromTo(
            timeDisplay.value,
            { opacity: 0.7, scale: 0.98 },
            { opacity: 1, scale: 1, duration: 0.3, ease: 'power2.out' }
          );
        }

        // Animate date change at midnight
        if (dateDisplay.value &&
            oldTime.getDate() !== time.value.getDate()) {
          gsap.fromTo(
            dateDisplay.value,
            { y: -20, opacity: 0 },
            { y: 0, opacity: 1, duration: 0.5, ease: 'back.out' }
          );
        }
      }, 1000);
    });

    onBeforeUnmount(() => {
      if (timer) {
        clearInterval(timer);
      }
    });

    return {
      time,
      timeString,
      dateString,
      timeDisplay,
      dateDisplay
    };
  }
};
</script>

<style lang="scss" scoped>
.digital-clock-card {
  height: 100%;
  display: flex;
  flex-direction: column;

  .clock-title {
    font-size: 1rem;
    font-weight: 500;
    padding: 12px 16px;
    background-color: rgba(var(--v-theme-primary), 0.05);
  }

  .time-container {
    margin-bottom: 8px;
  }

  .time-display {
    font-weight: 700;
    letter-spacing: 1px;
  }

  .date-display {
    opacity: 0.85;
  }
}
</style>
