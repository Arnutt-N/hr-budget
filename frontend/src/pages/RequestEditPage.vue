<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useBudgetRequest, useUpdateBudgetRequest } from '@/queries/useBudgetRequests'
import { useFiscalYearList } from '@/queries/useFiscalYears'
import { useOrganizationList } from '@/queries/useOrganizations'
import ItemEditor from '@/components/ItemEditor.vue'
import FileUploader from '@/components/FileUploader.vue'
import type { ItemRow } from '@/components/ItemEditor.vue'

const route = useRoute()
const router = useRouter()
const requestId = computed(() => Number(route.params.id))
const requestQuery = useBudgetRequest(requestId)
const updateMut = useUpdateBudgetRequest()
const { data: fiscalYears } = useFiscalYearList()
const { data: organizations } = useOrganizationList()

const requestTitle = ref('')
const fiscalYear = ref(0)
const orgId = ref<number | null>(null)
const items = ref<ItemRow[]>([])
const errorMsg = ref('')
const loaded = ref(false)
const saving = computed(() => updateMut.isPending.value)

// One-shot populate when the request arrives (don't clobber edits on refetch).
watch(
  requestQuery.data,
  (req) => {
    if (!req || loaded.value) return

    if (req.request_status !== 'draft' && req.request_status !== 'saved') {
      router.replace(`/requests/${requestId.value}`)
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
  },
  { immediate: true },
)

async function handleSave() {
  errorMsg.value = ''
  const validItems = items.value.filter((i) => i.item_name.trim() !== '')
  if (validItems.length === 0) {
    errorMsg.value = 'ต้องมีรายการอย่างน้อย 1 รายการ'
    return
  }

  try {
    await updateMut.mutateAsync({
      id: requestId.value,
      data: {
        request_title: requestTitle.value.trim(),
        fiscal_year: fiscalYear.value,
        org_id: orgId.value,
        items: validItems,
      },
    })
    router.push(`/requests/${requestId.value}`)
  } catch (e) {
    errorMsg.value = e instanceof Error ? e.message : 'ไม่สามารถบันทึกได้'
  }
}
</script>

<template>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <h1 class="text-2xl font-bold text-white">แก้ไขคำของบประมาณ</h1>
      <router-link :to="`/requests/${route.params.id}`" class="text-sm text-dark-muted hover:text-dark-text">
        &larr; กลับ
      </router-link>
    </div>

    <div v-if="!loaded" class="py-16 text-center text-dark-muted">กำลังโหลด...</div>

    <template v-else>
      <div v-if="errorMsg" class="mb-4 rounded bg-red-500/10 p-3 text-sm text-red-400" role="alert">
        {{ errorMsg }}
      </div>

      <div class="space-y-6 rounded-lg bg-dark-card border border-dark-border p-6 shadow">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <label class="mb-1 block text-sm font-medium text-dark-muted">ชื่อคำขอ *</label>
            <input
              v-model="requestTitle"
              type="text"
              class="w-full rounded bg-dark-card border border-dark-border text-dark-text px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
            />
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-dark-muted">ปีงบประมาณ</label>
            <select
              v-model.number="fiscalYear"
              class="w-full rounded bg-dark-card border border-dark-border text-dark-text px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
            >
              <option v-for="fy in fiscalYears ?? []" :key="fy.id" :value="fy.year">
                {{ fy.year }}{{ fy.is_current ? ' (ปีปัจจุบัน)' : '' }}
              </option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-dark-muted">หน่วยงาน</label>
            <select
              v-model="orgId"
              class="w-full rounded bg-dark-card border border-dark-border text-dark-text px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
            >
              <option :value="null">-- เลือกหน่วยงาน --</option>
              <option v-for="org in organizations ?? []" :key="org.id" :value="org.id">
                {{ org.name_th }}
              </option>
            </select>
          </div>
        </div>

        <div>
          <label class="mb-2 block text-sm font-medium text-dark-muted">รายการงบประมาณ</label>
          <ItemEditor v-model="items" />
        </div>

        <FileUploader :request-id="requestId" />

        <div class="flex gap-3 border-t border-dark-border pt-4">
          <button
            @click="handleSave"
            :disabled="saving"
            class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-500 disabled:opacity-50"
          >
            {{ saving ? 'กำลังบันทึก...' : 'บันทึก' }}
          </button>
          <router-link
            :to="`/requests/${route.params.id}`"
            class="rounded-lg border border-dark-border bg-dark-card px-4 py-2 text-sm font-medium text-dark-muted hover:bg-slate-800/50"
          >
            ยกเลิก
          </router-link>
        </div>
      </div>
    </template>
  </div>
</template>
