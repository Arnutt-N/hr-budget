export const VACANCY_TYPE_OPTIONS: { value: string; label: string }[] = [
  { value: 'transfer_request', label: 'หนังสือขอรับโอน' },
  { value: 'eligibility_list', label: 'ประกาศขึ้นบัญชี' },
  { value: 'ready_to_fill', label: 'อัตราพร้อมบรรจุ' },
]

export function vacancyTypeLabel(v: string | null): string {
  return VACANCY_TYPE_OPTIONS.find((o) => o.value === v)?.label ?? v ?? '—'
}

export const CALC_MODE_OPTIONS: { value: string; label: string }[] = [
  { value: 'prorate', label: 'แบ่งตามเดือนจริง (prorate)' },
  { value: 'snapshot', label: 'นับเต็ม 12 เดือน (snapshot)' },
]

export const CATEGORY_OPTIONS: { value: string; label: string }[] = [
  { value: 'civil_servant', label: 'ข้าราชการ' },
  { value: 'government_employee', label: 'พนักงานราชการ' },
  { value: 'permanent_employee', label: 'ลูกจ้างประจำ' },
]

export const OCCUPANCY_OPTIONS: { value: string; label: string }[] = [
  { value: 'occupied', label: 'มีคนครอง' },
  { value: 'vacant_funded', label: 'ว่างมีเงิน' },
  { value: 'vacant_unfunded', label: 'ว่างไม่มีเงิน' },
]

export function categoryLabel(v: string | null): string {
  return CATEGORY_OPTIONS.find((o) => o.value === v)?.label ?? v ?? '—'
}

export function occupancyTag(v: string | null): { label: string; severity: string } {
  if (v === 'occupied') return { label: 'มีคนครอง', severity: 'success' }
  if (v === 'vacant_funded') return { label: 'ว่างมีเงิน', severity: 'warn' }
  if (v === 'vacant_unfunded') return { label: 'ว่างไม่มีเงิน', severity: 'secondary' }
  return { label: '—', severity: 'secondary' }
}
