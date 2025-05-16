// resources/js/router/index.js
import { createRouter, createWebHistory } from 'vue-router';

// Import pages
import Home from '@pages/Home.vue';

const routes = [
  {
    path: '/',
    name: 'home',
    component: Home,
    meta: { requiresAuth: false }
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('@pages/Login.vue'),
    meta: { requiresAuth: false }
  },
  // Protected routes will be added in future phases
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition;
    } else {
      return { top: 0 };
    }
  }
});

// Navigation guards will be implemented in future phases

export default router;
