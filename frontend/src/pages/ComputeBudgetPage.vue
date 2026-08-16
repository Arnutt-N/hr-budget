<script setup lang="ts">
import { ref, computed } from 'vue'
import { useToast } from 'primevue/usetoast'
import Button from 'primevue/button'
import Select from 'primevue/select'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'
import Message from 'primevue/message'
import { useComputePersonnelBudget } from '@/queries/usePersonnel'
import { useFiscalYearList } from '@/queries/useFiscalYears'
import { useExpenseStructure } from '@/queries/useDisbursements'
import type { ComputeBudgetResult } from '@/types/personnel'

const toast = useToast()
const { data: fiscalYears } = useFiscalYearList()
const { data: expenseStructure } = useExpenseStructure()
const computeMutation = useComputePersonnelBudget()

const selectedYearId = ref<number | null>(null)
const result = ref<ComputeBudgetResult | null>(null)

// index โดย expense_item_id สำหรับ map ชื่อรายการ
const itemNames = computed(() => {
  const map: Record<number, string> = {}
  for (const et of expenseStructure.value ?? []) {
    for (const g of et.groups ?? []) {
      for (const item of g.items ?? []) {
        map[item.id] = item.name_th
      }
    }
  }
  return map
})

const rows = computed(() => {
  const lines = result.value?.lines ?? {}
  return Object.entries(lines)
    .map(([key, amount]) => {
      const [ei, org] = key.split(':')
      return {
        key: `${ei}-${org}`, // unique — ei ซ้ำได้ข้ามหน่วยงาน
        expense_item_id: Number(ei),
        organization_id: Number(org),
        amount,
        name_th: itemNames.value[Number(ei)] ?? `รายการ #${ei}`,
      }
    })
    .sort((a, b) => a.expense_item_id - b.expense_item_id)
})

const total = computed(() => rows.value.reduce((sum, r) => sum + r.amount, 0))

async function runDryRun(): Promise<void> {
  if (!selectedYearId.value) return
  try {
    result.value = await computeMutation.mutateAsync({ fiscalYearId: selectedYearId.value, dryRun: true })
    toast.add({ severity: 'success', summary: 'คำนวณ (ทดลอง) สำเร็จ', life: 3000 })
  } catch (e: unknown) {
    const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
    toast.add({ severity: 'error', summary: 'คำนวณไม่สำเร็จ', detail: message, life: 5000 })
  }
}

async function runCommit(): Promise<void> {
  if (!selectedYearId.value) return
  try {
    result.value = await computeMutation.mutateAsync({ fiscalYearId: selectedYearId.value, dryRun: false })
    toast.add({
      severity: 'success',
      summary: `เขียนงบสำเร็จ ${result.value?.written ?? 0} รายการ`,
      life: 5000,
    })
  } catch (e: unknown) {
    const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
    toast.add({ severity: 'error', summary: 'เขียนงบไม่สำเร็จ', detail: message, life: 5000 })
  }
}
</script>

<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-white">คำนวณงบบุคลากร</h1>
      <p class="mt-1 text-sm text-dark-muted">
        อัตรากำลัง × อัตราเงินเพิ่ม → ยอดลง budget_line_items (source=computed) — แทนที่เฉพาะแถว computed เดิม ไม่แตะแถวพิมพ์มือ
      </p>
    </div>

    <div class="mb-6 flex flex-wrap items-end gap-3 rounded-lg border border-dark-border p-4">
      <div class="flex flex-col gap-1">
        <span class="text-sm font-medium text-dark-muted">ปีงบประมาณ</span>
        <Select
          v-model="selectedYearId"
          :options="fiscalYears ?? []"
          option-label="year"
          option-value="id"
          placeholder="เลือกปีงบ"
          class="w-52"
        />
      </div>
      <Button
        label="คำนวณ (ทดลอง)"
        severity="secondary"
        icon="pi pi-eye"
        :loading="computeMutation.isPending.value"
        :disabled="!selectedYearId"
        @click="runDryRun"
      />
      <Button
        label="คำนวณ + เขียนงบ"
        icon="pi pi-save"
        :loading="computeMutation.isPending.value"
        :disabled="!selectedYearId"
        @click="runCommit"
      />
    </div>

    <Message v-if="result?.message" severity="info" :closable="false" class="mb-4">
      {{ result.message }}
    </Message>

    <template v-if="rows.length">
      <div class="mb-3 flex items-center justify-between">
        <span class="text-sm text-dark-muted">รวมทั้งสิ้น</span>
        <span class="text-xl font-bold text-white">{{ total.toLocaleString('th-TH') }} บาท</span>
      </div>

      <DataTable :value="rows" data-key="key" class="overflow-hidden rounded-lg border border-dark-border shadow">
        <Column header="รายการงบ" field="name_th" />
        <Column header="รหัสรายการ" field="expense_item_id" />
        <Column header="หน่วยงาน" field="organization_id" />
        <Column header="ยอด" field="amount">
          <template #body="{ data }">
            <Tag :value="Number(data.amount).toLocaleString('th-TH')" severity="info" />
          </template>
        </Column>
      </DataTable>
    </template>
    <p v-else-if="result" class="py-4 text-center text-dark-muted">
      คำนวณแล้วแต่ไม่มีรายการ — ตรวจสอบนโยบาย/เกณฑ์อัตราว่าง/สถานะอนุมัติ
    </p>
    <p v-else class="py-4 text-center text-dark-muted">ยังไม่ได้คำนวณ — เลือกปีงบแล้วกด "คำนวณ (ทดลอง)" ก่อน</p>
  </div>
</template>
