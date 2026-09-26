import { describe, expect, test } from 'vitest'
import { buildDocumentTitle } from '../routeTitle'

describe('buildDocumentTitle', () => {
  test('appends suffix to meta title', () => {
    // Arrange
    const meta = 'จัดการผู้ใช้'

    // Act
    const result = buildDocumentTitle(meta, 'BASE')

    // Assert
    expect(result).toBe('จัดการผู้ใช้ · HR Budget')
  })

  test('falls back when meta title is undefined', () => {
    expect(buildDocumentTitle(undefined, 'BASE')).toBe('BASE')
  })

  test('falls back when meta title is empty', () => {
    expect(buildDocumentTitle('', 'BASE')).toBe('BASE')
  })
})
