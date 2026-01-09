# Reusable PHP Components Guide

**เอกสารวิจัย:** แนวทางการสร้าง UI Components แบบ Reusable สำหรับโปรเจค HR Budget

---

## 🎯 บทนำ

เอกสารนี้อธิบายแนวทางการแปลงดีไซน์จาก HTML Mockups ([mockup_form.html](./mockup_form.html), [mockup_table.html](./mockup_table.html)) ให้เป็น **Reusable PHP Components** ที่สามารถนำไปใช้ซ้ำได้ทั่วทั้งโปรเจค

---

## 📋 ปัญหาที่พบ

### Before (ก่อนใช้ Components)
```php
<!-- ต้องเขียนโค้ดซ้ำๆ ในทุกหน้า -->
<button class="inline-flex items-center gap-1 px-3 py-1.5 text-xs bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-500 transition-all shadow-md shadow-primary-500/20 whitespace-nowrap">
    <i data-lucide="plus" class="w-4 h-4"></i>เพิ่มรายการ
</button>
```

**ปัญหา:**
- 🔴 เขียนโค้ดซ้ำ (Code Duplication)
- 🔴 ยากต่อการแก้ไข (ต้องแก้หลายที่)
- 🔴 ความสอดคล้อง (Consistency) ไม่แน่นอน
- 🔴 ความซับซ้อนสูง (ยากต่อการอ่าน)

---

## ✅ แนวทางแก้ไข: Reusable Components

### After (ใช้ Components)
```php
<?php \App\Core\View::partial('components.button', [
    'variant' => 'primary',
    'icon' => 'plus',
    'label' => 'เพิ่มรายการ'
]); ?>
```

**ข้อดี:**
- ✅ เขียนแค่ครั้งเดียว ใช้ได้หลายที่
- ✅ แก้ไขง่าย (แก้ที่เดียว ใช้ได้ทุกที่)
- ✅ ความสอดคล้องสูง (Design Consistency)
- ✅ อ่านง่าย เข้าใจง่าย

---

## 🏗️ โครงสร้างที่เสนอ

```
resources/views/
├── components/           # ← โฟลเดอร์ใหม่สำหรับ Components
│   ├── button.php       # ปุ่มทุกชนิด
│   ├── input.php        # ช่องกรอกข้อมูล
│   ├── select.php       # Dropdown
│   ├── card.php         # Glassmorphism Card
│   └── table.php        # Table Component (optional)
└── examples/
    └── components.php   # Style Guide / Component Gallery
```

---

## 📦 Components ที่จะสร้าง

### 1. Button Component

**ไฟล์:** `resources/views/components/button.php`

**Props:**
- `variant`: `primary` | `secondary` | `danger` | `success`
- `label`: ข้อความบนปุ่ม
- `icon`: ชื่อ Lucide icon (ถ้ามี)
- `type`: `button` | `submit`
- `class`: CSS classes เพิ่มเติม
- `attributes`: HTML attributes อื่นๆ

**ตัวอย่างการใช้งาน:**
```php
<!-- Primary Button with Icon -->
<?php \App\Core\View::partial('components.button', [
    'variant' => 'primary',
    'label' => 'บันทึก',
    'icon' => 'save',
    'type' => 'submit'
]); ?>

<!-- Secondary Button -->
<?php \App\Core\View::partial('components.button', [
    'variant' => 'secondary',
    'label' => 'ยกเลิก',
    'icon' => 'x'
]); ?>

<!-- Danger Button -->
<?php \App\Core\View::partial('components.button', [
    'variant' => 'danger',
    'label' => 'ลบ',
    'icon' => 'trash-2'
]); ?>

<!-- Success Button -->
<?php \App\Core\View::partial('components.button', [
    'variant' => 'success',
    'label' => 'Import Excel',
    'icon' => 'file-spreadsheet'
]); ?>
```

**Variant Styles:**

| Variant | Background | Text Color | Use Case |
|---------|-----------|------------|----------|
| `primary` | `bg-primary-600` | `text-white` | การดำเนินการหลัก (Save, Submit) |
| `secondary` | `bg-slate-700` | `text-slate-100` | การดำเนินการรอง (Cancel) |
| `danger` | `bg-red-600` | `text-white` | การลบ/ทำลาย (Delete) |
| `success` | `bg-emerald-600` | `text-white` | Import, Export |

---

### 2. Input Component

**ไฟล์:** `resources/views/components/input.php`

**Props:**
- `name`: input name
- `label`: ข้อความ label
- `type`: `text` | `number` | `date` | `email`
- `value`: ค่า default
- `placeholder`: placeholder text
- `required`: `true` | `false`
- `error`: error message (ถ้ามี)

**ตัวอย่างการใช้งาน:**
```php
<!-- Text Input -->
<?php \App\Core\View::partial('components.input', [
    'name' => 'budget_name',
    'label' => 'ชื่อรายการ',
    'type' => 'text',
    'placeholder' => 'เช่น ค่าวัสดุสำนักงาน',
    'required' => true
]); ?>

<!-- Number Input with Error -->
<?php \App\Core\View::partial('components.input', [
    'name' => 'amount',
    'label' => 'จำนวนเงิน',
    'type' => 'number',
    'value' => '1000000',
    'error' => 'จำนวนเงินต้องมากกว่า 0'
]); ?>
```

**Features:**
- ความสูงมาตรฐาน: `38px` (h-[38px])
- Focus states: `focus:border-primary-500 focus:ring-1 focus:ring-primary-500/20`
- Error states: `border-red-500 text-red-500`

---

### 3. Select Component

**ไฟล์:** `resources/views/components/select.php`

**Props:**
- `name`: select name
- `label`: ข้อความ label
- `options`: array ของ options `['value' => 'label']`
- `value`: ค่าที่เลือก
- `required`: `true` | `false`
- `icon`: Lucide icon ด้านซ้าย (optional)

**ตัวอย่างการใช้งาน:**
```php
<!-- Simple Select -->
<?php \App\Core\View::partial('components.select', [
    'name' => 'year',
    'label' => 'ปีงบประมาณ',
    'options' => [
        '2568' => '2568',
        '2567' => '2567',
        '2566' => '2566'
    ],
    'value' => '2568'
]); ?>

<!-- Select with Icon -->
<?php \App\Core\View::partial('components.select', [
    'name' => 'category',
    'label' => 'หมวดหมู่',
    'icon' => 'folder',
    'options' => [
        '1' => 'งบบุคลากร',
        '2' => 'งบดำเนินงาน',
        '3' => 'งบลงทุน'
    ]
]); ?>
```

**Features:**
- Custom chevron-down icon (Lucide)
- `appearance-none` + custom SVG icon
- Consistent height: `38px`

---

### 4. Card Component

**ไฟล์:** `resources/views/components/card.php`

**Props:**
- `title`: หัวข้อการ์ด
- `icon`: Lucide icon
- `class`: CSS classes เพิ่มเติม
- **Content:** ส่งผ่าน slot หรือ render ภายใน

**ตัวอย่างการใช้งาน:**
```php
<!-- Method 1: Using content parameter -->
<?php \App\Core\View::partial('components.card', [
    'title' => 'รายการงบประมาณ',
    'icon' => 'table',
    'content' => '<p>Card content here</p>'
]); ?>

<!-- Method 2: Wrapping content -->
<?php ob_start(); ?>
<div class="space-y-4">
    <p>Custom content</p>
    <button>Action</button>
</div>
<?php $content = ob_get_clean(); ?>

<?php \App\Core\View::partial('components.card', [
    'title' => 'ฟอร์มเพิ่มรายการ',
    'icon' => 'edit',
    'content' => $content
]); ?>
```

**Styling:**
- Glassmorphism: `bg-slate-900/50 backdrop-blur-sm`
- Border: `border border-slate-700`
- Rounded: `rounded-xl`
- Shadow: `shadow-2xl`

---

## 🔧 การใช้งาน View::partial()

โปรเจคนี้ใช้ **Custom PHP Framework** ที่มี `\App\Core\View::partial()` method อยู่แล้ว

### Syntax:
```php
\App\Core\View::partial(string $view, array $data = [])
```

### ตัวอย่าง:
```php
<?php
// Call button component
\App\Core\View::partial('components.button', [
    'variant' => 'primary',
    'label' => 'คลิกที่นี่',
    'icon' => 'check'
]);
?>
```

### ภายใน Component File (button.php):
```php
<?php
// Extract variables from $data array
$variant = $variant ?? 'primary';
$label = $label ?? 'Button';
$icon = $icon ?? null;
$type = $type ?? 'button';

// Build CSS classes
$classes = match($variant) {
    'primary' => 'bg-primary-600 text-white hover:bg-primary-500',
    'secondary' => 'bg-slate-700 text-slate-100 hover:bg-slate-600',
    'danger' => 'bg-red-600 text-white hover:bg-red-500',
    'success' => 'bg-emerald-600 text-white hover:bg-emerald-500',
    default => 'bg-primary-600 text-white'
};
?>

<button 
    type="<?= $type ?>"
    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs rounded-lg font-medium transition-all whitespace-nowrap <?= $classes ?>">
    <?php if ($icon): ?>
        <i data-lucide="<?= $icon ?>" class="w-4 h-4"></i>
    <?php endif; ?>
    <?= htmlspecialchars($label) ?>
</button>
```

---

## 📚 Style Guide / Component Gallery

สร้างหน้า **Component Gallery** เพื่อแสดงตัวอย่างการใช้งาน Components ทั้งหมด

**เส้นทาง:** `/examples/components`

**ไฟล์:** `resources/views/examples/components.php`

**เนื้อหา:**
- แสดง Button ทุก variant
- แสดง Input, Select, Card
- แสดง Hover states
- แสดง Error states
- เป็น Living Documentation

---

## 🎨 Design Tokens

### Colors
```css
/* Primary (Blue) */
--primary-400: #38bdf8
--primary-500: #0ea5e9
--primary-600: #0284c7

/* Dark Theme */
--slate-700: #334155
--slate-800: #1e293b
--slate-900: #0f172a

/* Success (Green) */
--emerald-500: #10b981
--emerald-600: #059669

/* Danger (Red) */
--red-500: #ef4444
--red-600: #dc2626
```

### Spacing
- Button height: `38px` (py-1.5 for text-xs)
- Input height: `38px`
- Gap between icon and text: `4px` (gap-1)
- Card padding: `p-8`

### Typography
- Font: `Noto Sans Thai`
- Button text: `text-xs` (12px)
- Label text: `text-[10px]` (uppercase, bold)
- Numbers: `tabular-nums`

### Icons
- Library: **Lucide Icons** (https://lucide.dev)
- Size: `w-4 h-4` (16px)
- Initialize: `lucide.createIcons()`

---

## 📖 ตัวอย่างการใช้งานจริง

### ฟอร์มเพิ่มรายการงบประมาณ

```php
<!-- resources/views/budgets/form.php -->
<?php \App\Core\View::partial('components.card', [
    'title' => 'เพิ่มรายการงบประมาณ',
    'icon' => 'edit',
    'content' => ob_get_clean()
]); ob_start(); ?>

<form action="/budgets" method="POST" class="space-y-6">
    <?= \App\Core\View::csrf() ?>
    
    <?php \App\Core\View::partial('components.input', [
        'name' => 'budget_name',
        'label' => 'ชื่อรายการ',
        'type' => 'text',
        'required' => true
    ]); ?>
    
    <?php \App\Core\View::partial('components.select', [
        'name' => 'category_id',
        'label' => 'หมวดหมู่',
        'icon' => 'folder',
        'options' => $categories,
        'required' => true
    ]); ?>
    
    <?php \App\Core\View::partial('components.input', [
        'name' => 'amount',
        'label' => 'จำนวนเงิน',
        'type' => 'number',
        'required' => true
    ]); ?>
    
    <div class="flex gap-3">
        <?php \App\Core\View::partial('components.button', [
            'variant' => 'primary',
            'label' => 'บันทึก',
            'icon' => 'save',
            'type' => 'submit'
        ]); ?>
        
        <?php \App\Core\View::partial('components.button', [
            'variant' => 'secondary',
            'label' => 'ยกเลิก',
            'icon' => 'x'
        ]); ?>
    </div>
</form>

<?php $content = ob_get_clean(); ?>
```

---

## 🚀 ขั้นตอนการ Implement

### Phase 1: Core Components
1. สร้างโฟลเดอร์ `resources/views/components/`
2. สร้าง `button.php`
3. สร้าง `input.php`
4. สร้าง `select.php`
5. สร้าง `card.php`

### Phase 2: Documentation
1. สร้าง `resources/views/examples/components.php`
2. เพิ่ม route `/examples/components` ใน `routes/web.php`
3. ทดสอบการแสดงผล

### Phase 3: Refactoring
1. Refactor หน้าเดิมให้ใช้ components
2. เริ่มจากหน้าง่ายๆ ก่อน (เช่น ฟอร์ม)
3. ค่อยๆ ขยายไปหน้าอื่นๆ

---

## ✅ ข้อดีของ Component-Based Approach

### 1. **Maintainability** (ดูแลรักษาง่าย)
- แก้ไขที่เดียว ใช้ได้ทุกที่
- ลด bugs จากการ copy-paste

### 2. **Consistency** (ความสอดคล้อง)
- ดีไซน์เหมือนกันทั่วโปรเจค
- UX/UI ที่ดีขึ้น

### 3. **Productivity** (ประหยัดเวลา)
- เขียนโค้ดเร็วขึ้น
- ลดเวลา debugging

### 4. **Scalability** (ขยายได้ง่าย)
- เพิ่ม component ใหม่ได้ง่าย
- รองรับการเติบโตของโปรเจค

### 5. **Documentation** (มีเอกสารชัดเจน)
- Style Guide เป็น Living Documentation
- Developer ใหม่เรียนรู้ได้ง่าย

---

## 📝 Best Practices

### 1. **Naming Convention**
- ใช้ชื่อที่สื่อความหมาย: `button.php`, `input.php`
- ไม่ใช้ชื่อเฉพาะเจาะจง: `blue-button.php` ❌

### 2. **Props Validation**
```php
// ตรวจสอบ required props
if (!isset($label)) {
    throw new \InvalidArgumentException('Label is required');
}

// Default values
$variant = $variant ?? 'primary';
$icon = $icon ?? null;
```

### 3. **CSS Classes Organization**
```php
// Base classes (เหมือนกันทุก variant)
$baseClasses = 'inline-flex items-center gap-1 px-3 py-1.5 text-xs rounded-lg font-medium transition-all whitespace-nowrap';

// Variant classes (แตกต่างกันตาม variant)
$variantClasses = match($variant) {
    'primary' => 'bg-primary-600 text-white hover:bg-primary-500',
    // ...
};

$classes = "{$baseClasses} {$variantClasses}";
```

### 4. **Accessibility**
- ใส่ `aria-label` ให้ icon-only buttons
- ใช้ semantic HTML
- รองรับ keyboard navigation

---

## 🔗 เอกสารอ้างอิง

- [mockup_form.html](./mockup_form.html) - ฟอร์มตัวอย่าง
- [mockup_table.html](./mockup_table.html) - ตารางตัวอย่าง
- [Lucide Icons](https://lucide.dev) - Icon library
- [Tailwind CSS](https://tailwindcss.com) - CSS framework

---

## 📌 สรุป

การสร้าง **Reusable Components** จะช่วยให้:
1. ✅ โค้ดสะอาด อ่านง่าย
2. ✅ ดูแลรักษาง่าย
3. ✅ ดีไซน์สอดคล้องทั่วโปรเจค
4. ✅ ประหยัดเวลาในการพัฒนา
5. ✅ ทีมใหม่เข้าใจและใช้งานได้ง่าย

**Next Steps:** 
- Review เอกสารนี้
- อนุมัติการ implement
- เริ่มสร้าง components ตามแผน

---

**เอกสารจัดทำโดย:** Antigravity AI Assistant  
**วันที่:** 27 ธันวาคม 2568  
**เวอร์ชัน:** 1.0
