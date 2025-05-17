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
  {
    path: '/forgot-password',
    name: 'forgot-password',
    component: () => import('@pages/ForgotPassword.vue'),
    meta: { requiresAuth: false }
  },
  {
    path: '/reset-password',
    name: 'reset-password',
    component: () => import('@pages/ResetPassword.vue'),
    meta: { requiresAuth: false }
  },
  // Protected routes
  {
    path: '/profile',
    name: 'profile',
    component: () => import('@/views/ProfileView.vue'),
    meta: { requiresAuth: true }
  },
  // Content routes
  {
    path: '/content/:slug',
    name: 'content-by-slug',
    component: () => import('@/views/ContentView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/content/id/:id',
    name: 'content-by-id',
    component: () => import('@/views/ContentView.vue'),
    meta: { requiresAuth: true }
  },
  // Embed routes
  {
    path: '/embed/:uuid',
    name: 'embed',
    component: () => import('@/views/EmbedView.vue'),
    meta: { requiresAuth: true, layout: 'blank' }
  },
  {
    path: '/notifications',
    name: 'notifications',
    component: () => import('@/views/NotificationListView.vue'),
    meta: { requiresAuth: true, title: 'Notifications' }
  },
  // Component Showcase (Development Only)
  {
    path: '/dev/component-showcase',
    name: 'component-showcase',
    component: () => import('@/views/dev/ComponentShowcaseView.vue'),
    meta: {
      requiresAuth: true,
      requiresPermission: 'admin',
      title: 'Component Showcase',
      devOnly: true
    }
  },

  // Admin routes
  {
    path: '/admin/users',
    name: 'admin-users',
    component: () => import('@/views/admin/UserManagementView.vue'),
    meta: { requiresAuth: true, requiresPermission: 'user-view', title: 'User Management' }
  },
  {
    path: '/admin/roles',
    name: 'admin-roles',
    component: () => import('@/views/admin/RoleManagementView.vue'),
    meta: { requiresAuth: true, requiresPermission: 'role-view', title: 'Role Management' }
  },
  {
    path: '/admin/system-configurations',
    name: 'admin-system-configurations',
    component: () => import('@/views/admin/SystemConfigurationView.vue'),
    meta: { requiresAuth: true, requiresPermission: 'system-config-view', title: 'System Configuration' }
  },  {
    path: '/admin/email-templates',
    name: 'admin-email-templates',
    component: () => import('@/views/admin/EmailTemplateView.vue'),
    meta: { requiresAuth: true, requiresPermission: 'email-template-view', title: 'Email Templates' }
  },
  {
    path: '/admin/audit-logs',
    name: 'admin-audit-logs',
    component: () => import('@/views/admin/AuditLogView.vue'),
    meta: { requiresAuth: true, requiresPermission: 'audit-log-view', title: 'Audit Logs' }
  },

  // Development routes
  {
    path: '/dev/component-showcase',
    name: 'component-showcase',
    component: () => import('@pages/ComponentShowcase.vue'),
    meta: {
      requiresAuth: false,
      development: true
    }
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
  const requiredPermission = to.matched.find(record => record.meta.requiresPermission)?.meta.requiresPermission;
  const isDevelopmentRoute = to.matched.some(record => record.meta.development);

  // Check if development routes should be accessible
  const isDevOnly = to.matched.some(record => record.meta.devOnly);
  if ((isDevelopmentRoute || isDevOnly) && process.env.NODE_ENV !== 'development') {
    next({ name: 'home' });
    return;
  }

  if (requiresAuth && (!authStore || !authStore.isAuthenticated)) {
    next({ name: 'login', query: { redirect: to.fullPath } });
    return;
  }

  if (to.name === 'login' && authStore && authStore.isAuthenticated) {
    // Redirect to home if trying to access login while authenticated
    next({ name: 'home' });
    return;
  }

  // Check for permission requirements
  if (requiredPermission && authStore) {
    const user = authStore.user;
    const hasPermission = user && (
      // Check if user has the required permission directly or through roles
      user.permissions?.includes(requiredPermission) ||
      user.roles?.some(role => role.permissions?.includes(requiredPermission))
    );

    if (!hasPermission) {
      // Redirect to home or show unauthorized page if user doesn't have permission
      next({ name: 'home' });
      return;
    }
  }

  next();
});

export default router;
