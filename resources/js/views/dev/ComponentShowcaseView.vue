<template>
  <v-container>
    <v-card class="mb-8">
      <v-card-title class="text-h4">
        GSAP Animations Showcase
        <v-icon icon="mdi-animation-play" class="ml-2"></v-icon>
      </v-card-title>
      <v-card-text>
        <p class="text-subtitle-1 mb-4">
          This page demonstrates the various GSAP animations and transitions available in the Indonet Analytics Hub Platform.
          All animations use the GSAP library and follow the MCP get-library-docs standards.
        </p>
      </v-card-text>
    </v-card>

    <!-- Page Transitions Section -->
    <v-card class="mb-8">
      <v-card-title>
        <v-icon icon="mdi-page-next" class="mr-2"></v-icon>
        Page Transitions
      </v-card-title>
      <v-card-text>
        <p class="mb-4">Page transitions are implemented in DefaultLayout.vue with GSAP animations. Click the buttons below to simulate page transitions:</p>

        <v-row>
          <v-col cols="12" sm="6" md="3">
            <v-btn color="primary" block @click="simulatePageTransition('fade')">
              Fade Transition
            </v-btn>
          </v-col>
          <v-col cols="12" sm="6" md="3">
            <v-btn color="secondary" block @click="simulatePageTransition('slide')">
              Slide Transition
            </v-btn>
          </v-col>
          <v-col cols="12" sm="6" md="3">
            <v-btn color="success" block @click="simulatePageTransition('zoom')">
              Zoom Transition
            </v-btn>
          </v-col>
          <v-col cols="12" sm="6" md="3">
            <v-btn color="info" block @click="simulatePageTransition('flip')">
              Flip Transition
            </v-btn>
          </v-col>
        </v-row>

        <v-card class="mt-4 pa-4" color="grey-darken-3">
          <div ref="transitionDemoArea" class="transition-demo-area">
            <h3 class="mb-2">Page Content Simulation</h3>
            <p>This content will be animated when you click the buttons above.</p>
            <v-img
              src="/images/logo.png"
              alt="Logo"
              width="120"
              class="mx-auto my-4"
            ></v-img>
          </div>
        </v-card>
      </v-card-text>
    </v-card>

    <!-- Loading Indicator Section -->
    <v-card class="mb-8">
      <v-card-title>
        <v-icon icon="mdi-loading" class="mr-2"></v-icon>
        Global Loading Indicator
      </v-card-title>
      <v-card-text>
        <p class="mb-4">The GlobalLoadingIndicator component uses GSAP for smooth animations:</p>
        <v-row>
          <v-col cols="12" sm="6">
            <v-btn color="primary" block @click="simulateLoading(2)">
              Show Loading for 2 Seconds
            </v-btn>
          </v-col>
          <v-col cols="12" sm="6">
            <v-btn color="secondary" block @click="simulateLoading(5)">
              Show Loading for 5 Seconds
            </v-btn>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- Custom Directives Section -->
    <v-card class="mb-8">
      <v-card-title>
        <v-icon icon="mdi-gesture-tap" class="mr-2"></v-icon>
        GSAP Custom Directives
      </v-card-title>
      <v-card-text>
        <v-row>
          <!-- Fade In -->
          <v-col cols="12" md="4">
            <v-card height="200" class="d-flex align-center justify-center">
              <v-btn color="primary" @click="resetAnimation('fadeInDemo')">
                v-gsap-fade-in
                <div ref="fadeInDemo" v-show="showFadeIn" v-gsap-fade-in="{ duration: 1, y: 20 }">
                  <v-icon icon="mdi-star" color="yellow" size="large" class="ml-2"></v-icon>
                </div>
              </v-btn>
            </v-card>
          </v-col>

          <!-- Pulse -->
          <v-col cols="12" md="4">
            <v-card height="200" class="d-flex align-center justify-center">
              <div>
                <v-btn color="error" class="mb-2" @click="togglePulse">
                  {{ pulsing ? 'Stop Pulse' : 'Start Pulse' }}
                </v-btn>
                <div class="d-flex justify-center">
                  <v-icon
                    ref="pulseDemo"
                    icon="mdi-bell"
                    color="red"
                    size="large"
                    v-gsap-pulse="{ duration: 1.5, scale: 1.2, active: pulsing }"
                  ></v-icon>
                </div>
              </div>
            </v-card>
          </v-col>

          <!-- Hover -->
          <v-col cols="12" md="4">
            <v-card height="200" class="d-flex align-center justify-center">
              <v-avatar
                v-gsap-hover="{ scale: 1.2, rotation: 10, duration: 0.4 }"
                color="primary"
                size="100"
              >
                <v-icon icon="mdi-account" size="large"></v-icon>
              </v-avatar>
            </v-card>
          </v-col>
        </v-row>

        <v-row class="mt-4">
          <!-- Click -->
          <v-col cols="12" md="4">
            <v-card height="200" class="d-flex align-center justify-center">
              <v-btn
                color="success"
                size="x-large"
                v-gsap-click="{ scale: 0.9, duration: 0.2 }"
              >
                v-gsap-click
              </v-btn>
            </v-card>
          </v-col>

          <!-- Stagger -->
          <v-col cols="12" md="4">
            <v-card height="200" class="d-flex flex-column align-center justify-center pa-4">
              <v-btn color="info" class="mb-4" @click="resetStaggerDemo">
                Reset Stagger
              </v-btn>
              <div ref="staggerContainer" v-show="showStagger" v-gsap-stagger="{ stagger: 0.1, y: 20 }">
                <v-chip class="ma-1" color="primary">Item 1</v-chip>
                <v-chip class="ma-1" color="secondary">Item 2</v-chip>
                <v-chip class="ma-1" color="success">Item 3</v-chip>
                <v-chip class="ma-1" color="error">Item 4</v-chip>
                <v-chip class="ma-1" color="warning">Item 5</v-chip>
              </div>
            </v-card>
          </v-col>

          <!-- Shake -->
          <v-col cols="12" md="4">
            <v-card height="200" class="d-flex align-center justify-center">
              <v-btn color="error" @click="triggerShakeAnimation">
                v-gsap-shake
                <v-icon
                  icon="mdi-alert"
                  class="ml-2"
                  v-gsap-shake="{ trigger: shakeTriggered }"
                ></v-icon>
              </v-btn>
            </v-card>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- Card & Button Animations -->
    <v-card class="mb-8">
      <v-card-title>
        <v-icon icon="mdi-cards" class="mr-2"></v-icon>
        Card & Button Animations
      </v-card-title>
      <v-card-text>
        <p class="mb-4">Cards and buttons have consistent hover and click effects:</p>
        <v-row>
          <v-col cols="12" sm="6" md="4" v-for="i in 3" :key="i">
            <v-card elevation="2" class="pa-4">
              <v-card-title>Example Card {{ i }}</v-card-title>
              <v-card-text>
                <p>This card has hover animations applied globally.</p>
                <p>Hover over it to see the effect.</p>
              </v-card-text>
              <v-card-actions>
                <v-btn color="primary" v-gsap-click>Click Me</v-btn>
                <v-btn color="secondary" v-gsap-hover>Hover Me</v-btn>
              </v-card-actions>
            </v-card>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- Animation Code Examples -->
    <v-card>
      <v-card-title>
        <v-icon icon="mdi-code-braces" class="mr-2"></v-icon>
        Implementation Examples
      </v-card-title>
      <v-card-text>
        <v-tabs v-model="codeTab">
          <v-tab value="gsap-plugin">GSAP Plugin</v-tab>
          <v-tab value="page-transitions">Page Transitions</v-tab>
          <v-tab value="loading-indicator">Loading Indicator</v-tab>
          <v-tab value="scss">Animation SCSS</v-tab>
        </v-tabs>

        <v-window v-model="codeTab" class="mt-2">
          <v-window-item value="gsap-plugin">
            <v-card flat>
              <v-card-text>
                <pre class="code-block"><code>// resources/js/plugins/gsap.js
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

export default {
  install: (app) => {
    app.config.globalProperties.$gsap = gsap;

    // Add custom directives
    app.directive('gsap-fade-in', {
      mounted(el, binding) {
        const duration = binding.value?.duration || 0.5;
        const delay = binding.value?.delay || 0;

        gsap.from(el, {
          opacity: 0,
          duration,
          delay,
          ease: 'power1.inOut'
        });
      }
    });

    // More directives...
  }
}</code></pre>
              </v-card-text>
            </v-card>
          </v-window-item>

          <v-window-item value="page-transitions">
            <v-card flat>
              <v-card-text>
                <pre class="code-block"><code>// DefaultLayout.vue
&lt;template&gt;
  &lt;v-main&gt;
    &lt;router-view v-slot="{ Component }"&gt;
      &lt;transition name="page-transition" mode="out-in"
        @enter="onEnter" @leave="onLeave"&gt;
        &lt;component :is="Component" /&gt;
      &lt;/transition&gt;
    &lt;/router-view&gt;
  &lt;/v-main&gt;
&lt;/template&gt;

&lt;script&gt;
export default {
  methods: {
    onEnter(el, done) {
      const gsap = this.$gsap;
      gsap.fromTo(
        el,
        { opacity: 0, y: 20 },
        { opacity: 1, y: 0, duration: 0.5, onComplete: done }
      );
    },

    onLeave(el, done) {
      const gsap = this.$gsap;
      gsap.to(
        el,
        { opacity: 0, y: -20, duration: 0.3, onComplete: done }
      );
    }
  }
}
&lt;/script&gt;</code></pre>
              </v-card-text>
            </v-card>
          </v-window-item>

          <v-window-item value="loading-indicator">
            <v-card flat>
              <v-card-text>
                <pre class="code-block"><code>// GlobalLoadingIndicator.vue
&lt;template&gt;
  &lt;transition @enter="onEnter" @leave="onLeave"&gt;
    &lt;div v-if="isLoading" class="loading-overlay"&gt;
      &lt;v-progress-circular indeterminate color="primary"&gt;&lt;/v-progress-circular&gt;
      &lt;div class="loading-text mt-4" ref="loadingText"&gt;Loading...&lt;/div&gt;
    &lt;/div&gt;
  &lt;/transition&gt;
&lt;/template&gt;

&lt;script&gt;
import { mapState } from 'pinia';
import { useLayoutStore } from '@/stores/layoutStore';

export default {
  computed: {
    ...mapState(useLayoutStore, ['isLoading'])
  },
  methods: {
    onEnter(el, done) {
      const gsap = this.$gsap;
      const tl = gsap.timeline({ onComplete: done });

      tl.fromTo(el,
        { opacity: 0 },
        { opacity: 1, duration: 0.3 }
      );

      if (this.$refs.loadingText) {
        tl.fromTo(this.$refs.loadingText,
          { opacity: 0, y: 10 },
          { opacity: 1, y: 0, duration: 0.4 },
          "-=0.1"
        );
      }
    }
    // onLeave method...
  }
}
&lt;/script&gt;</code></pre>
              </v-card-text>
            </v-card>
          </v-window-item>

          <v-window-item value="scss">
            <v-card flat>
              <v-card-text>
                <pre class="code-block"><code>// _animations.scss
// Remove default Vue transition classes to use GSAP only
.page-transition-enter-active,
.page-transition-leave-active {
  transition-property: none;
}

.page-transition-enter-from,
.page-transition-leave-to {
  opacity: 0;
}

// Hover effect for cards
.v-card {
  transition: transform 0.3s ease, box-shadow 0.3s ease;

  &:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 28px rgba(0, 0, 0, 0.25),
                0 10px 10px rgba(0, 0, 0, 0.22) !important;
  }
}

// Animation for data loading state
.data-loading {
  position: relative;
  overflow: hidden;

  &::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(
      90deg,
      rgba(0, 0, 0, 0.1) 0%,
      rgba(0, 0, 0, 0.2) 50%,
      rgba(0, 0, 0, 0.1) 100%
    );
    animation: shimmer 1.5s infinite;
    transform: translateX(-100%);
  }
}</code></pre>
              </v-card-text>
            </v-card>
          </v-window-item>
        </v-window>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script>
import { mapState, mapActions } from 'pinia';
import { useLayoutStore } from '@/stores/layoutStore';
import gsap from 'gsap';

export default {
  name: 'ComponentShowcaseView',

  data() {
    return {
      showFadeIn: true,
      pulsing: true,
      showStagger: true,
      shakeTriggered: false,
      codeTab: 'gsap-plugin'
    };
  },

  computed: {
    ...mapState(useLayoutStore, ['isLoading'])
  },

  methods: {
    ...mapActions(useLayoutStore, ['startLoading', 'stopLoading']),

    simulatePageTransition(type) {
      const el = this.$refs.transitionDemoArea;

      switch (type) {
        case 'fade':
          gsap.fromTo(el,
            { opacity: 0 },
            { opacity: 1, duration: 0.7, ease: 'power2.inOut' }
          );
          break;

        case 'slide':
          gsap.fromTo(el,
            { opacity: 0, x: -50 },
            { opacity: 1, x: 0, duration: 0.7, ease: 'back.out' }
          );
          break;

        case 'zoom':
          gsap.fromTo(el,
            { opacity: 0, scale: 0.8 },
            { opacity: 1, scale: 1, duration: 0.7, ease: 'back.out(1.2)' }
          );
          break;

        case 'flip':
          gsap.fromTo(el,
            { opacity: 0, rotationY: 90 },
            { opacity: 1, rotationY: 0, duration: 0.8, ease: 'power3.out' }
          );
          break;
      }
    },

    simulateLoading(seconds) {
      this.startLoading();

      setTimeout(() => {
        this.stopLoading();
      }, seconds * 1000);
    },

    resetAnimation(ref) {
      if (ref === 'fadeInDemo') {
        this.showFadeIn = false;

        setTimeout(() => {
          this.showFadeIn = true;
        }, 100);
      }
    },

    togglePulse() {
      this.pulsing = !this.pulsing;
    },

    resetStaggerDemo() {
      this.showStagger = false;

      setTimeout(() => {
        this.showStagger = true;
      }, 100);
    },

    triggerShakeAnimation() {
      this.shakeTriggered = false;

      setTimeout(() => {
        this.shakeTriggered = true;
      }, 100);
    }
  }
};
</script>

<style lang="scss" scoped>
.transition-demo-area {
  min-height: 200px;
  padding: 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
}

.code-block {
  background-color: #1e1e1e;
  color: #e0e0e0;
  padding: 16px;
  border-radius: 4px;
  overflow-x: auto;
  font-family: 'Courier New', monospace;

  code {
    white-space: pre-wrap;
  }
}
</style>
