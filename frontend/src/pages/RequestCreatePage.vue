<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import { useCreateBudgetRequest, useSubmitBudgetRequest } from '@/queries/useBudgetRequests'
import { fiscalYearLabel, useFiscalYearList } from '@/queries/useFiscalYears'
import { useOrganizationList } from '@/queries/useOrganizations'
import PageHeader from '@/components/PageHeader.vue'
import QueryErrorState from '@/components/QueryErrorState.vue'
import FormField from '@/components/FormField.vue'
import ItemEditor from '@/components/ItemEditor.vue'
import type { ItemRow } from '@/components/ItemEditor.vue'

const router = useRouter()
const toast = useToast()
const createMut = useCreateBudgetRequest()
const submitMut = useSubmitBudgetRequest()
const { data: fiscalYears } = useFiscalYearList()
const { data: organizations } = useOrganizationList()

const requestTitle = ref('')
const fiscalYear = ref<number>(0)
const orgId = ref<number | null>(null)
const items = ref<ItemRow[]>([
  { item_name: '', quantity: '0', unit_price: '0', remark: null, category_item_id: null },
])
const errorMsg = ref('')

// Template scope can't reference the `window` global — expose reload as a binding.
function reloadPage(): void {
  window.location.reload()
}
const loading = computed(() => createMut.isPending.value || submitMut.isPending.value)

// Default the fiscal year once the list arrives (TanStack data is async/reactive)
watch(
  fiscalYears,
  (list) => {
    if (!list || fiscalYear.value !== 0) return
    const current = list.find((fy) => fy.is_current)
    if (current) fiscalYear.value = current.year
    else if (list.length > 0) fiscalYear.value = list[0].year
  },
  { immediate: true },
)

const canSave = computed(() =>
  requestTitle.value.trim() !== '' && items.value.some((i) => i.item_name.trim() !== ''),
)

async function saveDraft() {
  const id = await doCreate()
  if (id) router.push(`/requests/${id}`)
}

async function saveAndSubmit() {
  const id = await doCreate()
  if (!id) return // create failed → errorMsg shown, stay on the form
  try {
    await submitMut.mutateAsync(id)
  } catch (e) {
    // Created as a draft but submit failed. A Toast survives the route change
    // (rendered app-level in AppLayout) so the user knows to retry on detail.
    toast.add({
      severity: 'warn',
      summary: 'ส่งอนุมัติไม่สำเร็จ',
      detail: e instanceof Error ? e.message : 'บันทึกร่างแล้ว – ลองส่งอนุมัติอีกครั้งในหน้ารายละเอียด',
      life: 6000,
    })
  }
  router.push(`/requests/${id}`)
}

async function doCreate(): Promise<number | null> {
  errorMsg.value = ''

  const validItems = items.value.filter((i) => i.item_name.trim() !== '')
  if (validItems.length === 0) {
    errorMsg.value = 'ต้องมีรายการอย่างน้อย 1 รายการ'
    return null
  }

  try {
    const created = await createMut.mutateAsync({
      request_title: requestTitle.value.trim(),
      fiscal_year: fiscalYear.value,
      org_id: orgId.value,
      items: validItems,
    })
    return created.id
  } catch (e) {
    errorMsg.value = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
    return null
  }
}
</script>

<template>
  <div>
    <PageHeader title="สร้างคำของบประมาณ">
      <router-link to="/requests" class="text-sm text-dark-muted hover:text-dark-text">
        &larr; กลับ
      </router-link>
    </PageHeader>

    <QueryErrorState v-if="errorMsg" :error="errorMsg" :retry="reloadPage" />

    <form class="space-y-6 rounded-lg bg-dark-card border border-dark-border p-6 shadow" @submit.prevent="saveAndSubmit">
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <FormField id="req-title" label="ชื่อคำขอ *">
          <input
            id="req-title"
            v-model="requestTitle"
            type="text"
            class="w-full rounded bg-dark-card border border-dark-border text-dark-text px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
            placeholder="เช่น คำของบประมาณเดือนตุลาคม 2569"
          />
        </FormField>
        <FormField id="req-fiscal-year" label="ปีงบประมาณ">
          <select
            id="req-fiscal-year"
            v-model.number="fiscalYear"
            class="w-full rounded bg-dark-card border border-dark-border text-dark-text px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
          >
            <option v-for="fy in fiscalYears ?? []" :key="fy.id" :value="fy.year">
              {{ fiscalYearLabel(fy) }}
            </option>
          </select>
        </FormField>
        <FormField id="req-org" label="หน่วยงาน">
          <select
            id="req-org"
            v-model="orgId"
            class="w-full rounded bg-dark-card border border-dark-border text-dark-text px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
          >
            <option :value="null">-- เลือกหน่วยงาน --</option>
            <option v-for="org in organizations ?? []" :key="org.id" :value="org.id">
              {{ org.name_th }}
            </option>
          </select>
        </FormField>
      </div>

      <div>
        <label class="mb-2 block text-sm font-medium text-dark-muted">รายการงบประมาณ</label>
        <ItemEditor v-model="items" />
      </div>

      <div class="flex gap-3 border-t border-dark-border pt-4">
        <button
          type="button"
          @click="saveDraft"
          :disabled="loading || !canSave"
          class="rounded-lg border border-dark-border bg-dark-card px-4 py-2 text-sm font-medium text-dark-muted hover:bg-slate-800/50 disabled:opacity-50"
        >
          {{ loading ? 'กำลังบันทึก...' : 'บันทึกร่าง' }}
        </button>
        <button
          type="submit"
          @click="saveAndSubmit"
          :disabled="loading || !canSave"
          class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 disabled:opacity-50"
        >
          {{ loading ? 'กำลังส่ง...' : 'ส่งอนุมัติ' }}
        </button>
      </div>
    </form>
  </div>
</template>
