<!-- resources/js/views/EmbedView.vue -->
<template>
  <div class="embed-view" :class="{ 'is-loading': isLoading }">
    <!-- Loading state -->
    <div v-if="isLoading" class="embed-loading">
      <v-progress-circular
        indeterminate
        size="64"
        color="primary"
      ></v-progress-circular>
      <div class="text-body-1 mt-4">Loading external content...</div>
    </div>

    <!-- Error state -->
    <div v-else-if="error" class="embed-error">
      <v-icon icon="mdi-alert-circle-outline" size="64" color="error" class="mb-4"></v-icon>
      <h2 class="text-h5 mb-2">Unable to Load Content</h2>
      <p class="text-body-1 mb-6">The requested embedded content is not available or could not be loaded.</p>
      <v-btn
        color="primary"
        variant="outlined"
        @click="reloadEmbed"
      >
        Try Again
      </v-btn>
    </div>

    <!-- Embed iframe -->
    <iframe
      v-else-if="targetEmbedUrl"
      :src="targetEmbedUrl"
      class="embed-frame"
      frameborder="0"
      allowfullscreen
      sandbox="allow-same-origin allow-scripts allow-popups allow-forms"
      ref="embedFrame"
    ></iframe>

    <!-- No URL found -->
    <div v-else class="embed-not-found">
      <v-icon icon="mdi-link-off" size="64" color="warning" class="mb-4"></v-icon>
      <h2 class="text-h5 mb-2">Embed Not Found</h2>
      <p class="text-body-1 mb-6">The requested embed URL does not exist or has been removed.</p>
    </div>
  </div>
</template>

<script>
import { mapState, mapActions } from 'pinia';
import { useContentStore } from '@/stores/contentStore';
import { useRoute } from 'vue-router';
import gsap from 'gsap';

export default {
  name: 'EmbedView',

  computed: {
    ...mapState(useContentStore, ['embedUrl', 'isLoading', 'error', 'targetEmbedUrl']),

    route() {
      return useRoute();
    }
  },

  async mounted() {
    await this.loadEmbed();

    if (this.targetEmbedUrl && this.$refs.embedFrame) {
      this.setupLoadAnimation();
      this.setupResizeHandler();
    }
  },

  methods: {
    ...mapActions(useContentStore, ['fetchEmbedUrl']),

    async loadEmbed() {
      const { uuid } = this.route.params;

      if (uuid) {
        await this.fetchEmbedUrl(uuid);
      }
    },

    reloadEmbed() {
      this.loadEmbed();
    },

    setupLoadAnimation() {
      const frame = this.$refs.embedFrame;

      // Add load event listener to the iframe
      frame.addEventListener('load', () => {
        // Animate iframe once loaded
        gsap.fromTo(frame,
          { opacity: 0, scale: 0.95 },
          { opacity: 1, scale: 1, duration: 0.5, ease: 'power2.out' }
        );
      });
    },

    setupResizeHandler() {
      // Ensure the iframe adjusts to window resize events
      const handleResize = () => {
        if (this.$refs.embedFrame) {
          const windowHeight = window.innerHeight;
          const windowWidth = window.innerWidth;

          // Set iframe dimensions to match window size
          gsap.set(this.$refs.embedFrame, {
            height: windowHeight,
            width: windowWidth
          });
        }
      };

      // Initial setup
      handleResize();

      // Listen for resize events
      window.addEventListener('resize', handleResize);

      // Clean up on component destroy
      this.$once('hook:beforeDestroy', () => {
        window.removeEventListener('resize', handleResize);
      });
    }
  }
};
</script>

<style lang="scss" scoped>
.embed-view {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  background-color: #121212;

  &.is-loading {
    background-color: rgba(18, 18, 18, 0.95);
  }
}

.embed-frame {
  width: 100%;
  height: 100%;
  border: none;
}

.embed-loading,
.embed-error,
.embed-not-found {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 32px;
  max-width: 500px;
}

.embed-loading {
  animation: pulse 1.5s infinite alternate;
}

@keyframes pulse {
  from {
    opacity: 0.7;
  }
  to {
    opacity: 1;
  }
}
</style>
