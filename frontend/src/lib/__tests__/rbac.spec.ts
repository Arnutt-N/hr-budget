import { describe, expect, test } from 'vitest'
import {
  scopeTypeLabel,
  activeSeverity,
  approvalActionLabel,
  canActOnChain,
} from '../rbac'

describe('scopeTypeLabel', () => {
  test('maps known scope types to Thai labels', () => {
    expect(scopeTypeLabel('all')).toBe('ทั้งหมด')
    expect(scopeTypeLabel('organization')).toContain('หน่วยงาน')
    expect(scopeTypeLabel('category')).toBe('หมวดงบ')
    expect(scopeTypeLabel('region')).toBe('ภูมิภาค')
  })

  test('falls back to the raw value for unknown scopes', () => {
    expect(scopeTypeLabel('mystery')).toBe('mystery')
  })
})

describe('activeSeverity', () => {
  test('returns success when active (1 / true), secondary otherwise', () => {
    expect(activeSeverity(1)).toBe('success')
    expect(activeSeverity(true)).toBe('success')
    expect(activeSeverity(0)).toBe('secondary')
    expect(activeSeverity(false)).toBe('secondary')
  })
})

describe('approvalActionLabel', () => {
  test('maps approved/rejected to Thai, passes through unknown', () => {
    expect(approvalActionLabel('approved')).toBe('อนุมัติ')
    expect(approvalActionLabel('rejected')).toBe('ปฏิเสธ')
    expect(approvalActionLabel('weird')).toBe('weird')
  })
})

describe('canActOnChain', () => {
  const approve = ['request.approve']

  test('true only when permitted, pending, and sitting on a level', () => {
    expect(canActOnChain(approve, { request_status: 'pending', current_level: 1 })).toBe(true)
  })

  test('false without the request.approve permission', () => {
    expect(canActOnChain(['request.view'], { request_status: 'pending', current_level: 1 })).toBe(
      false,
    )
  })

  test('false when not pending', () => {
    expect(canActOnChain(approve, { request_status: 'approved', current_level: null })).toBe(false)
  })

  test('false when not on a level', () => {
    expect(canActOnChain(approve, { request_status: 'pending', current_level: null })).toBe(false)
  })

  test('false when status is null', () => {
    expect(canActOnChain(approve, null)).toBe(false)
  })
})
