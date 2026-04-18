import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes: RouteRecordRaw[] = [
  {
    path: '/login',
    name: 'login',
    component: () => import('@/pages/LoginPage.vue'),
    meta: { requiresAuth: false },
  },
  {
    path: '/',
    component: () => import('@/layouts/AppLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      { path: '', redirect: '/dashboard' },
      {
        path: 'dashboard',
        name: 'dashboard',
        component: () => import('@/pages/DashboardPage.vue'),
      },
      {
        path: 'requests/create',
        name: 'request-create',
        component: () => import('@/pages/RequestCreatePage.vue'),
      },
      {
        path: 'requests/:id/edit',
        name: 'request-edit',
        component: () => import('@/pages/RequestEditPage.vue'),
      },
      {
        path: 'requests/:id',
        name: 'request-detail',
        component: () => import('@/pages/RequestDetailPage.vue'),
      },
      {
        path: 'requests',
        name: 'requests',
        component: () => import('@/pages/RequestListPage.vue'),
      },
      // Admin pages
      {
        path: 'fiscal-years',
        name: 'fiscal-years',
        component: () => import('@/pages/FiscalYearListPage.vue'),
        meta: { requiresAdmin: true },
      },
      {
        path: 'organizations',
        name: 'organizations',
        component: () => import('@/pages/OrganizationListPage.vue'),
        meta: { requiresAdmin: true },
      },
      {
        path: 'categories',
        name: 'categories',
        component: () => import('@/pages/CategoryListPage.vue'),
        meta: { requiresAdmin: true },
      },
      {
        path: 'users',
        name: 'users',
        component: () => import('@/pages/UserListPage.vue'),
        meta: { requiresAdmin: true },
      },
    ],
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    redirect: '/',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to) => {
  const auth = useAuthStore()
  const requiresAuth = to.matched.some((r) => r.meta.requiresAuth)

  if (requiresAuth && !auth.isAuthenticated) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }
  if (to.name === 'login' && auth.isAuthenticated) {
    return { name: 'dashboard' }
  }

  if (to.meta.requiresAdmin && auth.user?.role !== 'admin') {
    return { name: 'dashboard' }
  }
})

export default router
