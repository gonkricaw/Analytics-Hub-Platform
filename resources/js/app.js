import './bootstrap';
import { createApp } from 'vue';
import vuetify from './plugins/vuetify';
import router from './router';
import pinia from './plugins/pinia';
import PerfectScrollbar from 'vue3-perfect-scrollbar';
import apexchartsPlugin from './plugins/apexcharts';
import quillPlugin from './plugins/quill';
import gsapPlugin from './plugins/gsap';
import inactivityDetector from './plugins/inactivity-detector';
import 'vue3-perfect-scrollbar/dist/vue3-perfect-scrollbar.css';
import App from './App.vue';

import '../scss/main.scss';

const app = createApp(App);

// Register global plugins
app.use(vuetify);
app.use(router);
app.use(pinia);
app.use(PerfectScrollbar);
app.use(apexchartsPlugin);
app.use(quillPlugin);
app.use(gsapPlugin);
app.use(inactivityDetector);

// Global error handler
app.config.errorHandler = (err, vm, info) => {
  console.error('Application Error:', err);
  console.error('Component:', vm);
  console.error('Info:', info);
};

// Mount the app
app.mount('#app');
