import { describe, expect, test } from 'vitest'
import { formatThaiDate } from '../date'

describe('formatThaiDate', () => {
  test('renders Buddhist-era year (2026 CE → 2569 BE)', () => {
    // Arrange
    const iso = '2026-06-12'

    // Act
    const result = formatThaiDate(iso)

    // Assert
    expect(result).toContain('2569')
    expect(result).toContain('12')
  })

  test('returns "-" for empty input', () => {
    expect(formatThaiDate('')).toBe('-')
    expect(formatThaiDate(null)).toBe('-')
    expect(formatThaiDate(undefined)).toBe('-')
  })

  test('returns "-" for unparseable input without throwing', () => {
    expect(formatThaiDate('not-a-date')).toBe('-')
  })
})
