<!-- resources/js/components/MarqueeText.vue -->
<template>
  <div
    class="marquee-container"
    ref="marqueeContainer"
    @mouseenter="pauseScroll"
    @mouseleave="resumeScroll"
  >
    <div class="marquee-inner" ref="marqueeText">
      <span v-if="announcements.length" class="announcement-item">
        <v-icon icon="mdi-bullhorn" class="mr-2" />
        <template v-for="(announcement, index) in announcements" :key="index">
          <span class="announcement-text">{{ announcement.text || announcement }}</span>
          <v-icon icon="mdi-circle-small" class="mx-2" v-if="index < announcements.length - 1" />
        </template>
      </span>
      <span v-else class="announcement-item">
        <v-icon icon="mdi-bullhorn" class="mr-2" />
        <span class="announcement-text">Welcome to Indonet Analytics Hub Platform</span>
      </span>
    </div>
  </div>
</template>

<script>
import gsap from 'gsap';

export default {
  name: 'MarqueeText',

  props: {
    announcements: {
      type: Array,
      default: () => []
    },
    duration: {
      type: Number,
      default: 20
    },
    pauseOnHover: {
      type: Boolean,
      default: true
    }
  },

  data() {
    return {
      animation: null
    };
  },

  mounted() {
    this.$nextTick(() => {
      this.initScrollAnimation();
    });

    // Re-init animation if announcements change
    this.$watch('announcements', () => {
      if (this.animation) {
        this.animation.kill();
      }

      this.$nextTick(() => {
        this.initScrollAnimation();
      });
    });

    // Handle window resize
    window.addEventListener('resize', this.handleResize);
  },

  beforeUnmount() {
    if (this.animation) {
      this.animation.kill();
    }
    window.removeEventListener('resize', this.handleResize);
  },

  methods: {
    initScrollAnimation() {
      const marquee = this.$refs.marqueeText;

      if (!marquee) return;

      // Calculate the width and ensure it's actually overflowing
      const containerWidth = this.$refs.marqueeContainer.clientWidth;
      const textWidth = marquee.clientWidth;

      if (textWidth <= containerWidth) {
        // Not overflowing, no need for animation
        return;
      }

      // Calculate speed based on text length (longer text = faster scroll)
      const scrollDistance = textWidth;
      const speedFactor = scrollDistance / 200; // pixels per second
      const scrollDuration = this.duration * speedFactor;

      // Create GSAP animation
      this.animation = gsap.to(marquee, {
        x: -scrollDistance,
        duration: scrollDuration,
        ease: 'linear',
        repeat: -1, // infinite repeat
        onRepeat: () => {
          gsap.set(marquee, { x: containerWidth });
        }
      });
    },

    pauseScroll() {
      if (this.pauseOnHover && this.animation) {
        this.animation.pause();
      }
    },

    resumeScroll() {
      if (this.pauseOnHover && this.animation) {
        this.animation.play();
      }
    },

    handleResize() {
      if (this.animation) {
        this.animation.kill();
      }

      this.$nextTick(() => {
        this.initScrollAnimation();
      });
    }
  }
};
</script>

<style lang="scss" scoped>
.marquee-container {
  position: relative;
  width: 100%;
  height: 40px;
  overflow: hidden;
  background-color: rgba(140, 62, 255, 0.1); // Primary color with low opacity
  border-radius: 4px;
  display: flex;
  align-items: center;
}

.marquee-inner {
  position: absolute;
  white-space: nowrap;
  padding: 0 20px;
  will-change: transform;
  display: flex;
  align-items: center;
}

.announcement-item {
  display: flex;
  align-items: center;
  color: var(--v-primary-base);
  font-weight: 500;
}

.announcement-text {
  opacity: 0.9;
}
</style>
