# PRP: ThaID SPA parity — flash error surfacing + doc sync

วันที่: 2026-09-13
สถานะ: พร้อม implement

## บริบท / ปัญหา

SPA มี ThaID flow ครบแล้ว (ปุ่ม + status gate + full-page navigation ไป `/api/v1/auth/thaid/login`) —
ข้อมูลใน AGENTS.md/README ที่ว่า "SPA ยังไม่มี ThaID flow" ล้าสมัยกว่าโค้ด

ช่องว่างจริงที่เหลือ: **error path เงียบ**
- `ThaIdController::callback()` ทุก error path (provider error L106, state หมดอายุ L118, generic L137)
  และ mock fail (L75) จบด้วย redirect `/` + `$_SESSION['flash_error']` (ข้อความไทย)
- SPA boot ที่ `/` → router guard → `bootstrap()` ได้ 401 → redirect `/login`
- ไม่มีส่วนไหนของ SPA อ่าน flash (grep "flash" ใน frontend/src = 0 hit)
- ผล: user ThaID login fail แล้วเด้งกลับหน้า login โดยไม่รู้สาเหตุ

## เป้าหมาย

1. SPA แสดงข้อความ flash_error จาก ThaID flow บนหน้า login (ใช้ `errorMsg` + `<Message>` ที่มีอยู่)
2. กลไกอ่าน-แล้ว-ลบ (one-time consume) ฝั่ง API
3. AGENTS.md + README เลิกอ้างว่าเป็น parity gap

## แผนงาน

### A. Backend — endpoint อ่าน flash (ใหม่)

`src/Api/Controllers/ThaIdController.php` เพิ่ม action `flash()`:
- อ่าน `$_SESSION['flash_error']` ถ้ามี → `unset` ทันที (one-time) → `ApiResponse::ok(['message' => $msg])`
- ไม่มี → `ApiResponse::ok(['message' => null])`
- เรียก `CorsMiddleware::apply()` เหมือน `status()` (L46)
- public (ไม่ require auth) — flash เกิดตอนยังไม่ login

`routes/web.php` เพิ่ม (กลุ่ม thaid เดิม L64-66):
```
Router::get('/api/v1/auth/thaid/flash', [ApiThaIdController::class, 'flash']);
```

### B. Frontend — อ่าน flash บน LoginPage

`frontend/src/api/auth.ts` เพิ่ม:
```ts
export async function fetchThaidFlash(): Promise<string | null> {
  try {
    const res = await apiFetch<{ message: string | null }>('/auth/thaid/flash');
    return res.success ? (res.data?.message ?? null) : null;
  } catch {
    return null; // best-effort: ไม่ยุ่งกับ flow login ปกติ
  }
}
```

`frontend/src/pages/LoginPage.vue`:
- `onMounted` (ข้าง `fetchThaidStatus()`): `const msg = await fetchThaidFlash(); if (msg && !errorMsg.value) errorMsg.value = msg;`
- ไม่แตะ UI อื่น — `<Message severity="error">` มีอยู่แล้ว

### C. Docs sync

- `AGENTS.md`: แก้ย่อหน้า ThaID remnant — SPA มี flow ครบ (button/status/flash) เหลือ server-side คือ 302 alias + `/logout` session clear; ลบคำว่า "documented parity gap"
- `README.md`: legacy remnants list — อัปเดตสถานะ ThaID

### D. Tests

- Backend ใหม่ `tests/Unit/Api/ThaIdControllerFlashTest.php`:
  1. set `$_SESSION['flash_error']` → call `flash()` → 200 + message ตรง + **session ถูก clear** (assert ครั้งที่สองได้ null)
  2. ไม่มี flash → 200 + `message: null`
  3. CORS header ถูก apply
- Frontend `frontend/src/api/__tests__/auth.spec.ts` (ใหม่):
  - mock fetch คืน envelope `{success, data:{message:'...'}}` → ได้ string
  - fetch reject → ได้ null
  - ต้อง `setActivePinia` (apiFetch ใช้ auth store ตอน 401)

## ขอบเขต / ไม่ทำ

- ไม่แตะ OAuth flow จริง (state/PKCE/session_regenerate — ผ่านการทดสอบแล้ว)
- ไม่เปลี่ยน SameSite policy (session=Lax จำเป็นสำหรับ DOPA callback)
- ไม่ลบ 302 alias `/thaid/login` (อาจมี external link จาก DOPA registration — เก็บไว้)
- ไม่ย้าย flash เป็น query param (ข้อความไทยใน URL และ leak ผ่าน log/referrer)

## การยืนยัน

- `vendor/bin/phpunit --filter ThaIdControllerFlashTest` ผ่าน
- `cd frontend && npm run test:unit` ผ่าน
- `composer verify` + `cd frontend && npm run verify` ผ่าน (gate เต็ม)
- grep ยืนยัน AGENTS.md/README ไม่เหลือคำว่า parity gap เก่า

## ผลการ implement / deviations

1. **CORS-header test ใน §D ข้อ 3 ไม่ได้เขียน** — consistent กับ `ThaIdControllerStatusTest` ที่มีอยู่ เพราะ CLI PHPUnit assert response header ไม่ได้หากไม่มี xdebug; `CorsMiddleware::apply()` ยังถูกเรียกใน `flash()` ตามปกติ
2. **Post-review fix:** `bootstrap()` ใน `frontend/src/stores/auth.ts` เมื่อ me() สำเร็จ (authed) จะ consume-and-discard flash ไปเลย — กัน stale flash โผล่บนหน้า /login ในอนาคต (ผู้ใช้ที่ login อยู่แล้ว fail ThaID ไม่ต้องเห็น error); กรณี 401 ไม่ consume เพื่อให้ LoginPage ดึงไปแสดง และ `LoginPage.vue` ดึง status+flash พร้อมกันด้วย `Promise.all`
