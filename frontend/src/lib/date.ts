import dayjs from 'dayjs'
import buddhistEra from 'dayjs/plugin/buddhistEra'
import 'dayjs/locale/th'

dayjs.extend(buddhistEra)
dayjs.locale('th')

/**
 * Format a date in Thai with Buddhist-era year, e.g. "12 มิ.ย. 2569".
 * Returns "-" for empty or unparseable input.
 */
export function formatThaiDate(value: string | Date | null | undefined): string {
  if (!value) return '-'
  const d = dayjs(value)
  if (!d.isValid()) return '-'
  return d.format('D MMM BBBB')
}
