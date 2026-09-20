import { describe, expect, test } from 'vitest'
// @ts-expect-error TS7016: untyped JS config (strict + no allowJs)
import tailwindConfig from '../../../tailwind.config.js'

// Arrange — same relative-luminance formula as scripts/contrast.py
function luminance(hex: string): number {
  const c = hex.replace('#', '')
  const [r, g, b] = [0, 2, 4].map((i) => {
    const v = parseInt(c.slice(i, i + 2), 16) / 255
    return v <= 0.03928 ? v / 12.92 : ((v + 0.055) / 1.055) ** 2.4
  })
  return 0.2126 * r + 0.7152 * g + 0.0722 * b
}

function ratio(fg: string, bg: string): number {
  const [a, b] = [luminance(fg), luminance(bg)].sort((x, y) => y - x)
  return (a + 0.05) / (b + 0.05)
}

const primary = (tailwindConfig.theme?.extend?.colors?.primary ?? {}) as Record<string, string>

describe('design tokens (AA normal ≥ 4.5)', () => {
  test('white on primary-600 (#0369a1) passes', () => {
    // Arrange
    const bg = primary['600']

    // Act
    const r = ratio('#ffffff', bg)

    // Assert
    expect(bg).toBe('#0369a1')
    expect(r).toBeGreaterThanOrEqual(4.5)
  })

  test('white on primary-700 (#075985) passes', () => {
    // Arrange
    const bg = primary['700']

    // Act
    const r = ratio('#ffffff', bg)

    // Assert
    expect(bg).toBe('#075985')
    expect(r).toBeGreaterThanOrEqual(4.5)
  })

  test('primary-400 (#38bdf8) on dark card (#1e293b) passes', () => {
    // Act
    const r = ratio('#38bdf8', '#1e293b')

    // Assert
    expect(r).toBeGreaterThanOrEqual(4.5)
  })
})
