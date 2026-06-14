import { defineStore } from 'pinia'
import { ref } from 'vue'
import type {
  DisbursementRecord,
  DisbursementSession,
  SaveTrackingItem,
} from '@/types/disbursement'

/**
 * UI-only draft state for the 4-step disbursement wizard.
 *
 * Holds ONLY transient client state (which step, the chosen session/record,
 * and the amounts being typed). Server truth lives in TanStack Query — this
 * store never caches list/detail responses.
 *
 * `amounts` is keyed by expense_item_id; each entry is a full SaveTrackingItem
 * so Step 3 inputs bind directly and Step 4 submits the map's values as-is.
 */
export const useDisbursementWizard = defineStore('disbursementWizard', () => {
  const step = ref(1)
  const session = ref<DisbursementSession | null>(null)
  const record = ref<DisbursementRecord | null>(null)
  const amounts = ref<Record<number, SaveTrackingItem>>({})

  /** Step 1 result: store the chosen session and advance the wizard. */
  function setSession(s: DisbursementSession, nextStep = 2): void {
    session.value = s
    step.value = nextStep
  }

  /** Step 2 result: store the created record and advance to Step 3. */
  function setRecord(r: DisbursementRecord, nextStep = 3): void {
    record.value = r
    step.value = nextStep
  }

  /** Replace the full amounts map (seeding from record detail). */
  function setAmounts(next: Record<number, SaveTrackingItem>): void {
    amounts.value = next
  }

  /** Immutably upsert one item's amounts (Step 3 input edits). */
  function setAmount(itemId: number, item: SaveTrackingItem): void {
    amounts.value = { ...amounts.value, [itemId]: item }
  }

  /** Jump to a specific step (forward navigation, e.g. Step 3 → summary). */
  function goToStep(n: number): void {
    step.value = n
  }

  /** Go back one step (never below 1). */
  function previousStep(): void {
    if (step.value > 1) step.value -= 1
  }

  function reset(): void {
    step.value = 1
    session.value = null
    record.value = null
    amounts.value = {}
  }

  return {
    step,
    session,
    record,
    amounts,
    setSession,
    setRecord,
    setAmounts,
    setAmount,
    goToStep,
    previousStep,
    reset,
  }
})
