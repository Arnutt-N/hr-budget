<script setup lang="ts">
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import { z } from 'zod'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import Button from 'primevue/button'
import Message from 'primevue/message'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

const schema = toTypedSchema(
  z.object({
    email: z.string().min(1, 'กรุณากรอกอีเมล').email('รูปแบบอีเมลไม่ถูกต้อง'),
    password: z.string().min(1, 'กรุณากรอกรหัสผ่าน'),
  }),
)

const { defineField, handleSubmit, errors } = useForm({ validationSchema: schema })
const [email] = defineField('email')
const [password] = defineField('password')

const errorMsg = ref('')
const loading = ref(false)

const onSubmit = handleSubmit(async (values) => {
  errorMsg.value = ''
  loading.value = true
  try {
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
  <div class="min-h-screen flex items-center justify-center bg-gray-50 px-4">
    <form
      class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md space-y-5"
      @submit.prevent="onSubmit"
    >
      <div class="text-center">
        <h1 class="text-2xl font-bold text-gray-900">HR Budget</h1>
        <p class="text-sm text-gray-500 mt-1">ระบบจัดการงบประมาณ</p>
      </div>

      <div class="flex flex-col gap-1">
        <label for="email" class="text-sm font-medium text-gray-700">อีเมล</label>
        <InputText
          id="email"
          v-model.trim="email"
          type="email"
          name="email"
          autocomplete="email"
          :invalid="!!errors.email"
          fluid
        />
        <small v-if="errors.email" class="text-red-600" role="alert">{{ errors.email }}</small>
      </div>

      <div class="flex flex-col gap-1">
        <label for="password" class="text-sm font-medium text-gray-700">รหัสผ่าน</label>
        <Password
          v-model="password"
          input-id="password"
          :input-props="{ name: 'password', autocomplete: 'current-password' }"
          :feedback="false"
          :invalid="!!errors.password"
          toggle-mask
          fluid
        />
        <small v-if="errors.password" class="text-red-600" role="alert">{{ errors.password }}</small>
      </div>

      <Message v-if="errorMsg" severity="error" :closable="false">{{ errorMsg }}</Message>

      <Button
        type="submit"
        :loading="loading"
        :label="loading ? 'กำลังเข้าสู่ระบบ...' : 'เข้าสู่ระบบ'"
        class="w-full"
      />
    </form>
  </div>
</template>
