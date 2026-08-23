/**
 * Format a decimal-string amount with Thai grouping and 2 fraction digits.
 * Returns "-" for empty input.
 */
export function formatAmount(amount: string | null): string {
  if (!amount) return '-'
  return parseFloat(amount).toLocaleString('th-TH', { minimumFractionDigits: 2 })
}
