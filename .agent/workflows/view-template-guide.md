---
description: PHP View Template Guidelines - กติกาสำหรับสร้าง View ใหม่
---

# PHP View Template Guidelines

## ⚠️ สิ่งสำคัญ: ห้ามใช้ View::section()/endSection()

เมื่อสร้าง view files ใหม่ **ห้าม** ใช้ `View::section()` และ `View::endSection()` เพราะจะทำให้หน้าว่างเปล่า (blank page)

### ❌ ห้ามทำแบบนี้:
```php
<?php \App\Core\View::section('content'); ?>

<div>Content here...</div>

<?php \App\Core\View::endSection(); ?>
```

### ✅ ทำแบบนี้แทน:
```php
<div>Content here directly...</div>
```

เขียน HTML/PHP โดยตรงโดยไม่ต้อง wrap ด้วย section

---

## 📌 การใช้ URLs ใน Views

ทุก URL ที่เป็น internal links ต้องใช้ `View::url()` helper:

### ❌ ห้ามทำแบบนี้:
```php
<a href="/budgets">Budgets</a>
<form action="/login" method="POST">
```

### ✅ ทำแบบนี้แทน:
```php
<a href="<?= \App\Core\View::url('/budgets') ?>">Budgets</a>
<form action="<?= \App\Core\View::url('/login') ?>" method="POST">
```

สิ่งนี้จำเป็นเพราะ app อาจ deploy ที่ subdirectory (เช่น `/hr_budget/public/`)

---

## 📦 Layout ที่ใช้ได้

- `main` - สำหรับหน้าหลังจาก login (มี sidebar)
- `auth` - สำหรับหน้า login/forgot password

### การใช้ Layout ใน Controller:
```php
// วิธีที่ 1: ส่ง layout ใน render()
View::render('viewname', $data, 'main');

// วิธีที่ 2: setLayout ก่อน render
View::setLayout('auth');
View::render('auth/login', $data);
```

---

## 📋 View File Checklist

ก่อน commit view file ใหม่ ตรวจสอบ:

- [ ] ไม่มี `View::section()` หรือ `View::endSection()`
- [ ] ทุก internal URLs ใช้ `View::url()`
- [ ] ใช้ `View::csrf()` ในทุก form
- [ ] ใช้ `htmlspecialchars()` สำหรับ user input
- [ ] Test หน้าว่าแสดงผลถูกต้อง
