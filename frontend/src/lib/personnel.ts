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
