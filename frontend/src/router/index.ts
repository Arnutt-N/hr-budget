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
        meta: { title: 'ภาพรวม (Dashboard)' },
      },
      {
        path: 'requests/create',
        name: 'request-create',
        component: () => import('@/pages/RequestCreatePage.vue'),
        meta: { title: 'สร้างคำของบประมาณ' },
      },
      {
        path: 'requests/:id/edit',
        name: 'request-edit',
        component: () => import('@/pages/RequestEditPage.vue'),
        meta: { title: 'แก้ไขคำของบประมาณ' },
      },
      {
        path: 'requests/:id',
        name: 'request-detail',
        component: () => import('@/pages/RequestDetailPage.vue'),
        meta: { title: 'รายละเอียดคำของบประมาณ' },
      },
      {
        path: 'requests',
        name: 'requests',
        component: () => import('@/pages/RequestListPage.vue'),
        meta: { title: 'คำของบประมาณ' },
      },
      // Disbursement tracking (authed staff — NOT admin-only)
      {
        path: 'disbursements/wizard',
        name: 'disbursement-wizard',
        component: () => import('@/pages/DisbursementWizardPage.vue'),
        meta: { title: 'บันทึกการเบิกจ่าย' },
      },
      {
        path: 'disbursements',
        name: 'disbursements',
        component: () => import('@/pages/DisbursementListPage.vue'),
        meta: { title: 'บันทึกการเบิกจ่าย' },
      },
      {
        path: 'notifications',
        name: 'notifications',
        component: () => import('@/pages/NotificationListPage.vue'),
        meta: { title: 'การแจ้งเตือน' },
      },
      // Admin pages
      {
        path: 'fiscal-years',
        name: 'fiscal-years',
        component: () => import('@/pages/FiscalYearListPage.vue'),
        meta: { requiresAdmin: true, title: 'จัดการปีงบประมาณ' },
      },
      {
        path: 'organizations',
        name: 'organizations',
        component: () => import('@/pages/OrganizationListPage.vue'),
        meta: { requiresAdmin: true, title: 'จัดการหน่วยงาน' },
      },
      {
        path: 'categories',
        name: 'categories',
        component: () => import('@/pages/CategoryListPage.vue'),
        meta: { requiresAdmin: true, title: 'จัดการประเภทรายจ่าย' },
      },
      {
        path: 'users',
        name: 'users',
        component: () => import('@/pages/UserListPage.vue'),
        meta: { requiresAdmin: true, title: 'จัดการผู้ใช้' },
      },
      {
        path: 'divisions',
        name: 'divisions',
        component: () => import('@/pages/DivisionListPage.vue'),
        meta: { requiresAdmin: true, title: 'จัดการกอง/สำนัก' },
      },
      {
        path: 'plans',
        name: 'plans',
        component: () => import('@/pages/PlanListPage.vue'),
        meta: { requiresAdmin: true, title: 'จัดการแผนงาน/ผลผลิต' },
      },
      {
        path: 'target-types',
        name: 'target-types',
        component: () => import('@/pages/TargetTypeListPage.vue'),
        meta: { requiresAdmin: true, title: 'จัดการประเภทเป้าหมาย' },
      },
      {
        path: 'targets',
        name: 'targets',
        component: () => import('@/pages/TargetListPage.vue'),
        meta: { requiresAdmin: true, title: 'จัดการเป้าหมายงบประมาณ' },
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

router.beforeEach(async (to) => {
  const auth = useAuthStore()

  // Cookie auth: JS cannot read the token, so on first navigation we ask
  // /auth/me whether a session exists. bootstrap() is a no-op afterwards.
  if (!auth.initialized) {
    await auth.bootstrap()
  }

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
