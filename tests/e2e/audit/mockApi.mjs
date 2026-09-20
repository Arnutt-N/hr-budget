// Mock API layer for the SPA design-audit pass (AUDIT_MODE=mock, the default).
// Lets every authed route render with deterministic data and zero backend:
// enables axe, real-render contrast, overflow and focus checks per route.
export const ADMIN = { id: 1, email: 'admin@moj.go.th', name: 'ผู้ดูแลระบบ', role: 'admin' };

const LONG =
  'รายการทดสอบชื่อยาวมากเพื่อตรวจสอบการตัดคำและการล้นของตารางในหน้าจอขนาดเล็กและการแสดงผลของคอลัมน์ต่างๆ';

const item = (id, label, extra = {}) => ({
  id,
  name: id === 2 ? LONG : `${label} ${id}`,
  title: id === 2 ? LONG : `${label} ${id}`,
  code: `A-${String(id).padStart(3, '0')}`,
  status: id === 2 ? 'pending' : 'active',
  is_active: id !== 2,
  created_at: '2026-01-15T09:00:00+07:00',
  updated_at: '2026-02-01T10:00:00+07:00',
  ...extra,
});

const list = (label, n = 2) => ({
  success: true,
  data: Array.from({ length: n }, (_, i) => item(i + 1, label)),
  meta: { current_page: 1, last_page: 3, per_page: 15, total: 32, page: 1, total: 32 },
});

/** Shape-aware mock for GET-heavy audit rendering (mutations echo success). */
export function mockFor(url) {
  const path = url.pathname.replace(/^\/api\/v1/, '');
  if (path === '/auth/me' || path === '/auth/login') {
    return { success: true, data: path === '/auth/me' ? ADMIN : { token: 'mock', expires_in: 3600, user: ADMIN } };
  }
  if (path === '/auth/logout') return { success: true, data: null };
  if (path === '/auth/thaid/status') return { success: true, data: { enabled: false, mock: false } };
  if (path === '/auth/thaid/flash') return { success: true, data: null };
  if (path === '/me/permissions') return { success: true, data: ['*'] };
  // The bell's unread badge reads res.data.unread_count (useUnreadCount) —
  // the generic branch below returns an array there, which resolves the
  // query with undefined and spams one console error per route.
  if (path === '/notifications/unread-count') {
    return { success: true, data: { unread_count: 1 } };
  }
  if (path.startsWith('/notifications')) {
    return {
      success: true,
      data: [
        item(1, 'การแจ้งเตือน', { is_read: false, message: 'คำขอ A-001 ได้รับการอนุมัติแล้ว', type: 'approved', link: '/requests/1' }),
        item(2, 'การแจ้งเตือน', { is_read: true, message: 'รอบเลื่อนเงินเดือนใหม่', type: 'info', link: null }),
      ],
      meta: { unread_count: 1 },
    };
  }
  if (path.startsWith('/dashboard')) {
    return { success: true, data: { stats: { total_requests: 12, pending: 3, approved: 8, rejected: 1 }, fiscal_year: 2569 } };
  }
  if (path.startsWith('/analytics')) {
    return { success: true, data: { labels: ['ไตรมาส 1', 'ไตรมาส 2'], datasets: [{ label: 'จริง', data: [10, 20] }] } };
  }
  if (path.startsWith('/budget-execution')) return list('ผลเบิกจ่าย');
  if (path.startsWith('/disbursement')) return list('การเบิกจ่าย');
  if (path.startsWith('/expense-structure') || path.startsWith('/categories')) return list('หมวดรายจ่าย');
  // useVaultYears maps res.data as VaultYear[] — the generic branch below
  // returns a {folders,files} object, which crashes the page (.map on object).
  if (path === '/vault/years') {
    return { success: true, data: [{ fiscal_year: 2568 }, { fiscal_year: 2569 }] };
  }
  if (path.startsWith('/vault')) {
    return {
      success: true,
      data: { folders: [item(1, 'โฟลเดอร์'), item(2, 'โฟลเดอร์')], files: [item(1, 'ไฟล์', { size: 1024, mime: 'application/pdf' })] },
    };
  }
  // Role rows render data.permissions.length and role.name_th/code, and the
  // picker groups the catalogue by resource — generic items have neither.
  if (path === '/permissions') {
    return {
      success: true,
      data: [
        { id: 1, code: 'requests.view', name_th: 'ดูคำขอ', resource: 'คำขอ' },
        { id: 2, code: 'requests.approve', name_th: 'อนุมัติคำขอ', resource: 'คำขอ' },
        { id: 3, code: 'users.manage', name_th: 'จัดการผู้ใช้', resource: 'ผู้ใช้' },
      ],
    };
  }
  if (path === '/roles') {
    return {
      success: true,
      data: [
        {
          id: 1, code: 'admin', name_th: 'ผู้ดูแลระบบ', name_en: 'Administrator',
          description: null, is_system: 1, is_active: 1, sort_order: 1,
          permissions: ['requests.view', 'requests.approve', 'users.manage'],
        },
        {
          id: 2, code: 'OjwpHFFVRhfFOfcDQQHUh', name_th: 'รายการทดสอบชื่อยาวมากเพื่อตรวจสอบการตัดคำและการล้นของตารางในหน้าจอขนาดเล็กและการแสดงผลของคอลัมน์ต่างๆ', name_en: null,
          description: null, is_system: 0, is_active: 1, sort_order: 2,
          permissions: ['requests.view'],
        },
      ],
      meta: { current_page: 1, last_page: 1, per_page: 15, total: 2 },
    };
  }
  if (path.startsWith('/requests')) {
    // FileUploader binds f.original_name/file_type/file_size (FileAttachment)
    // — generic items leave the download link text empty (axe link-name).
    if (/\/requests\/\d+\/files/.test(path)) {
      return {
        success: true,
        data: [1, 2].map((n) => ({
          ...item(n, 'ไฟล์แนบ'),
          request_id: 1,
          original_name: n === 2 ? LONG + '.pdf' : `เอกสารประกอบ ${n}.pdf`,
          stored_name: `stored-${n}.pdf`,
          file_type: 'pdf',
          file_size: 1024 * n,
          mime_type: 'application/pdf',
          uploaded_by: 1,
        })),
      };
    }
    if (/\/requests\/\d+\/approval/.test(path)) return list('สายอนุมัติ');
    if (/\/requests\/\d+$/.test(path)) {
      return {
        success: true,
        data: {
          ...item(1, 'คำขอ'), amount: 50000, fiscal_year: '2569', organization: 'กองการเจ้าหน้าที่',
          items: [item(1, 'รายการ'), item(2, 'รายการ')], approvals: [item(1, 'อนุมัติ')],
        },
      };
    }
    // The list table binds data.request_title/request_status/total_amount/
    // created_by_name (BudgetRequest type) — generic items leave the title
    // link text empty, which trips axe link-name (serious).
    return {
      success: true,
      data: Array.from({ length: 2 }, (_, i) => ({
        ...item(i + 1, 'คำขอ'),
        request_title: i === 1 ? LONG : `คำขอ ${i + 1}`,
        request_status: i === 1 ? 'pending' : 'approved',
        total_amount: '50000',
        created_by_name: 'ผู้ทดสอบ',
      })),
      meta: { current_page: 1, last_page: 3, per_page: 15, total: 32, page: 1 },
    };
  }
  if (/\/\d+\/access-grants$/.test(path)) return list('สิทธิ์');
  if (/\/\d+$/.test(path)) return { success: true, data: item(1, 'รายการ') };
  return list('รายการ');
}

/** Intercept every /api/v1 call. Logged-out contexts get 401 except ThaID status. */
export async function installMockRoutes(page, authed) {
  await page.route('**/api/v1/**', async (rt) => {
    const url = new URL(rt.request().url());
    if (!authed && url.pathname !== '/api/v1/auth/thaid/status' && url.pathname !== '/api/v1/auth/thaid/flash') {
      return rt.fulfill({ status: 401, contentType: 'application/json', body: JSON.stringify({ success: false, error: 'unauthorized' }) });
    }
    return rt.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify(mockFor(url)) });
  });
}
