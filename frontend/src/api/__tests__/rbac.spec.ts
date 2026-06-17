import { beforeEach, describe, expect, test, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import {
  fetchMyPermissions,
  fetchRoles,
  createRole,
  updateRole,
  deleteRole,
  fetchUserGrants,
  createGrant,
  deleteGrant,
} from '../rbac'
import { approveStep, rejectStep } from '../approvalChain'

/** Captures the fetch call for shape assertions; returns success envelopes. */
function stubFetch(status = 200, body: unknown = { success: true, data: {} }): void {
  vi.stubGlobal(
    'fetch',
    vi.fn().mockResolvedValue({
      status,
      json: () => Promise.resolve(body),
    }),
  )
}

function lastCall(): [string, RequestInit] {
  const calls = vi.mocked(fetch).mock.calls
  return calls[calls.length - 1] as unknown as [string, RequestInit]
}

describe('rbac api request shapes', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.unstubAllGlobals()
    stubFetch()
  })

  test('fetchMyPermissions GETs /api/v1/me/permissions', async () => {
    await fetchMyPermissions()
    const [url, init] = lastCall()
    expect(url).toBe('/api/v1/me/permissions')
    expect(init.method ?? 'GET').toBe('GET')
  })

  test('fetchRoles GETs /api/v1/roles', async () => {
    await fetchRoles()
    expect(lastCall()[0]).toBe('/api/v1/roles')
  })

  test('createRole POSTs the role with code + permissions', async () => {
    await createRole({ code: 'regional_supervisor', name_th: 'ผู้ดูแลภาค', permissions: ['request.view'] })
    const [url, init] = lastCall()
    expect(url).toBe('/api/v1/roles')
    expect(init.method).toBe('POST')
    expect(JSON.parse(init.body as string)).toEqual({
      code: 'regional_supervisor',
      name_th: 'ผู้ดูแลภาค',
      permissions: ['request.view'],
    })
  })

  test('updateRole PUTs the role with a JSON body', async () => {
    await updateRole(7, { is_active: false })
    const [url, init] = lastCall()
    expect(url).toBe('/api/v1/roles/7')
    expect(init.method).toBe('PUT')
    expect(JSON.parse(init.body as string)).toEqual({ is_active: false })
  })

  test('updateRole can rewrite the permission set', async () => {
    await updateRole(7, { name_th: 'ใหม่', permissions: ['request.view', 'request.approve'] })
    const [, init] = lastCall()
    expect(JSON.parse(init.body as string)).toEqual({
      name_th: 'ใหม่',
      permissions: ['request.view', 'request.approve'],
    })
  })

  test('deleteRole DELETEs the role by id', async () => {
    await deleteRole(7)
    const [url, init] = lastCall()
    expect(url).toBe('/api/v1/roles/7')
    expect(init.method).toBe('DELETE')
  })

  test('fetchUserGrants GETs the per-user grants path', async () => {
    await fetchUserGrants(42)
    expect(lastCall()[0]).toBe('/api/v1/users/42/access-grants')
  })

  test('createGrant POSTs the grant payload', async () => {
    await createGrant(42, { role_id: 3, scope_type: 'organization', scope_ref_id: 5 })
    const [url, init] = lastCall()
    expect(url).toBe('/api/v1/users/42/access-grants')
    expect(init.method).toBe('POST')
    expect(JSON.parse(init.body as string)).toEqual({
      role_id: 3,
      scope_type: 'organization',
      scope_ref_id: 5,
    })
  })

  test('deleteGrant DELETEs the grant by id', async () => {
    await deleteGrant(9)
    const [url, init] = lastCall()
    expect(url).toBe('/api/v1/access-grants/9')
    expect(init.method).toBe('DELETE')
  })
})

describe('approval chain api request shapes', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.unstubAllGlobals()
    stubFetch()
  })

  test('approveStep POSTs note (null when omitted)', async () => {
    await approveStep(11)
    const [url, init] = lastCall()
    expect(url).toBe('/api/v1/requests/11/approval/approve')
    expect(init.method).toBe('POST')
    expect(JSON.parse(init.body as string)).toEqual({ note: null })
  })

  test('rejectStep POSTs the required note', async () => {
    await rejectStep(11, 'งบไม่พอ')
    const [url, init] = lastCall()
    expect(url).toBe('/api/v1/requests/11/approval/reject')
    expect(JSON.parse(init.body as string)).toEqual({ note: 'งบไม่พอ' })
  })
})
