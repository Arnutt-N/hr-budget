<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useBudgetRequestStore } from '@/stores/budgetRequests'
import { useFiscalYearStore } from '@/stores/fiscalYears'
import { useOrganizationStore } from '@/stores/organizations'
import ItemEditor from '@/components/ItemEditor.vue'
import FileUploader from '@/components/FileUploader.vue'
import type { ItemRow } from '@/components/ItemEditor.vue'

const route = useRoute()
const router = useRouter()
const store = useBudgetRequestStore()
const fyStore = useFiscalYearStore()
const orgStore = useOrganizationStore()

const requestTitle = ref('')
const fiscalYear = ref(0)
const orgId = ref<number | null>(null)
const items = ref<ItemRow[]>([])
const errorMsg = ref('')
const loaded = ref(false)

onMounted(async () => {
  await Promise.all([fyStore.fetchList(), orgStore.fetchList()])

  const id = Number(route.params.id)
  const ok = await store.fetchById(id)
  if (!ok) return

  const req = store.currentRequest
  if (!req) return

  if (req.request_status !== 'draft' && req.request_status !== 'saved') {
    router.replace(`/requests/${id}`)
    return
  }

  requestTitle.value = req.request_title
  fiscalYear.value = req.fiscal_year
  orgId.value = req.org_id

  items.value = (req.items || []).map((item) => ({
    item_name: item.item_name,
    quantity: item.quantity,
    unit_price: item.unit_price,
    remark: item.remark,
    category_item_id: item.category_item_id,
  }))

  loaded.value = true
})

async function handleSave() {
  errorMsg.value = ''
  const validItems = items.value.filter((i) => i.item_name.trim() !== '')
  if (validItems.length === 0) {
    errorMsg.value = 'ต้องมีรายการอย่างน้อย 1 รายการ'
    return
  }

  const id = Number(route.params.id)
  const result = await store.update(id, {
    request_title: requestTitle.value.trim(),
    fiscal_year: fiscalYear.value,
    org_id: orgId.value,
    items: validItems,
  })

  if (result.ok) {
    router.push(`/requests/${id}`)
  } else {
    errorMsg.value = result.error ?? 'ไม่สามารถบันทึกได้'
  }
}
</script>

<template>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <h1 class="text-2xl font-bold text-gray-900">แก้ไขคำของบประมาณ</h1>
      <router-link :to="`/requests/${route.params.id}`" class="text-sm text-gray-500 hover:text-gray-700">
        &larr; กลับ
      </router-link>
    </div>

    <div v-if="!loaded" class="py-16 text-center text-gray-500">กำลังโหลด...</div>

    <template v-else>
      <div v-if="errorMsg" class="mb-4 rounded bg-red-50 p-3 text-sm text-red-700" role="alert">
        {{ errorMsg }}
      </div>

      <div class="space-y-6 rounded-lg bg-white p-6 shadow">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">ชื่อคำขอ *</label>
            <input
              v-model="requestTitle"
              type="text"
              class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
            />
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">ปีงบประมาณ</label>
            <select
              v-model.number="fiscalYear"
              class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
            >
              <option v-for="fy in fyStore.fiscalYears" :key="fy.id" :value="fy.year">
                {{ fy.year }}{{ fy.is_current ? ' (ปีปัจจุบัน)' : '' }}
              </option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">หน่วยงาน</label>
            <select
              v-model="orgId"
              class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
            >
              <option :value="null">-- เลือกหน่วยงาน --</option>
              <option v-for="org in orgStore.organizations" :key="org.id" :value="org.id">
                {{ org.name_th }}
              </option>
            </select>
          </div>
        </div>

        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700">รายการงบประมาณ</label>
          <ItemEditor v-model="items" />
        </div>

        <FileUploader :request-id="Number(route.params.id)" />

        <div class="flex gap-3 border-t pt-4">
          <button
            @click="handleSave"
            :disabled="store.loading"
            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
          >
            {{ store.loading ? 'กำลังบันทึก...' : 'บันทึก' }}
          </button>
          <router-link
            :to="`/requests/${route.params.id}`"
            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            ยกเลิก
          </router-link>
        </div>
      </div>
    </template>
  </div>
</template>
