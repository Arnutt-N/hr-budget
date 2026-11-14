<script setup lang="ts">
import { ref, computed, watch, nextTick, onBeforeUnmount } from 'vue'
import { useRouter, useRoute, RouterLink } from 'vue-router'
import {
  Landmark,
  LayoutDashboard,
  FileText,
  Wallet,
  Calendar,
  Building2,
  Building,
  Network,
  List,
  Target,
  Goal,
  Users,
  ShieldCheck,
  UserRound,
  Coins,
  Ruler,
  ArrowUpCircle,
  ScrollText,
  ClipboardList,
  HeartHandshake,
  Calculator,
  Plane,
  Bell,
  FolderArchive,
  BarChart3,
  LineChart,
  LogOut,
  Menu,
} from '@lucide/vue'
import Toast from 'primevue/toast'
import ConfirmDialog from 'primevue/confirmdialog'
import { useAuthStore } from '@/stores/auth'
import NotificationBell from '@/components/NotificationBell.vue'
import { useEscapeClose } from '@/composables/useEscapeClose'
import { trapTabKey } from '@/lib/focusTrap'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

const isCurrent = (path: string): boolean => route.path.replace(/\/$/, '') === path

// User hydration happens in the router guard (auth.bootstrap) — by the time
// this layout renders, auth.user is already resolved.

const sidebarOpen = ref(false) // mobile drawer
useEscapeClose(sidebarOpen, () => (sidebarOpen.value = false))

// Drawer keyboard support: move focus in on open, trap Tab while open,
// return focus to the Menu button on close (Escape handled by useEscapeClose).
function focusMain(): void {
  document.getElementById('main')?.focus({ preventScroll: false })
}
const menuButton = ref<HTMLButtonElement | null>(null)
const sidebarEl = ref<HTMLElement | null>(null)
function onDrawerKeydown(e: KeyboardEvent): void {
  if (sidebarEl.value) trapTabKey(sidebarEl.value, e)
}
watch(sidebarOpen, async (open) => {
  await nextTick()
  const aside = sidebarEl.value
  if (open && aside) {
    aside.addEventListener('keydown', onDrawerKeydown)
    aside.querySelector<HTMLElement>(
      'a[href], button:not([disabled]), input, select, [tabindex]:not([tabindex="-1"])',
    )?.focus()
  } else {
    aside?.removeEventListener('keydown', onDrawerKeydown)
    menuButton.value?.focus()
  }
})
onBeforeUnmount(() => sidebarEl.value?.removeEventListener('keydown', onDrawerKeydown))

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
  <div class="min-h-screen flex overflow-x-clip bg-dark-bg text-dark-text">
    <a href="#main" @click.prevent="focusMain" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded focus:bg-primary-600 focus:px-4 focus:py-2 focus:text-white">ข้ามไปเนื้อหา</a>
    <Toast position="top-right" />
    <ConfirmDialog />

    <!-- Mobile Overlay -->
    <div
      v-if="sidebarOpen"
      aria-hidden="true"
      class="fixed inset-0 bg-black/50 z-20 lg:hidden"
      @click="sidebarOpen = false"
    ></div>

    <!-- Sidebar -->
    <aside
      id="app-sidebar"
      ref="sidebarEl"
      class="fixed inset-y-0 left-0 z-30 flex w-64 flex-col overflow-hidden border-r border-dark-border bg-dark-card transition-transform duration-300 lg:translate-x-0"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <!-- Logo -->
      <div class="flex h-16 items-center border-b border-dark-border px-6">
        <Landmark aria-hidden="true" class="h-8 w-8 text-primary-500" />
        <span class="ml-3 whitespace-nowrap text-lg font-bold">HR Budget</span>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
        <RouterLink to="/dashboard" class="nav-link" :aria-current="isCurrent('/dashboard') ? 'page' : undefined" @click="sidebarOpen = false">
          <LayoutDashboard aria-hidden="true" class="h-5 w-5" />
          <span class="ml-3">ภาพรวม (Dashboard)</span>
        </RouterLink>

        <RouterLink to="/requests" class="nav-link" :aria-current="isCurrent('/requests') ? 'page' : undefined" @click="sidebarOpen = false">
          <FileText aria-hidden="true" class="h-5 w-5" />
          <span class="ml-3">คำขอประมาณ</span>
        </RouterLink>

        <RouterLink to="/disbursements" class="nav-link" :aria-current="isCurrent('/disbursements') ? 'page' : undefined" @click="sidebarOpen = false">
          <Wallet aria-hidden="true" class="h-5 w-5" />
          <span class="ml-3">บันทึกการเบิกจ่าย</span>
        </RouterLink>

        <RouterLink to="/budget-execution" class="nav-link" :aria-current="isCurrent('/budget-execution') ? 'page' : undefined" @click="sidebarOpen = false">
          <BarChart3 aria-hidden="true" class="h-5 w-5" />
          <span class="ml-3">ผลการเบิกจ่าย</span>
        </RouterLink>

        <RouterLink to="/analytics" class="nav-link" :aria-current="isCurrent('/analytics') ? 'page' : undefined" @click="sidebarOpen = false">
          <LineChart aria-hidden="true" class="h-5 w-5" />
          <span class="ml-3">รายงานวิเคราะห์</span>
        </RouterLink>

        <RouterLink to="/notifications" class="nav-link" :aria-current="isCurrent('/notifications') ? 'page' : undefined" @click="sidebarOpen = false">
          <Bell aria-hidden="true" class="h-5 w-5" />
          <span class="ml-3">การแจ้งเตือน</span>
        </RouterLink>

        <RouterLink to="/vault" class="nav-link" :aria-current="isCurrent('/vault') ? 'page' : undefined" @click="sidebarOpen = false">
          <FolderArchive aria-hidden="true" class="h-5 w-5" />
          <span class="ml-3">คลังเอกสาร</span>
        </RouterLink>

        <!-- Management Section (admin only) -->
        <div v-if="auth.user?.role === 'admin'" class="mt-4 border-t border-dark-border pt-4">
          <div class="mb-2 px-3 text-xs font-semibold uppercase tracking-wider text-dark-muted">
            จัดการ
          </div>

          <RouterLink to="/fiscal-years" class="nav-link" :aria-current="isCurrent('/fiscal-years') ? 'page' : undefined" @click="sidebarOpen = false">
            <Calendar aria-hidden="true" class="h-5 w-5" />
            <span class="ml-3">ปีงบประมาณ</span>
          </RouterLink>

          <RouterLink to="/organizations" class="nav-link" :aria-current="isCurrent('/organizations') ? 'page' : undefined" @click="sidebarOpen = false">
            <Building2 aria-hidden="true" class="h-5 w-5" />
            <span class="ml-3">หน่วยงาน</span>
          </RouterLink>

          <RouterLink to="/divisions" class="nav-link" :aria-current="isCurrent('/divisions') ? 'page' : undefined" @click="sidebarOpen = false">
            <Building aria-hidden="true" class="h-5 w-5" />
            <span class="ml-3">กอง/สำนัก</span>
          </RouterLink>

          <RouterLink to="/plans" class="nav-link" :aria-current="isCurrent('/plans') ? 'page' : undefined" @click="sidebarOpen = false">
            <Network aria-hidden="true" class="h-5 w-5" />
            <span class="ml-3">แผนงาน/ผลผลิต</span>
          </RouterLink>

          <RouterLink to="/categories" class="nav-link" :aria-current="isCurrent('/categories') ? 'page' : undefined" @click="sidebarOpen = false">
            <List aria-hidden="true" class="h-5 w-5" />
            <span class="ml-3">ประเภทรายจ่าย</span>
          </RouterLink>

          <!-- Phase 9 — อัตรากำลังและงบบุคลากร -->
          <RouterLink to="/positions" class="nav-link" :aria-current="isCurrent('/positions') ? 'page' : undefined" @click="sidebarOpen = false">
            <UserRound aria-hidden="true" class="h-5 w-5" />
            <span class="ml-3">อัตรากำลัง</span>
          </RouterLink>

          <RouterLink to="/allowance-types" class="nav-link" :aria-current="isCurrent('/allowance-types') ? 'page' : undefined" @click="sidebarOpen = false">
            <Coins aria-hidden="true" class="h-5 w-5" />
            <span class="ml-3">เงินเพิ่ม</span>
          </RouterLink>

          <RouterLink to="/salary-scales" class="nav-link" :aria-current="isCurrent('/salary-scales') ? 'page' : undefined" @click="sidebarOpen = false">
            <Ruler aria-hidden="true" class="h-5 w-5" />
            <span class="ml-3">อัตราเงินเดือน</span>
          </RouterLink>

          <RouterLink to="/salary-raise-rounds" class="nav-link" :aria-current="isCurrent('/salary-raise-rounds') ? 'page' : undefined" @click="sidebarOpen = false">
            <ArrowUpCircle aria-hidden="true" class="h-5 w-5" />
            <span class="ml-3">รอบเลื่อนเงินเดือน</span>
          </RouterLink>

          <RouterLink to="/personnel-budget-policies" class="nav-link" :aria-current="isCurrent('/personnel-budget-policies') ? 'page' : undefined" @click="sidebarOpen = false">
            <ScrollText aria-hidden="true" class="h-5 w-5" />
            <span class="ml-3">นโยบายงบบุคลากร</span>
          </RouterLink>

          <RouterLink to="/vacancy-recruitment" class="nav-link" :aria-current="isCurrent('/vacancy-recruitment') ? 'page' : undefined" @click="sidebarOpen = false">
            <ClipboardList aria-hidden="true" class="h-5 w-5" />
            <span class="ml-3">อัตราว่างพร้อมบรรจุ</span>
          </RouterLink>

          <RouterLink to="/personnel-allowances" class="nav-link" :aria-current="isCurrent('/personnel-allowances') ? 'page' : undefined" @click="sidebarOpen = false">
            <HeartHandshake aria-hidden="true" class="h-5 w-5" />
            <span class="ml-3">การรับจริงเงินเพิ่ม</span>
          </RouterLink>

          <RouterLink to="/personnel-assignments" class="nav-link" :aria-current="isCurrent('/personnel-assignments') ? 'page' : undefined" @click="sidebarOpen = false">
            <Plane aria-hidden="true" class="h-5 w-5" />
            <span class="ml-3">ไปช่วยราชการ</span>
          </RouterLink>

          <RouterLink to="/compute-budget" class="nav-link" :aria-current="isCurrent('/compute-budget') ? 'page' : undefined" @click="sidebarOpen = false">
            <Calculator aria-hidden="true" class="h-5 w-5" />
            <span class="ml-3">คำนวณงบบุคลากร</span>
          </RouterLink>

          <RouterLink to="/target-types" class="nav-link" :aria-current="isCurrent('/target-types') ? 'page' : undefined" @click="sidebarOpen = false">
            <Target aria-hidden="true" class="h-5 w-5" />
            <span class="ml-3">ประเภทเป้าหมาย</span>
          </RouterLink>

          <RouterLink to="/targets" class="nav-link" :aria-current="isCurrent('/targets') ? 'page' : undefined" @click="sidebarOpen = false">
            <Goal aria-hidden="true" class="h-5 w-5" />
            <span class="ml-3">เป้าหมายงบประมาณ</span>
          </RouterLink>

          <RouterLink to="/users" class="nav-link" :aria-current="isCurrent('/users') ? 'page' : undefined" @click="sidebarOpen = false">
            <Users aria-hidden="true" class="h-5 w-5" />
            <span class="ml-3">จัดการผู้ใช้</span>
          </RouterLink>

          <RouterLink to="/roles" class="nav-link" :aria-current="isCurrent('/roles') ? 'page' : undefined" @click="sidebarOpen = false">
            <ShieldCheck aria-hidden="true" class="h-5 w-5" />
            <span class="ml-3">บทบาท/สิทธิ์</span>
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
          <LogOut aria-hidden="true" class="h-5 w-5" />
          <span class="ml-3">ออกจากระบบ</span>
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="flex min-h-screen min-w-0 flex-1 flex-col transition-all duration-300 lg:ml-64">
      <!-- Topbar -->
      <header
        class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-dark-border bg-dark-card/80 px-6 backdrop-blur"
      >
        <div class="flex items-center">
          <button
            ref="menuButton"
            type="button"
            class="mr-4 rounded-lg p-2 text-dark-muted hover:bg-slate-800 hover:text-white lg:hidden"
            aria-label="เปิดเมนู"
            :aria-expanded="sidebarOpen"
            aria-controls="app-sidebar"
            @click="sidebarOpen = !sidebarOpen"
          >
            <Menu aria-hidden="true" class="h-6 w-6" />
          </button>
          <!-- Chrome, not a semantic heading — the page content owns the h1/h2 -->
          <div class="hidden text-lg font-semibold text-white sm:block">{{ pageTitle }}</div>
        </div>

        <div class="flex items-center gap-4">
          <!-- Fiscal Year Badge -->
          <span class="badge badge-blue">
            <Calendar aria-hidden="true" class="mr-1 h-4 w-4" />
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
      <main id="main" tabindex="-1" class="min-w-0 flex-1 overflow-y-auto bg-dark-bg p-6">
        <RouterView />
      </main>
    </div>
  </div>
</template>
