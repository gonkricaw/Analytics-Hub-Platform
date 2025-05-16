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
  // Protected routes
  {
    path: '/profile',
    name: 'profile',
    component: () => import('@/views/ProfileView.vue'),
    meta: { requiresAuth: true }
  },
  // More protected routes will be added in future phases
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

// Navigation guards
router.beforeEach((to, from, next) => {
  // Import auth store directly to avoid circular dependency
  const authStore = window.pinia?.state?.value?.auth;
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth);

  if (requiresAuth && (!authStore || !authStore.isAuthenticated)) {
    next({ name: 'login', query: { redirect: to.fullPath } });
  } else if (to.name === 'login' && authStore && authStore.isAuthenticated) {
    // Redirect to home if trying to access login while authenticated
    next({ name: 'home' });
  } else {
    next();
  }
});

export default router;
