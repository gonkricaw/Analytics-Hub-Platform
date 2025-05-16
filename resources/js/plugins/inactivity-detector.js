// plugins/inactivity-detector.js
import { useAuthStore } from '../stores/authStore';

export default {
  install(app) {
    const authStore = useAuthStore();
    let inactivityTimer = null;
    const timeoutDuration = 15 * 60 * 1000; // 15 minutes in milliseconds

    // Function to reset the timer
    function resetTimer() {
      if (inactivityTimer) {
        clearTimeout(inactivityTimer);
      }

      // Only start timer if user is authenticated
      if (authStore.isAuthenticated) {
        inactivityTimer = setTimeout(() => {
          console.log('User inactive for 15 minutes, logging out');
          authStore.logout();
          alert('You have been logged out due to inactivity.');
        }, timeoutDuration);
      }
    }

    // Events to listen for user activity
    const activityEvents = [
      'mousedown', 'mousemove', 'keypress',
      'scroll', 'touchstart', 'click',
      'DOMMouseScroll', 'mousewheel', 'touchmove'
    ];

    // Add event listeners when plugin is installed
    const addListeners = () => {
      activityEvents.forEach(event => {
        document.addEventListener(event, resetTimer);
      });

      // Watch for authentication state changes
      if (authStore.isAuthenticated) {
        resetTimer();
      }
    };

    // Remove event listeners
    const removeListeners = () => {
      if (inactivityTimer) {
        clearTimeout(inactivityTimer);
      }

      activityEvents.forEach(event => {
        document.removeEventListener(event, resetTimer);
      });
    };

    // Add a function to the app to manually handle activity
    app.config.globalProperties.$userActive = resetTimer;

    // Add the activity detection to the app
    app.config.globalProperties.$startActivityDetection = addListeners;
    app.config.globalProperties.$stopActivityDetection = removeListeners;

    // Auto-start activity detection
    addListeners();

    // When the user logs in or out, update the timer
    authStore.$subscribe((mutation, state) => {
      if (state.isAuthenticated) {
        resetTimer();
      } else {
        clearTimeout(inactivityTimer);
        inactivityTimer = null;
      }
    });
  }
};
