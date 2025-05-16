// resources/js/plugins/gsap.js
import { gsap } from 'gsap';

// Add any GSAP plugins here
// Example: import { ScrollTrigger } from 'gsap/ScrollTrigger';

// Register plugins
// Example: gsap.registerPlugin(ScrollTrigger);

export default {
  install: (app) => {
    app.config.globalProperties.$gsap = gsap;

    // Add custom directives for common animations
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

    app.directive('gsap-pulse', {
      mounted(el, binding) {
        const duration = binding.value?.duration || 1.5;
        const scale = binding.value?.scale || 1.05;

        gsap.to(el, {
          scale: scale,
          duration: duration / 2,
          repeat: -1,
          yoyo: true,
          ease: 'power1.inOut'
        });
      }
    });

    // Hover effect directive
    app.directive('gsap-hover', {
      mounted(el, binding) {
        const scale = binding.value?.scale || 1.05;
        const duration = binding.value?.duration || 0.3;

        el.addEventListener('mouseenter', () => {
          gsap.to(el, { scale: scale, duration });
        });

        el.addEventListener('mouseleave', () => {
          gsap.to(el, { scale: 1, duration });
        });
      }
    });
  }
};
