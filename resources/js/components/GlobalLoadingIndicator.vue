<!-- resources/js/components/GlobalLoadingIndicator.vue -->
<template>
  <transition
    @enter="onEnter"
    @leave="onLeave"
  >
    <div v-if="isLoading" class="loading-overlay">
      <v-progress-circular
        indeterminate
        color="primary"
        size="64"
        width="4"
      ></v-progress-circular>
      <div class="loading-text mt-4" ref="loadingText">Loading...</div>
    </div>
  </transition>

  <!-- Alternative linear progress indicator at the top of the app -->
  <transition
    @enter="onEnterLinear"
    @leave="onLeaveLinear"
  >
    <div v-if="isLoading" class="linear-loader" ref="linearLoader">
      <v-progress-linear
        indeterminate
        color="primary"
        height="3"
      ></v-progress-linear>
    </div>
  </transition>
</template>

<script>
import { mapState } from 'pinia';
import { useLayoutStore } from '@stores/layoutStore';

export default {
  name: 'GlobalLoadingIndicator',

  computed: {
    ...mapState(useLayoutStore, ['isLoading'])
  },

  methods: {
    onEnter(el, done) {
      const gsap = this.$gsap;
      const tl = gsap.timeline({
        onComplete: done
      });

      tl.fromTo(el,
        {
          opacity: 0
        },
        {
          opacity: 1,
          duration: 0.3,
          ease: 'power2.inOut'
        }
      );

      // If the text element exists, animate it separately
      if (this.$refs.loadingText) {
        tl.fromTo(this.$refs.loadingText,
          {
            opacity: 0,
            y: 10
          },
          {
            opacity: 1,
            y: 0,
            duration: 0.4,
            ease: 'back.out'
          },
          "-=0.1" // Start slightly before previous animation ends
        );
      }
    },

    onLeave(el, done) {
      const gsap = this.$gsap;
      gsap.to(el, {
        opacity: 0,
        duration: 0.2,
        onComplete: done
      });
    },

    onEnterLinear(el, done) {
      const gsap = this.$gsap;
      gsap.fromTo(el,
        {
          scaleX: 0,
          opacity: 0
        },
        {
          scaleX: 1,
          opacity: 1,
          duration: 0.4,
          ease: 'power1.inOut',
          onComplete: done
        }
      );
    },

    onLeaveLinear(el, done) {
      const gsap = this.$gsap;
      gsap.to(el, {
        opacity: 0,
        duration: 0.2,
        onComplete: done
      });
    }
  }
};
</script>

<style lang="scss" scoped>
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.8);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.loading-text {
  color: white;
  font-size: 1.2rem;
  letter-spacing: 1px;
}

.linear-loader {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 9998;
  transform-origin: left center;
}
</style>
