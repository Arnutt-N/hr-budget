<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useToast } from 'primevue/usetoast'
const toast = useToast()
import { useConfirm } from 'primevue/useconfirm'
import { useRoute, useRouter } from 'vue-router'
import { useBudgetRequest, useUpdateBudgetRequest } from '@/queries/useBudgetRequests'
import { fiscalYearLabel, useFiscalYearList } from '@/queries/useFiscalYears'
import { useOrganizationList } from '@/queries/useOrganizations'
import PageHeader from '@/components/PageHeader.vue'
import QueryErrorState from '@/components/QueryErrorState.vue'
import FormField from '@/components/FormField.vue'
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
const confirm = useConfirm()
const snapshot = ref('')
const saving = computed(() => updateMut.isPending.value)

const isDirty = computed(
  () =>
    loaded.value &&
    snapshot.value !==
      JSON.stringify({
        t: requestTitle.value,
        y: fiscalYear.value,
        o: orgId.value,
        items: items.value,
      }),
)

// One-shot populate when the request arrives (don't clobber edits on refetch).
watch(
  requestQuery.data,
  (req) => {
    if (!req || loaded.value) return

    if (req.request_status !== 'draft' && req.request_status !== 'saved') {
      toast.add({ severity: 'warn', summary: 'คำขอนี้ไม่อยู่ในสถานะที่แก้ไขได้', life: 5000 })
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

    snapshot.value = JSON.stringify({
      t: requestTitle.value,
      y: fiscalYear.value,
      o: orgId.value,
      items: items.value,
    })
    loaded.value = true
  },
  { immediate: true },
)

function cancelEdit(): void {
  const back = (): void => {
    router.push(`/requests/${route.params.id}`)
  }
  if (!isDirty.value) {
    back()
    return
  }
  confirm.require({
    header: 'ยกเลิกการแก้ไข?',
    message: 'การเปลี่ยนแปลงที่ยังไม่บันทึกจะหายไป ยืนยันการยกเลิกหรือไม่',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'ยกเลิกการแก้ไข',
    rejectLabel: 'ทำต่อ',
    accept: back,
  })
}

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
    <PageHeader title="แก้ไขคำของบประมาณ">
      <router-link :to="`/requests/${route.params.id}`" class="text-sm text-dark-muted hover:text-dark-text">
        &larr; กลับ
      </router-link>
    </PageHeader>

    <div v-if="!loaded" class="py-16 text-center text-dark-muted">กำลังโหลด...</div>

    <template v-else>
      <QueryErrorState v-if="errorMsg" :error="errorMsg" :retry="() => window.location.reload()" />

      <div class="space-y-6 rounded-lg bg-dark-card border border-dark-border p-6 shadow">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <FormField id="req-title" label="ชื่อคำขอ *">
            <input
              id="req-title"
              v-model="requestTitle"
              type="text"
              class="w-full rounded bg-dark-card border border-dark-border text-dark-text px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
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

        <FileUploader :request-id="requestId" />

        <div class="flex gap-3 border-t border-dark-border pt-4">
          <button
            @click="handleSave"
            :disabled="saving"
            class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 disabled:opacity-50"
          >
            {{ saving ? 'กำลังบันทึก...' : 'บันทึก' }}
          </button>
          <button
            type="button"
            class="rounded-lg border border-dark-border bg-dark-card px-4 py-2 text-sm font-medium text-dark-muted hover:bg-slate-800/50"
            @click="cancelEdit"
          >
            ยกเลิก
          </button>
        </div>
      </div>
    </template>
  </div>
</template>
