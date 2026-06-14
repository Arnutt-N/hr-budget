<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'primevue/usetoast'
import {
  useCreateSession,
  useCreateRecord,
  useSaveRecord,
  useSessionActivities,
  useDisbursementRecord,
  useExpenseStructure,
} from '@/queries/useDisbursements'
import { useFiscalYearList } from '@/queries/useFiscalYears'
import { useOrganizationList } from '@/queries/useOrganizations'
import { useAuthStore } from '@/stores/auth'
import { useDisbursementWizard } from '@/stores/disbursementWizard'
import {
  MONTH_LABELS,
  type ExpenseItem,
  type SaveTrackingItem,
} from '@/types/disbursement'

const router = useRouter()
const toast = useToast()
const auth = useAuthStore()
const wizard = useDisbursementWizard()

const isAdmin = computed(() => auth.user?.role === 'admin')

// ---- Reference data ----
const { data: fiscalYears } = useFiscalYearList()
const { data: organizations } = useOrganizationList()
const { data: expenseTree } = useExpenseStructure()

// ---- Step 1 form ----
const orgId = ref<number | null>(null)
const fiscalYear = ref<number>(0)
const recordMonth = ref<number>(0)
const errorMsg = ref('')

const monthOptions = computed(() =>
  Object.entries(MONTH_LABELS).map(([value, label]) => ({ value: Number(value), label })),
)

// Default the fiscal year to the current one once the list arrives.
watch(
  fiscalYears,
  (list) => {
    if (!list || fiscalYear.value !== 0) return
    const current = list.find((fy) => fy.is_current)
    fiscalYear.value = current ? current.year : (list[0]?.year ?? 0)
  },
  { immediate: true },
)

const createSessionMut = useCreateSession()
const createRecordMut = useCreateRecord()
const saveRecordMut = useSaveRecord()

const canSubmitStep1 = computed(
  () =>
    (isAdmin.value ? orgId.value !== null && orgId.value > 0 : true) &&
    fiscalYear.value > 0 &&
    recordMonth.value >= 1 &&
    recordMonth.value <= 12,
)

async function submitStep1(): Promise<void> {
  errorMsg.value = ''
  try {
    // Staff send organization_id 0; the backend overrides it with their own org.
    const session = await createSessionMut.mutateAsync({
      organization_id: isAdmin.value ? Number(orgId.value) : 0,
      fiscal_year: fiscalYear.value,
      record_month: recordMonth.value,
    })
    wizard.setSession(session)
  } catch (e) {
    errorMsg.value = e instanceof Error ? e.message : 'ไม่สามารถสร้างรอบการบันทึกได้'
  }
}

// ---- Step 2: activities ----
const sessionId = computed(() => wizard.session?.id ?? 0)
const { data: activities, isLoading: activitiesLoading } = useSessionActivities(sessionId)
const selectedActivityId = ref<number | null>(null)

async function submitStep2(): Promise<void> {
  errorMsg.value = ''
  if (!wizard.session || !selectedActivityId.value) {
    errorMsg.value = 'กรุณาเลือกกิจกรรม'
    return
  }
  try {
    const record = await createRecordMut.mutateAsync({
      session_id: wizard.session.id,
      activity_id: selectedActivityId.value,
    })
    wizard.setRecord(record)
  } catch (e) {
    errorMsg.value = e instanceof Error ? e.message : 'ไม่สามารถสร้างรายการได้'
  }
}

// ---- Step 3: expense structure + amounts ----
const recordId = computed(() => wizard.record?.id ?? 0)
const { data: recordDetail, isLoading: recordLoading } = useDisbursementRecord(recordId)

const emptyAmount = (expenseItemId: number): SaveTrackingItem => ({
  expense_item_id: expenseItemId,
  allocated: '0',
  transfer: '0',
  disbursed: '0',
  pending: '0',
  po: '0',
})

// Active leaf items (everything we render an input row for), flattened.
// Header rows (is_header === 1) are section labels, not editable amounts —
// exclude them so they never reach the amounts map or the save payload.
const leafItems = computed<ExpenseItem[]>(() => {
  const out: ExpenseItem[] = []
  for (const type of expenseTree.value ?? []) {
    for (const group of type.groups) {
      for (const item of group.items) {
        if (item.is_active && !item.is_header) out.push(item)
      }
    }
  }
  return out
})

// Tracks which record's amounts are currently seeded, so picking a different
// activity re-prefills, while navigating back/forward within one record keeps edits.
const seededRecordId = ref(0)

// Once the tree + record detail are both loaded, seed the amounts map for
// every active item, prefilling from existing trackings. Re-seeds per record.
watch(
  [expenseTree, recordDetail],
  ([tree, detail]) => {
    if (!tree || !detail || wizard.step !== 3) return
    if (seededRecordId.value === detail.record.id) return
    seededRecordId.value = detail.record.id
    const next: Record<number, SaveTrackingItem> = {}
    for (const item of leafItems.value) {
      const existing = detail.trackings[item.id]
      next[item.id] = existing
        ? {
            expense_item_id: item.id,
            allocated: existing.allocated,
            transfer: existing.transfer,
            disbursed: existing.disbursed,
            pending: existing.pending,
            po: existing.po,
          }
        : emptyAmount(item.id)
    }
    wizard.setAmounts(next)
  },
  { immediate: true },
)

function num(v: string | undefined): number {
  const n = Number(v)
  return Number.isFinite(n) ? n : 0
}

// Parse a money string to integer cents (scale-2), rounding to the nearest
// cent. Mirrors the backend's bcmath scale-2 math so the preview agrees with
// the authoritative server result (float math like 0.1 + 0.2 would drift).
function toCents(v: string | undefined): number {
  return Math.round(num(v) * 100)
}

function remaining(itemId: number): number {
  const a = wizard.amounts[itemId]
  if (!a) return 0
  const cents =
    toCents(a.allocated) +
    toCents(a.transfer) -
    (toCents(a.disbursed) + toCents(a.pending) + toCents(a.po))
  return cents / 100
}

function formatMoney(value: number): string {
  return Number(value).toLocaleString('th-TH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })
}

function updateAmount(
  itemId: number,
  field: keyof Omit<SaveTrackingItem, 'expense_item_id'>,
  value: string,
): void {
  const current = wizard.amounts[itemId] ?? emptyAmount(itemId)
  wizard.setAmount(itemId, { ...current, [field]: value === '' ? '0' : value })
}

function goToStep3Summary(): void {
  errorMsg.value = ''
  wizard.goToStep(4)
}

// Items that have any non-zero amount — shown in the Step 4 summary.
const filledItems = computed(() =>
  leafItems.value.filter((item) => {
    const a = wizard.amounts[item.id]
    if (!a) return false
    return (
      num(a.allocated) !== 0 ||
      num(a.transfer) !== 0 ||
      num(a.disbursed) !== 0 ||
      num(a.pending) !== 0 ||
      num(a.po) !== 0
    )
  }),
)

const selectedActivityName = computed(() => {
  const id = selectedActivityId.value ?? wizard.record?.activity_id ?? 0
  return (activities.value ?? []).find((a) => a.activity_id === id)?.name_th ?? '-'
})

// ---- Step 4: save ----
async function submitFinal(): Promise<void> {
  errorMsg.value = ''
  if (!wizard.record) {
    errorMsg.value = 'ไม่พบรายการที่จะบันทึก'
    return
  }
  const items = Object.values(wizard.amounts)
  if (items.length === 0) {
    errorMsg.value = 'ไม่มีรายการรายจ่ายให้บันทึก'
    return
  }
  try {
    await saveRecordMut.mutateAsync({ id: wizard.record.id, body: { items } })
    toast.add({ severity: 'success', summary: 'บันทึกการเบิกจ่ายสำเร็จ', life: 3000 })
    wizard.reset()
    router.push('/disbursements')
  } catch (e) {
    errorMsg.value = e instanceof Error ? e.message : 'บันทึกไม่สำเร็จ'
    toast.add({
      severity: 'error',
      summary: 'บันทึกไม่สำเร็จ',
      detail: errorMsg.value,
      life: 5000,
    })
  }
}

function back(): void {
  errorMsg.value = ''
  wizard.previousStep()
}

function cancel(): void {
  wizard.reset()
  router.push('/disbursements')
}

const STEPS = [
  { n: 1, label: 'เลือกหน่วยงาน/เดือน' },
  { n: 2, label: 'เลือกกิจกรรม' },
  { n: 3, label: 'กรอกยอด' },
  { n: 4, label: 'สรุป' },
]

const AMOUNT_FIELDS: { key: keyof Omit<SaveTrackingItem, 'expense_item_id'>; label: string }[] = [
  { key: 'allocated', label: 'จัดสรร' },
  { key: 'transfer', label: 'โอน' },
  { key: 'disbursed', label: 'เบิกจ่าย' },
  { key: 'pending', label: 'ค้างจ่าย' },
  { key: 'po', label: 'PO' },
]
</script>

<template>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <h1 class="text-2xl font-bold text-white">บันทึกการเบิกจ่ายงบประมาณ</h1>
      <button type="button" @click="cancel" class="text-sm text-dark-muted hover:text-dark-text">
        &larr; กลับสู่รายการ
      </button>
    </div>

    <!-- Stepper -->
    <ol class="mb-6 flex flex-wrap items-center gap-2 text-sm">
      <li
        v-for="s in STEPS"
        :key="s.n"
        class="flex items-center gap-2 rounded-full px-3 py-1"
        :class="
          wizard.step === s.n
            ? 'bg-primary-600 text-white'
            : wizard.step > s.n
              ? 'bg-primary-900/40 text-primary-300'
              : 'bg-dark-card border border-dark-border text-dark-muted'
        "
      >
        <span class="font-semibold">{{ s.n }}</span>
        <span>{{ s.label }}</span>
      </li>
    </ol>

    <div v-if="errorMsg" class="mb-4 rounded bg-red-500/10 p-3 text-sm text-red-400" role="alert">
      {{ errorMsg }}
    </div>

    <div class="rounded-lg bg-dark-card border border-dark-border p-6 shadow">
      <!-- STEP 1 -->
      <section v-if="wizard.step === 1" class="space-y-4">
        <h2 class="text-lg font-semibold text-white">ขั้นที่ 1 — เลือกหน่วยงาน ปีงบ และเดือน</h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
          <div>
            <label class="mb-1 block text-sm font-medium text-dark-muted">หน่วยงาน</label>
            <select
              v-if="isAdmin"
              v-model="orgId"
              class="w-full rounded bg-dark-card border border-dark-border text-dark-text px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
            >
              <option :value="null">-- เลือกหน่วยงาน --</option>
              <option v-for="org in organizations ?? []" :key="org.id" :value="org.id">
                {{ org.name_th }}
              </option>
            </select>
            <p v-else class="rounded bg-dark-bg px-3 py-2 text-sm text-dark-muted">
              หน่วยงานของคุณ (กำหนดอัตโนมัติ)
            </p>
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
            <label class="mb-1 block text-sm font-medium text-dark-muted">เดือน</label>
            <select
              v-model.number="recordMonth"
              class="w-full rounded bg-dark-card border border-dark-border text-dark-text px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
            >
              <option :value="0">-- เลือกเดือน --</option>
              <option v-for="m in monthOptions" :key="m.value" :value="m.value">{{ m.label }}</option>
            </select>
          </div>
        </div>
        <div class="flex justify-end gap-3 border-t border-dark-border pt-4">
          <button
            type="button"
            @click="submitStep1"
            :disabled="!canSubmitStep1 || createSessionMut.isPending.value"
            class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-500 disabled:opacity-50"
          >
            {{ createSessionMut.isPending.value ? 'กำลังดำเนินการ...' : 'ถัดไป' }}
          </button>
        </div>
      </section>

      <!-- STEP 2 -->
      <section v-else-if="wizard.step === 2" class="space-y-4">
        <h2 class="text-lg font-semibold text-white">ขั้นที่ 2 — เลือกกิจกรรม</h2>
        <div v-if="activitiesLoading" class="py-6 text-center text-dark-muted">กำลังโหลดกิจกรรม...</div>
        <div v-else-if="(activities ?? []).length === 0" class="py-6 text-center text-dark-muted">
          ไม่พบกิจกรรมสำหรับรอบนี้
        </div>
        <div v-else class="space-y-2">
          <label
            v-for="act in activities ?? []"
            :key="act.activity_id"
            class="flex cursor-pointer items-start gap-3 rounded border border-dark-border p-3 hover:bg-slate-800/50"
            :class="selectedActivityId === act.activity_id ? 'border-primary-500 bg-primary-900/20' : ''"
          >
            <input
              type="radio"
              name="activity"
              :value="act.activity_id"
              v-model.number="selectedActivityId"
              class="mt-1"
            />
            <span class="flex-1">
              <span class="block text-sm text-dark-text">{{ act.name_th }}</span>
              <span v-if="act.code" class="block text-xs text-dark-muted">{{ act.code }}</span>
              <span
                v-if="act.record_status"
                class="mt-1 inline-block rounded bg-primary-900/40 px-2 py-0.5 text-xs text-primary-300"
              >
                มีข้อมูลแล้ว
              </span>
            </span>
          </label>
        </div>
        <div class="flex justify-between gap-3 border-t border-dark-border pt-4">
          <button
            type="button"
            @click="back"
            class="rounded-lg border border-dark-border px-4 py-2 text-sm text-dark-muted hover:bg-slate-800/50"
          >
            ย้อนกลับ
          </button>
          <button
            type="button"
            @click="submitStep2"
            :disabled="!selectedActivityId || createRecordMut.isPending.value"
            class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-500 disabled:opacity-50"
          >
            {{ createRecordMut.isPending.value ? 'กำลังดำเนินการ...' : 'ถัดไป' }}
          </button>
        </div>
      </section>

      <!-- STEP 3 -->
      <section v-else-if="wizard.step === 3" class="space-y-4">
        <h2 class="text-lg font-semibold text-white">ขั้นที่ 3 — กรอกยอดเบิกจ่าย</h2>
        <p class="text-sm text-dark-muted">กิจกรรม: {{ selectedActivityName }}</p>
        <div v-if="recordLoading" class="py-6 text-center text-dark-muted">กำลังโหลดข้อมูล...</div>
        <div v-else class="space-y-6">
          <div v-for="type in expenseTree ?? []" :key="type.id" class="space-y-3">
            <h3 class="text-base font-semibold text-primary-300">{{ type.name_th }}</h3>
            <div v-for="group in type.groups" :key="group.id" class="space-y-2">
              <h4 class="text-sm font-medium text-dark-text">{{ group.name_th }}</h4>
              <div class="overflow-x-auto rounded border border-dark-border">
                <table class="min-w-full divide-y divide-dark-border text-sm">
                  <thead class="bg-dark-bg">
                    <tr>
                      <th class="px-3 py-2 text-left text-xs font-medium text-dark-muted">รายการ</th>
                      <th
                        v-for="f in AMOUNT_FIELDS"
                        :key="f.key"
                        class="px-3 py-2 text-right text-xs font-medium text-dark-muted w-28"
                      >
                        {{ f.label }}
                      </th>
                      <th class="px-3 py-2 text-right text-xs font-medium text-dark-muted w-28">คงเหลือ</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-dark-border">
                    <tr
                      v-for="item in group.items.filter((i) => i.is_active && !i.is_header)"
                      :key="item.id"
                      class="hover:bg-slate-800/40"
                    >
                      <td class="px-3 py-2 text-dark-text">{{ item.name_th }}</td>
                      <td v-for="f in AMOUNT_FIELDS" :key="f.key" class="px-3 py-2">
                        <input
                          type="number"
                          min="0"
                          step="any"
                          :value="wizard.amounts[item.id]?.[f.key] ?? '0'"
                          @input="updateAmount(item.id, f.key, ($event.target as HTMLInputElement).value)"
                          :aria-label="`${item.name_th} ${f.label}`"
                          class="w-full rounded bg-dark-card border border-dark-border text-dark-text px-2 py-1 text-right text-sm focus:border-primary-500 focus:outline-none"
                        />
                      </td>
                      <td class="px-3 py-2 text-right text-dark-muted whitespace-nowrap">
                        {{ formatMoney(remaining(item.id)) }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <div class="flex justify-between gap-3 border-t border-dark-border pt-4">
          <button
            type="button"
            @click="back"
            class="rounded-lg border border-dark-border px-4 py-2 text-sm text-dark-muted hover:bg-slate-800/50"
          >
            ย้อนกลับ
          </button>
          <button
            type="button"
            @click="goToStep3Summary"
            class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-500"
          >
            ถัดไป
          </button>
        </div>
      </section>

      <!-- STEP 4 -->
      <section v-else class="space-y-4">
        <h2 class="text-lg font-semibold text-white">ขั้นที่ 4 — สรุปและบันทึก</h2>
        <dl class="grid grid-cols-1 gap-2 text-sm sm:grid-cols-3">
          <div>
            <dt class="text-dark-muted">หน่วยงาน</dt>
            <dd class="text-dark-text">{{ wizard.session?.org_name || '-' }}</dd>
          </div>
          <div>
            <dt class="text-dark-muted">ปีงบ / เดือน</dt>
            <dd class="text-dark-text">
              {{ wizard.session?.fiscal_year }} /
              {{ wizard.session ? MONTH_LABELS[wizard.session.record_month] : '-' }}
            </dd>
          </div>
          <div>
            <dt class="text-dark-muted">กิจกรรม</dt>
            <dd class="text-dark-text">{{ selectedActivityName }}</dd>
          </div>
        </dl>

        <div class="overflow-x-auto rounded border border-dark-border">
          <table class="min-w-full divide-y divide-dark-border text-sm">
            <thead class="bg-dark-bg">
              <tr>
                <th class="px-3 py-2 text-left text-xs font-medium text-dark-muted">รายการ</th>
                <th
                  v-for="f in AMOUNT_FIELDS"
                  :key="f.key"
                  class="px-3 py-2 text-right text-xs font-medium text-dark-muted"
                >
                  {{ f.label }}
                </th>
                <th class="px-3 py-2 text-right text-xs font-medium text-dark-muted">คงเหลือ</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-dark-border">
              <tr v-if="filledItems.length === 0">
                <td colspan="7" class="px-3 py-4 text-center text-dark-muted">
                  ยังไม่มีรายการที่กรอกยอด (จะบันทึกเป็น 0 ทั้งหมด)
                </td>
              </tr>
              <tr v-for="item in filledItems" :key="item.id">
                <td class="px-3 py-2 text-dark-text">{{ item.name_th }}</td>
                <td v-for="f in AMOUNT_FIELDS" :key="f.key" class="px-3 py-2 text-right text-dark-muted">
                  {{ formatMoney(num(wizard.amounts[item.id]?.[f.key])) }}
                </td>
                <td class="px-3 py-2 text-right text-dark-muted">{{ formatMoney(remaining(item.id)) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="flex justify-between gap-3 border-t border-dark-border pt-4">
          <button
            type="button"
            @click="back"
            class="rounded-lg border border-dark-border px-4 py-2 text-sm text-dark-muted hover:bg-slate-800/50"
          >
            ย้อนกลับ
          </button>
          <button
            type="button"
            @click="submitFinal"
            :disabled="saveRecordMut.isPending.value"
            class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-500 disabled:opacity-50"
          >
            {{ saveRecordMut.isPending.value ? 'กำลังบันทึก...' : 'บันทึก' }}
          </button>
        </div>
      </section>
    </div>
  </div>
</template>
