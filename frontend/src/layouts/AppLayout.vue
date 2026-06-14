<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter, useRoute, RouterLink } from 'vue-router'
import {
  Landmark,
  LayoutDashboard,
  FileText,
  Calendar,
  Building2,
  Building,
  Network,
  List,
  Target,
  Goal,
  Users,
  LogOut,
  Menu,
} from '@lucide/vue'
import Toast from 'primevue/toast'
import ConfirmDialog from 'primevue/confirmdialog'
import { useAuthStore } from '@/stores/auth'
import NotificationBell from '@/components/NotificationBell.vue'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

// User hydration happens in the router guard (auth.bootstrap) — by the time
// this layout renders, auth.user is already resolved.

const sidebarOpen = ref(false) // mobile drawer

const pageTitle = computed(() => (route.meta.title as string | undefined) ?? 'HR Budget')

// Same boundary rule as config/app.php > fiscal_year (Oct 1 starts the new BE year)
const currentFiscalYear = computed(() => {
  const now = new Date()
  const beYear = now.getFullYear() + 543
  return now.getMonth() + 1 >= 10 ? beYear + 1 : beYear
})

const userInitial = computed(() => (auth.user?.name ?? 'U').charAt(0).toUpperCase())

async function onLogout(): Promise<void> {
  await auth.logout()
  await router.replace({ name: 'login' })
}
</script>

<template>
  <div class="min-h-screen flex bg-dark-bg text-dark-text">
    <Toast position="top-right" />
    <ConfirmDialog />

    <!-- Mobile Overlay -->
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 bg-black/50 z-20 lg:hidden"
      @click="sidebarOpen = false"
    ></div>

    <!-- Sidebar -->
    <aside
      class="fixed inset-y-0 left-0 z-30 flex w-64 flex-col overflow-hidden border-r border-dark-border bg-dark-card transition-transform duration-300 lg:translate-x-0"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <!-- Logo -->
      <div class="flex h-16 items-center border-b border-dark-border px-6">
        <Landmark class="h-8 w-8 text-primary-500" />
        <span class="ml-3 whitespace-nowrap text-lg font-bold">HR Budget</span>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
        <RouterLink to="/dashboard" class="nav-link" @click="sidebarOpen = false">
          <LayoutDashboard class="h-5 w-5" />
          <span class="ml-3">ภาพรวม (Dashboard)</span>
        </RouterLink>

        <RouterLink to="/requests" class="nav-link" @click="sidebarOpen = false">
          <FileText class="h-5 w-5" />
          <span class="ml-3">คำขอประมาณ</span>
        </RouterLink>

        <!-- Management Section (admin only) -->
        <div v-if="auth.user?.role === 'admin'" class="mt-4 border-t border-dark-border pt-4">
          <div class="mb-2 px-3 text-xs font-semibold uppercase tracking-wider text-dark-muted">
            จัดการ
          </div>

          <RouterLink to="/fiscal-years" class="nav-link" @click="sidebarOpen = false">
            <Calendar class="h-5 w-5" />
            <span class="ml-3">ปีงบประมาณ</span>
          </RouterLink>

          <RouterLink to="/organizations" class="nav-link" @click="sidebarOpen = false">
            <Building2 class="h-5 w-5" />
            <span class="ml-3">หน่วยงาน</span>
          </RouterLink>

          <RouterLink to="/divisions" class="nav-link" @click="sidebarOpen = false">
            <Building class="h-5 w-5" />
            <span class="ml-3">กอง/สำนัก</span>
          </RouterLink>

          <RouterLink to="/plans" class="nav-link" @click="sidebarOpen = false">
            <Network class="h-5 w-5" />
            <span class="ml-3">แผนงาน/ผลผลิต</span>
          </RouterLink>

          <RouterLink to="/categories" class="nav-link" @click="sidebarOpen = false">
            <List class="h-5 w-5" />
            <span class="ml-3">ประเภทรายจ่าย</span>
          </RouterLink>

          <RouterLink to="/target-types" class="nav-link" @click="sidebarOpen = false">
            <Target class="h-5 w-5" />
            <span class="ml-3">ประเภทเป้าหมาย</span>
          </RouterLink>

          <RouterLink to="/targets" class="nav-link" @click="sidebarOpen = false">
            <Goal class="h-5 w-5" />
            <span class="ml-3">เป้าหมายงบประมาณ</span>
          </RouterLink>

          <RouterLink to="/users" class="nav-link" @click="sidebarOpen = false">
            <Users class="h-5 w-5" />
            <span class="ml-3">จัดการผู้ใช้</span>
          </RouterLink>
        </div>
      </nav>

      <!-- User & Logout -->
      <div class="border-t border-dark-border p-4">
        <button
          type="button"
          class="nav-link w-full text-red-400 hover:bg-red-900/20 hover:text-red-300"
          @click="onLogout"
        >
          <LogOut class="h-5 w-5" />
          <span class="ml-3">ออกจากระบบ</span>
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="flex min-h-screen flex-1 flex-col transition-all duration-300 lg:ml-64">
      <!-- Topbar -->
      <header
        class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-dark-border bg-dark-card/80 px-6 backdrop-blur"
      >
        <div class="flex items-center">
          <button
            type="button"
            class="mr-4 rounded-lg p-2 text-dark-muted hover:bg-slate-800 hover:text-white lg:hidden"
            aria-label="เปิดเมนู"
            @click="sidebarOpen = !sidebarOpen"
          >
            <Menu class="h-6 w-6" />
          </button>
          <h2 class="hidden text-lg font-semibold text-white sm:block">{{ pageTitle }}</h2>
        </div>

        <div class="flex items-center gap-4">
          <!-- Fiscal Year Badge -->
          <span class="badge badge-blue">
            <Calendar class="mr-1 h-4 w-4" />
            ปี {{ currentFiscalYear }}
          </span>

          <NotificationBell />

          <!-- User Info -->
          <div class="flex items-center gap-3">
            <div class="hidden text-right sm:block">
              <div class="text-sm font-medium text-white">{{ auth.user?.name || 'User' }}</div>
              <div class="text-xs text-dark-muted">{{ auth.user?.role || 'Viewer' }}</div>
            </div>
            <div
              class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-600 font-medium text-white"
            >
              {{ userInitial }}
            </div>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="flex-1 overflow-y-auto bg-dark-bg p-6">
        <RouterView />
      </main>
    </div>
  </div>
</template>
