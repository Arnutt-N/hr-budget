---
description: PHP View Template Guidelines — server-rendered views are retired (post Phase 6 cutover)
---

# PHP View Template Guidelines

> **อัปเดต 2026-06-16:** หลัง Phase 6 cutover + การปลด legacy (`/budgets`, `/files`)
> และการ sweep view scaffolding — **server-rendered views ถูกเลิกใช้เกือบทั้งหมด**
> UI หลักคือ Vue SPA ใน `frontend/` บน `/api/v1/*` เอกสารนี้จึงเป็นแนวทางสำหรับ
> **view เดียวที่เหลือ** เท่านั้น

## ⛔ อย่าสร้าง server-rendered view ใหม่

เพิ่มหน้าใหม่ใน **Vue SPA (`frontend/src/pages/`)** เสมอ ไม่ใช่ `resources/views/`

## view ที่ยังเหลือจริง

`resources/views/errors/*` เท่านั้น — หน้า error แบบ **standalone HTML** (403/404/500/502/503/504/505)
render โดย:

- `App\Core\Auth` → `errors/403` (authz ล้มเหลว)
- `Router::notFound()` → `errors/404` (fallback เมื่อ SPA build หาย)
- `App\Core\ErrorHandler` → `errors/500` / `errors/{code}`

แต่ละไฟล์เป็น HTML document สมบูรณ์ในตัว — **ไม่มี layout wrapping** (`View::render` ตอนนี้
render แบบ standalone อย่างเดียว)

## API ที่ถูกลบไปแล้ว (อย่าใช้)

`View::setLayout()`, `View::section()`, `View::endSection()`, `View::yield()`,
`View::partial()`, `View::share()` — **ถูกลบ** พร้อม `layouts/**` + `components/**`
(กู้จาก tag `pre-views-sweep`). `View::render()` รับแค่ `(string $view, array $data)`

## ถ้าต้องแก้ error view

- ✅ ใช้ `View::url()` กับ internal links เสมอ — เช่น
  `href="<?= \App\Core\View::url('/') ?>"` — เพราะ deploy ใต้ subdirectory
  (`/hr_budget/public/`) ได้
- ✅ escape ค่าทุกตัวที่ไม่คงที่ด้วย `htmlspecialchars(...)` หรือ `View::e(...)`
- ✅ เป็น HTML document เต็มในไฟล์เดียว (เลียนแบบ `errors/404.php`)

## helper ของ View ที่ยังใช้ได้

`View::url()`, `View::baseUrl()`, `View::e()`, `View::date()/datetime()`,
`View::currency*()/number()`, `View::vite()`, `View::csrf()/csrfToken()/verifyCsrf()`
— ยังอยู่ใน `src/Core/View.php` (เผื่อ error view ใช้)
