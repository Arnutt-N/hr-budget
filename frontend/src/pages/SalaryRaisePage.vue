<script setup lang="ts">
import { ref, computed } from 'vue'
import { useToast } from 'primevue/usetoast'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import ToggleSwitch from 'primevue/toggleswitch'
import Message from 'primevue/message'
import { formatThaiDate } from '@/lib/date'
import type { SalaryRaiseRound, SalaryRaiseProgress } from '@/types/salary'
import {
  useSalaryRaiseRounds,
  useSetIncludeInBudget,
  useRaiseProgress,
  useMarkRaiseProgress,
  useSeedRaiseProgress,
} from '@/queries/useSalary'

const toast = useToast()

const { data: rounds, isLoading, isError, error } = useSalaryRaiseRounds()
const includeMutation = useSetIncludeInBudget()
const markMutation = useMarkRaiseProgress()
const seedMutation = useSeedRaiseProgress()

function roundLabel(r: SalaryRaiseRound): string {
  return `${r.round_month === 'apr' ? 'เม.ย.' : 'ต.ค.'} ${r.round_year_be}`
}

async function onToggleInclude(r: SalaryRaiseRound, value: boolean): Promise<void> {
  try {
    await includeMutation.mutateAsync({ roundId: r.id, include: value })
    toast.add({
      severity: 'success',
      summary: value ? `นับรอบ ${roundLabel(r)} ในงบแล้ว` : `ตัดรอบ ${roundLabel(r)} ออกจากงบแล้ว`,
      life: 3000,
    })
  } catch (e: unknown) {
    const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
    toast.add({ severity: 'error', summary: 'อัปเดตไม่สำเร็จ', detail: message, life: 5000 })
  }
}

// ---------- progress dialog ----------
const showProgress = ref(false)
const activeRoundId = ref<number | null>(null)
const activeRound = computed(() => rounds.value?.find((r) => r.id === activeRoundId.value) ?? null)
const { data: progress, isLoading: progressLoading } = useRaiseProgress(activeRoundId)

const completedCount = computed(
  () => (progress.value ?? []).filter((p) => p.status === 'completed').length,
)

function openProgress(r: SalaryRaiseRound): void {
  activeRoundId.value = r.id
  showProgress.value = true
}

async function onToggleStatus(p: SalaryRaiseProgress): Promise<void> {
  if (!activeRoundId.value) return
  const next = p.status === 'completed' ? 'pending' : 'completed'
  try {
    await markMutation.mutateAsync({
      roundId: activeRoundId.value,
      organizationId: p.organization_id,
      status: next,
    })
  } catch (e: unknown) {
    const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
    toast.add({ severity: 'error', summary: 'บันทึกไม่สำเร็จ', detail: message, life: 5000 })
  }
}

async function onSeedAll(): Promise<void> {
  if (!activeRoundId.value) return
  try {
    const res = await seedMutation.mutateAsync(activeRoundId.value)
    toast.add({ severity: 'success', summary: `สร้างแถวติดตาม ${res?.created ?? 0} หน่วยงาน`, life: 3000 })
  } catch (e: unknown) {
    const message = e instanceof Error ? e.message : 'เกิดข้อผิดพลาด'
    toast.add({ severity: 'error', summary: 'สร้างแถวไม่สำเร็จ', detail: message, life: 5000 })
  }
}
</script>

<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-white">รอบเลื่อนเงินเดือน</h1>
      <p class="mt-1 text-sm text-dark-muted">
        สวิตช์ "นับในงบ" ตัดสินว่ารอบไหนเข้าคำนวณ · สถานะรายหน่วยตัดสินว่าเงินเดือนหน่วยนั้น "ยืนยัน" หรือ "ประมาณ"
      </p>
    </div>

    <Message v-if="isError" severity="error" :closable="false">
      {{ error?.message ?? 'ไม่สามารถโหลดข้อมูลได้' }}
    </Message>

    <DataTable
      v-else
      :value="rounds ?? []"
      :loading="isLoading"
      data-key="id"
      class="overflow-hidden rounded-lg border border-dark-border shadow"
    >
      <template #empty>
        <p class="py-4 text-center text-dark-muted">ยังไม่มีรอบเลื่อน</p>
      </template>

      <Column header="รอบ">
        <template #body="{ data }">{{ roundLabel(data) }}</template>
      </Column>
      <Column header="วันมีผล">
        <template #body="{ data }">{{ formatThaiDate(data.effective_date) }}</template>
      </Column>
      <Column header="ปีงบที่กระทบ">
        <template #body="{ data }">{{ data.fiscal_year ?? '—' }}</template>
      </Column>
      <Column header="นับในงบ">
        <template #body="{ data }">
          <ToggleSwitch
            :model-value="!!data.include_in_budget"
            @update:model-value="(v: boolean) => onToggleInclude(data, v)"
          />
        </template>
      </Column>
      <Column header="จัดการ" class="text-right">
        <template #body="{ data }">
          <Button label="สถานะรายหน่วย" size="small" text severity="info" @click="openProgress(data)" />
        </template>
      </Column>
    </DataTable>

    <Dialog
      v-model:visible="showProgress"
      :header="`สถานะการเลื่อน — รอบ ${activeRound ? roundLabel(activeRound) : ''}`"
      modal
      class="w-full max-w-2xl"
    >
      <div class="mb-3 flex items-center justify-between">
        <span class="text-sm text-dark-muted">
          เลื่อนเสร็จแล้ว {{ completedCount }} / {{ (progress ?? []).length }} หน่วยงาน
        </span>
        <Button
          label="สร้างแถวทุกหน่วยงาน"
          size="small"
          severity="secondary"
          :loading="seedMutation.isPending.value"
          @click="onSeedAll"
        />
      </div>

      <DataTable :value="progress ?? []" :loading="progressLoading" data-key="id" paginator :rows="15">
        <template #empty>
          <p class="py-3 text-center text-dark-muted">
            ยังไม่มีแถวติดตาม — กด "สร้างแถวทุกหน่วยงาน" เพื่อเริ่ม
          </p>
        </template>
        <Column field="organization_name" header="หน่วยงาน" />
        <Column header="สถานะ">
          <template #body="{ data }">
            <Tag
              :value="data.status === 'completed' ? 'เลื่อนเสร็จ (ยืนยัน)' : 'ยังไม่เสร็จ (ประมาณ)'"
              :severity="data.status === 'completed' ? 'success' : 'warn'"
            />
          </template>
        </Column>
        <Column header="เวลาที่เสร็จ">
          <template #body="{ data }">{{ data.completed_at ?? '—' }}</template>
        </Column>
        <Column header="" class="text-right">
          <template #body="{ data }">
            <Button
              :label="data.status === 'completed' ? 'ย้อนเป็นรอ' : 'ทำเครื่องหมายเสร็จ'"
              size="small"
              text
              :severity="data.status === 'completed' ? 'warn' : 'success'"
              @click="onToggleStatus(data)"
            />
          </template>
        </Column>
      </DataTable>
    </Dialog>
  </div>
</template>
