<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { Landmark, ShieldCheck, Mail, Lock, Eye, EyeOff, AlertCircle, LoaderCircle } from '@lucide/vue'
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import { z } from 'zod'
import InputText from 'primevue/inputtext'
import Checkbox from 'primevue/checkbox'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import FormField from '@/components/FormField.vue'
import { useAuthStore } from '@/stores/auth'
import { fetchThaidStatus, fetchThaidFlash, thaidLoginUrl } from '@/api/auth'

// "จดจำฉัน" remembers the email only — the session token stays in the
// httpOnly cookie and is never readable from JS.
const REMEMBER_EMAIL_KEY = 'hr_budget.login.email'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

// ThaID is dormant unless the backend reports it configured — only then do we
// render the button. The flow is a full-page navigation (OAuth), not a fetch.
const thaidEnabled = ref(false)
const remember = ref(false)
const showPassword = ref(false)
const showForgotDialog = ref(false)
const errorMsg = ref('')
const loading = ref(false)

const schema = toTypedSchema(
  z.object({
    email: z.string().min(1, 'กรุณากรอกอีเมล').email('รูปแบบอีเมลไม่ถูกต้อง'),
    password: z.string().min(1, 'กรุณากรอกรหัสผ่าน'),
  }),
)

const { defineField, handleSubmit, errors } = useForm({ validationSchema: schema })
const [email] = defineField('email')
const [password] = defineField('password')

onMounted(async () => {
  const [status, flashMsg] = await Promise.all([fetchThaidStatus(), fetchThaidFlash()])
  thaidEnabled.value = status.enabled
  // Surface a one-time ThaID error (set by the OAuth callback redirect) if the
  // form hasn't already shown its own error.
  if (flashMsg && !errorMsg.value) errorMsg.value = flashMsg
  const saved = localStorage.getItem(REMEMBER_EMAIL_KEY)
  if (saved) {
    email.value = saved
    remember.value = true
  }
})

function onThaidLogin(): void {
  window.location.href = thaidLoginUrl()
}

const onSubmit = handleSubmit(async (values) => {
  errorMsg.value = ''
  loading.value = true
  try {
    if (remember.value) localStorage.setItem(REMEMBER_EMAIL_KEY, values.email)
    else localStorage.removeItem(REMEMBER_EMAIL_KEY)

    const result = await auth.login({ email: values.email, password: values.password })
    if (!result.ok) {
      errorMsg.value = result.error ?? 'เข้าสู่ระบบไม่สำเร็จ'
      return
    }
    // Open-redirect guard: only same-app paths ("/..." but not "//host")
    const raw = route.query.redirect as string | undefined
    const redirect = raw && raw.startsWith('/') && !raw.startsWith('//') ? raw : '/dashboard'
    await router.replace(redirect)
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="flex min-h-screen items-center justify-center bg-dark-bg px-4">
    <div class="login-card relative z-10 w-full max-w-md">
      <div
        class="w-full rounded-3xl border border-dark-border bg-dark-card p-6 shadow-2xl sm:p-8"
      >
        <!-- Header -->
        <div class="mb-6 text-center">
          <div
            class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 shadow-lg shadow-primary-500/25"
          >
            <Landmark aria-hidden="true" class="h-8 w-8 text-white" />
          </div>
          <h1 class="mb-2 text-2xl font-bold text-white">ระบบจัดการงบประมาณทรัพยากรบุคคล</h1>
          <p class="text-sm text-dark-muted">เข้าสู่ระบบจัดการข้อมูล</p>
        </div>

        <!-- API-level error banner -->
        <div
          v-if="errorMsg"
          class="mb-4 flex items-center gap-2 rounded-lg border border-red-500/30 bg-red-500/10 p-3 text-sm text-red-300"
          role="alert"
        >
          <AlertCircle class="h-4 w-4 shrink-0" aria-hidden="true" />
          <span>{{ errorMsg }}</span>
        </div>

        <form class="space-y-5" @submit.prevent="onSubmit">
          <FormField id="login-email" label="อีเมล" :error="errors.email">
            <div class="relative">
              <Mail
                class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-dark-muted"
                aria-hidden="true"
              />
              <InputText
                id="login-email"
                v-model.trim="email"
                type="email"
                name="email"
                autocomplete="email"
                placeholder="กรุณากรอกอีเมลของคุณ"
                :invalid="!!errors.email"
                :aria-describedby="errors.email ? 'login-email-error' : undefined"
                class="!rounded-xl !bg-dark-bg/70 !py-3 !pl-11 focus:!border-primary-500 focus:!ring-2 focus:!ring-primary-500/30"
                fluid
              />
            </div>
          </FormField>

          <FormField id="login-password" label="รหัสผ่าน" :error="errors.password">
            <div class="relative">
              <Lock
                class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-dark-muted"
                aria-hidden="true"
              />
              <InputText
                id="login-password"
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                name="password"
                autocomplete="current-password"
                placeholder="กรุณากรอกรหัสผ่านของคุณ"
                :invalid="!!errors.password"
                :aria-describedby="errors.password ? 'login-password-error' : undefined"
                class="!rounded-xl !bg-dark-bg/70 !py-3 !pl-11 !pr-11 focus:!border-primary-500 focus:!ring-2 focus:!ring-primary-500/30"
                fluid
              />
              <button
                type="button"
                class="absolute right-3 top-1/2 flex -translate-y-1/2 items-center rounded-md p-1 text-dark-muted transition-colors hover:text-white focus:outline-none focus-visible:text-white focus-visible:ring-1 focus-visible:ring-primary-500"
                :aria-label="showPassword ? 'ซ่อนรหัสผ่าน' : 'แสดงรหัสผ่าน'"
                :aria-pressed="showPassword"
                @click="showPassword = !showPassword"
              >
                <EyeOff v-if="showPassword" class="h-5 w-5" aria-hidden="true" />
                <Eye v-else class="h-5 w-5" aria-hidden="true" />
              </button>
            </div>
          </FormField>

          <div class="flex items-center justify-between gap-3 px-1">
            <div class="flex items-center gap-2.5">
              <Checkbox v-model="remember" input-id="remember" binary />
              <label for="remember" class="text-sm text-dark-muted select-none">จดจำฉัน</label>
            </div>
            <button
              type="button"
              class="shrink-0 rounded text-sm text-primary-400 transition-colors hover:text-primary-300 hover:underline focus:outline-none focus-visible:text-primary-300 focus-visible:ring-1 focus-visible:ring-primary-500"
              @click="showForgotDialog = true"
            >
              ลืมรหัสผ่าน?
            </button>
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="mt-1 flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl py-3 font-medium text-white transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-primary-500/30 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-400 active:translate-y-0 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:translate-y-0 disabled:hover:shadow-none"
            style="background: linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%)"
          >
            <LoaderCircle v-if="loading" class="h-5 w-5 animate-spin" aria-hidden="true" />
            {{ loading ? 'กำลังเข้าสู่ระบบ...' : 'เข้าสู่ระบบ' }}
          </button>

          <template v-if="thaidEnabled">
            <div class="flex items-center gap-3 text-xs text-dark-muted">
              <span class="h-px flex-1 bg-dark-border"></span>
              <span>หรือ</span>
              <span class="h-px flex-1 bg-dark-border"></span>
            </div>
            <Button
              type="button"
              severity="secondary"
              outlined
              label="เข้าสู่ระบบด้วย ThaID"
              class="w-full !rounded-xl !py-3"
              @click="onThaidLogin"
            >
              <template #icon>
                <ShieldCheck aria-hidden="true" class="mr-2 h-4 w-4" />
              </template>
            </Button>
          </template>
        </form>
      </div>
    </div>

    <Dialog
      v-model:visible="showForgotDialog"
      modal
      header="ลืมรหัสผ่าน"
      class="w-full max-w-sm"
      :draggable="false"
    >
      <p class="text-sm text-dark-muted">
        ระบบยังไม่รองรับการตั้งรหัสผ่านใหม่ด้วยตนเอง
        กรุณาติดต่อผู้ดูแลระบบเพื่อขอตั้งรหัสผ่านใหม่
        พร้อมแจ้งอีเมลที่ใช้เข้าสู่ระบบของท่าน
      </p>
      <p v-if="email" class="mt-3 rounded-md bg-dark-bg border border-dark-border p-3 text-sm text-white">
        อีเมลของท่าน: <span class="font-medium">{{ email }}</span>
      </p>
    </Dialog>
  </div>
</template>

<style scoped>
/* Entrance animation — mirrors smart-port's login card reveal */
.login-card {
  animation: loginEnter 0.5s ease-out;
}
@keyframes loginEnter {
  from {
    opacity: 0;
    transform: translateY(20px) scale(0.98);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}
@media (prefers-reduced-motion: reduce) {
  .login-card {
    animation: none;
  }
}
</style>
