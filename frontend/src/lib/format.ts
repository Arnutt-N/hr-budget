/**
 * Format a decimal-string amount with Thai grouping and 2 fraction digits.
 * Returns "-" for empty input.
 */
export function formatBaht(amount: string | null): string {
  if (!amount) return '-'
  return parseFloat(amount).toLocaleString('th-TH', { minimumFractionDigits: 2 })
}

/** Human-readable file size — 1 decimal, B/KB/MB. */
export function formatSize(bytes: number): string {
  if (bytes >= 1_048_576) return `${(bytes / 1_048_576).toFixed(1)} MB`
  if (bytes >= 1024) return `${(bytes / 1024).toFixed(1)} KB`
  return `${bytes} B`
}
