<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { Landmark, ShieldCheck, Mail, Lock, Eye, EyeOff } from '@lucide/vue'
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import { z } from 'zod'
import InputText from 'primevue/inputtext'
import Checkbox from 'primevue/checkbox'
import Button from 'primevue/button'
import Message from 'primevue/message'
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
  <div class="min-h-screen flex items-center justify-center bg-dark-bg px-4">
    <form
      class="bg-dark-card border border-dark-border p-8 rounded-xl shadow-lg w-full max-w-md space-y-5"
      @submit.prevent="onSubmit"
    >
      <div class="text-center">
        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-primary-500">
          <Landmark class="h-6 w-6 text-white" />
        </div>
        <h1 class="text-2xl font-bold text-white">ระบบบริหารงบประมาณบุคลากร</h1>
        <p class="text-sm text-dark-muted mt-1">เข้าสู่ระบบจัดการข้อมูล</p>
      </div>

      <FormField id="login-email" label="อีเมล" :error="errors.email">
        <div class="relative">
          <Mail
            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-dark-muted"
            aria-hidden="true"
          />
          <InputText
            id="login-email"
            v-model.trim="email"
            type="email"
            name="email"
            autocomplete="email"
            :invalid="!!errors.email"
            :aria-describedby="errors.email ? 'login-email-error' : undefined"
            class="!pl-10"
            fluid
          />
        </div>
      </FormField>

      <FormField id="login-password" label="รหัสผ่าน" :error="errors.password">
        <div class="relative">
          <Lock
            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-dark-muted"
            aria-hidden="true"
          />
          <InputText
            id="login-password"
            v-model="password"
            :type="showPassword ? 'text' : 'password'"
            name="password"
            autocomplete="current-password"
            :invalid="!!errors.password"
            :aria-describedby="errors.password ? 'login-password-error' : undefined"
            class="!pl-10 !pr-10"
            fluid
          />
          <button
            type="button"
            class="absolute right-2 top-1/2 flex -translate-y-1/2 items-center rounded-md p-1 text-dark-muted hover:text-white focus:outline-none focus-visible:text-white focus-visible:ring-1 focus-visible:ring-primary-500"
            :aria-label="showPassword ? 'ซ่อนรหัสผ่าน' : 'แสดงรหัสผ่าน'"
            :aria-pressed="showPassword"
            @click="showPassword = !showPassword"
          >
            <EyeOff v-if="showPassword" class="h-4 w-4" aria-hidden="true" />
            <Eye v-else class="h-4 w-4" aria-hidden="true" />
          </button>
        </div>
      </FormField>

      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <Checkbox v-model="remember" input-id="remember" binary />
          <label for="remember" class="text-sm text-dark-muted select-none">จดจำฉัน</label>
        </div>
        <button
          type="button"
          class="rounded text-sm text-primary-400 hover:text-primary-300 focus:outline-none focus-visible:text-primary-300 focus-visible:ring-1 focus-visible:ring-primary-500"
          @click="showForgotDialog = true"
        >
          ลืมรหัสผ่าน?
        </button>
      </div>

      <Message v-if="errorMsg" severity="error" :closable="false">{{ errorMsg }}</Message>

      <Button
        type="submit"
        :loading="loading"
        :label="loading ? 'กำลังเข้าสู่ระบบ...' : 'เข้าสู่ระบบ'"
        class="w-full"
      />

      <template v-if="thaidEnabled">
        <div class="flex items-center gap-3 text-dark-muted text-xs">
          <span class="h-px flex-1 bg-dark-border"></span>
          <span>หรือ</span>
          <span class="h-px flex-1 bg-dark-border"></span>
        </div>
        <Button
          type="button"
          severity="secondary"
          outlined
          label="เข้าสู่ระบบด้วย ThaID"
          class="w-full"
          @click="onThaidLogin"
        >
          <template #icon>
            <ShieldCheck class="h-4 w-4 mr-2" />
          </template>
        </Button>
      </template>
    </form>

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
