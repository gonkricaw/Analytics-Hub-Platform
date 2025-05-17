// resources/js/plugins/gsap.js
import { gsap } from 'gsap';

// Add any GSAP plugins here
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { TextPlugin } from 'gsap/TextPlugin';

// Register plugins
gsap.registerPlugin(ScrollTrigger, TextPlugin);

export default {
  install: (app) => {
    app.config.globalProperties.$gsap = gsap;

    // Add custom directives for common animations
    app.directive('gsap-fade-in', {
      mounted(el, binding) {
        const duration = binding.value?.duration || 0.5;
        const delay = binding.value?.delay || 0;
        const y = binding.value?.y || 0;

        gsap.from(el, {
          opacity: 0,
          y,
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
        const active = binding.value?.active !== undefined ? binding.value.active : true;

        if (active) {
          gsap.to(el, {
            scale: scale,
            duration: duration / 2,
            repeat: -1,
            yoyo: true,
            ease: 'power1.inOut'
          });
        }
      },
      updated(el, binding) {
        // Allow dynamic updates to the pulse animation
        const active = binding.value?.active !== undefined ? binding.value.active : true;
        const scale = binding.value?.scale || 1.05;
        const duration = binding.value?.duration || 1.5;

        if (active) {
          gsap.killTweensOf(el);
          gsap.to(el, {
            scale: scale,
            duration: duration / 2,
            repeat: -1,
            yoyo: true,
            ease: 'power1.inOut'
          });
        } else {
          gsap.killTweensOf(el);
          gsap.to(el, { scale: 1, duration: 0.3 });
        }
      }
    });

    // Hover effect directive
    app.directive('gsap-hover', {
      mounted(el, binding) {
        const scale = binding.value?.scale || 1.05;
        const duration = binding.value?.duration || 0.3;
        const rotation = binding.value?.rotation || 0;
        const y = binding.value?.y || 0;
        const x = binding.value?.x || 0;
        const brightness = binding.value?.brightness || 1.1;

        el.addEventListener('mouseenter', () => {
          gsap.to(el, {
            scale,
            rotation,
            y,
            x,
            duration,
            ease: 'power2.out',
            filter: `brightness(${brightness})`
          });
        });

        el.addEventListener('mouseleave', () => {
          gsap.to(el, {
            scale: 1,
            rotation: 0,
            y: 0,
            x: 0,
            duration,
            ease: 'power2.in',
            filter: 'brightness(1)'
          });
        });
      }
    });

    // Click effect directive
    app.directive('gsap-click', {
      mounted(el, binding) {
        const scale = binding.value?.scale || 0.95;
        const duration = binding.value?.duration || 0.1;

        el.addEventListener('mousedown', () => {
          gsap.to(el, { scale, duration, ease: 'power2.out' });
        });

        el.addEventListener('mouseup', () => {
          gsap.to(el, { scale: 1, duration, ease: 'power2.in' });
        });

        el.addEventListener('mouseleave', () => {
          gsap.to(el, { scale: 1, duration, ease: 'power2.in' });
        });
      }
    });

    // Staggered list animation
    app.directive('gsap-stagger', {
      mounted(el, binding) {
        const delay = binding.value?.delay || 0;
        const stagger = binding.value?.stagger || 0.1;
        const duration = binding.value?.duration || 0.5;
        const y = binding.value?.y || 20;

        const children = el.children;
        if (children && children.length) {
          gsap.from(children, {
            opacity: 0,
            y,
            duration,
            delay,
            stagger: stagger,
            ease: 'power2.out'
          });
        }
      }
    });

    // Shake animation (for errors, etc.)
    app.directive('gsap-shake', {
      mounted(el, binding) {
        const trigger = binding.value?.trigger || false;

        if (trigger) {
          gsap.fromTo(el,
            { x: -10 },
            {
              x: 0,
              duration: 0.5,
              ease: 'elastic.out(1, 0.3)'
            }
          );
        }
      },
      updated(el, binding) {
        const newTrigger = binding.value?.trigger || false;
        const oldTrigger = binding.oldValue?.trigger || false;

        if (newTrigger !== oldTrigger && newTrigger) {
          gsap.fromTo(el,
            { x: -10 },
            {
              x: 0,
              duration: 0.5,
              ease: 'elastic.out(1, 0.3)'
            }
          );
        }
      }
    });
  }
};
