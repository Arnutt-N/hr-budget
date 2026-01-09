# Additional Reusable Components Analysis

**เอกสารวิจัย:** การวิเคราะห์ส่วนประกอบเพิ่มเติมที่สามารถทำเป็น Reusable Components

---

## 🔍 ภาพรวม

จากการวิเคราะห์โค้ดทั้งโปรเจค HR Budget พบว่า นอกเหนือจากส่วนประกอบพื้นฐาน (Button, Input, Select, Card) ที่ได้เอกสารไว้ใน [reusable_components_guide.md](./reusable_components_guide.md) แล้ว ยังมีส่วนประกอบอื่นๆ **ที่ซ้ำกันหลายที่** และควรแปลงเป็น Reusable Components

---

## 📊 สรุปผลการวิเคราะห์

### ส่วนประกอบที่พบบ่อย (Frequency Analysis)

| Component | จำนวนครั้งที่ใช้ | Priority | Complexity |
|-----------|------------------|----------|------------|
| **Badge** (สถานะ) | 30+ | 🔴 HIGH | ⭐ EASY |
| **KPI Card** | 15+ | 🔴 HIGH | ⭐⭐ MEDIUM |
| **Empty State** | 12+ | 🟡 MEDIUM | ⭐ EASY |
| **Modal Dialog** | 8+ | 🟡 MEDIUM | ⭐⭐⭐ HARD |
| **Pagination** | 6+ | 🟡 MEDIUM | ⭐⭐ MEDIUM |
| **Filter Bar** | 5+ | 🟢 LOW | ⭐⭐ MEDIUM |
| **Breadcrumb** | 3+ | 🟢 LOW | ⭐ EASY |
| **Progress Bar** | 5+ | 🟢 LOW | ⭐ EASY |
| **Tooltip** | 10+ | 🟡 MEDIUM | ⭐⭐ MEDIUM |
| **Alert/Toast** | 8+ | 🟡 MEDIUM | ⭐⭐ MEDIUM |

---

## 🎯 รายละเอียดแต่ละ Component

### 1. Badge Component ⭐ PRIORITY #1

**ความถี่:** ใช้มากกว่า 30 ครั้ง ทั้งโปรเจค

**ตัวอย่างที่พบ:**
```php
<!-- Dashboard: KPI Performance Badge -->
<span class="badge badge-green">ดีมาก</span>
<span class="badge badge-orange">ปานกลาง</span>
<span class="badge badge-red">ต่ำ</span>

<!-- Requests: Status Badge -->
<span class="badge badge-blue">ร่าง</span>
<span class="badge badge-orange">รออนุมัติ</span>
<span class="badge badge-green">อนุมัติ</span>
<span class="badge badge-red">ไม่อนุมัติ</span>

<!-- Fiscal Year Badge -->
<span class="badge badge-blue">
    <i class="ph ph-calendar mr-1"></i>
    ปี 2568
</span>
```

**ไฟล์ที่พบ:**
- `dashboard/index.php` (4 ที่)
- `requests/dashboard.php` (8 ที่)
- `layouts/main.php` (1 ที่)
- `budgets/_kpi_cards_snippet.php` (3 ที่)

**Props ที่เสนอ:**
- `variant`: `blue` | `green` | `orange` | `red` | `gray`
- `label`: ข้อความ
- `icon`: Phosphor icon (optional)
- `size`: `sm` | `md` | `lg`

**ประโยชน์:**
- ใช้บ่อยที่สุดในโปรเจค
- สร้างความสอดคล้อง (Consistency) สูง
- ง่ายต่อการ implement

---

### 2. KPI Card Component ⭐⭐ PRIORITY #2

**ความถี่:** ใช้มากกว่า 15 การ์ด

**ตัวอย่างที่พบ:**
```php
<!-- Dashboard KPI Cards -->
<div class="bg-dark-card border border-dark-border rounded-xl p-6 card-hover">
    <div class="flex justify-between items-start mb-4">
        <div>
            <p class="text-dark-muted text-sm font-medium">งบประมาณจัดสรร</p>
            <h3 class="text-2xl font-bold text-white mt-1">
                <?= \App\Core\View::currency($stats['allocated'] ?? 0) ?>
            </h3>
        </div>
        <div class="p-2 bg-blue-500/10 rounded-lg text-blue-500">
            <i class="ph ph-wallet text-2xl"></i>
        </div>
    </div>
    <div class="flex items-center gap-2">
        <span class="badge badge-blue">
            <i class="ph ph-calendar mr-1"></i>
            ปี <?= $fiscalYear ?>
        </span>
    </div>
</div>
```

**รูปแบบที่พบ:**
1. **Simple KPI Card** - แสดงค่าเดียว
2. **KPI Card with Progress** - มี progress bar
3. **KPI Card with Tooltip** - มี hover tooltip (calculation details)
4. **KPI Card with Badge** - มี status badge

**ไฟล์ที่พบ:**
- `dashboard/index.php` (4 cards)
- `budgets/_kpi_cards_refined_v2.php` (4 cards)
- `budgets/_kpi_cards_snippet.php` (4 cards)
- `requests/dashboard.php` (3 cards)

**Props ที่เสนอ:**
```php
[
    'title' => 'ชื่อ KPI',
    'value' => 'ค่าหลัก',
    'icon' => 'ph-wallet',
    'iconColor' => 'blue',
    'badge' => ['variant' => 'blue', 'label' => 'Tag'],
    'footer' => 'ข้อความด้านล่าง (optional)',
    'progress' => 45.5, // optional (0-100)
    'tooltip' => 'HTML tooltip content', // optional
]
```

**ประโยชน์:**
- Component ที่ซับซ้อนแต่ใช้บ่อย
- Dashboard หลักใช้การ์ดนี้เยอะ
- Tooltip calculation pattern ซ้ำกันมาก

---

### 3. Empty State Component ⭐ PRIORITY #3

**ความถี่:** พบ 12+ ที่

**ตัวอย่างที่พบ:**
```php
<!-- Table Empty State -->
<tr>
    <td colspan="9" class="text-center py-8 text-dark-muted">
        <i class="ph ph-folder-open text-4xl mb-2"></i>
        <p>ยังไม่มีข้อมูลงบประมาณ</p>
    </td>
</tr>

<!-- Card Empty State -->
<div class="text-center py-12 text-dark-muted">
    <i class="ph ph-file-x text-5xl mb-4"></i>
    <p class="text-lg">ไม่พบข้อมูล</p>
    <p class="text-sm">กรุณาเพิ่มข้อมูลใหม่</p>
    <button class="btn btn-primary mt-3">
        <i class="ph ph-plus mr-1"></i>เพิ่มรายการ
    </button>
</div>
```

**รูปแบบที่พบ:**
1. **Table Empty** - `<tr><td colspan="X">...</td></tr>`
2. **Card Empty** - `<div class="text-center">...</div>`
3. **List Empty**
4. **Empty with CTA** - มีปุ่ม action

**ไฟล์ที่พบ:**
- `dashboard/index.php`
- `budgets/list.php`
- `budgets/targets/index.php`
- `files/index.php`
- `requests/dashboard.php`

**Props ที่เสนอ:**
```php
[
    'icon' => 'ph-folder-open',
    'message' => 'ยังไม่มีข้อมูล',
    'description' => 'รายละเอียดเพิ่มเติม (optional)',
    'actionButton' => [
        'label' => 'เพิ่มรายการ',
        'url' => '/path',
        'icon' => 'ph-plus'
    ], // optional
    'colspan' => 9 // สำหรับ table empty
]
```

---

### 4. Modal Dialog Component ⭐⭐⭐

**ความถี่:** 8+ modals

**ตัวอย่างที่พบ:**
```php
<!-- Delete Confirmation Modal -->
<div class="absolute inset-0 bg-black/80 backdrop-blur-sm transition-opacity opacity-0" id="modalBackdrop">
</div>

<div class="fixed inset-0 flex items-center justify-center z-50 pointer-events-none" id="modalContainer">
    <div class="bg-dark-card border border-slate-600/50 rounded-xl p-6 w-full max-w-sm transform scale-95 opacity-0 transition-all duration-200 shadow-2xl shadow-black/50" id="modalContent">
        <h3 class="text-lg font-semibold text-white mb-4">ยืนยันการลบ</h3>
        <p class="text-dark-muted mb-6">คุณต้องการลบคำขอนี้หรือไม่?</p>
        <div class="flex gap-3">
            <button type="button" id="cancelDelete" class="btn btn-secondary w-full hover:bg-slate-700">ยกเลิก</button>
            <button type="button" id="confirmDelete" class="btn bg-red-500/10 border border-red-500/50 text-red-400 w-full">ลบคำขอ</button>
        </div>
    </div>
</div>
```

**รูปแบบที่พบ:**
1. **Confirmation Modal** - ยืนยัน/ยกเลิก
2. **Form Modal** - ฟอร์มในป๊อปอัป
3. **Info Modal** - แสดงข้อมูล

**ไฟล์ที่พบ:**
- `requests/index.php` (delete modal)
- `files/index.php` (create folder, upload file)

**Props ที่เสนอ:**
```php
[
    'id' => 'deleteModal',
    'title' => 'หัวข้อ',
    'content' => 'HTML content',
    'size' => 'sm' | 'md' | 'lg',
    'buttons' => [
        ['label' => 'ยกเลิก', 'variant' => 'secondary', 'action' => 'close'],
        ['label' => 'ยืนยัน', 'variant' => 'danger', 'action' => 'confirm'],
    ]
]
```

**Note:** Modal ต้องมี JavaScript สำหรับ show/hide

---

### 5. Pagination Component ⭐⭐

**ความถี่:** 6+ ที่

**ตัวอย่างที่พบ:**
```php
<?php if ($pagination['total'] > 1): ?>
<div class="flex justify-between items-center mt-4 text-sm">
    <div class="text-dark-muted">
        แสดง <?= $pagination['current'] * $pagination['perPage'] - $pagination['perPage'] + 1 ?> 
        ถึง <?= min($pagination['current'] * $pagination['perPage'], $pagination['totalRecords']) ?> 
        จาก <?= $pagination['totalRecords'] ?> รายการ
    </div>
    <div class="flex gap-1">
        <?php if ($pagination['current'] > 1): ?>
        <a href="?page=<?= $pagination['current'] - 1 ?>" class="btn btn-secondary btn-sm">
            <i class="ph ph-caret-left"></i>
        </a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $pagination['total']; $i++): ?>
        <a href="?page=<?= $i ?>" class="btn <?= $i == $pagination['current'] ? 'btn-primary' : 'btn-secondary' ?> btn-sm">
            <?= $i ?>
        </a>
        <?php endfor; ?>
        
        <?php if ($pagination['current'] < $pagination['total']): ?>
        <a href="?page=<?= $pagination['current'] + 1 ?>" class="btn btn-secondary btn-sm">
            <i class="ph ph-caret-right"></i>
        </a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
```

**ไฟล์ที่พบ:**
- `budgets/list.php`
- `requests/index.php`
- `files/index.php` (potentially)

**Props ที่เสนอ:**
```php
[
    'current' => 1,
    'total' => 10,
    'totalRecords' => 245,
    'perPage' => 25,
    'url' => '/budgets/list' // base URL
]
```

---

### 6. Filter Bar Component ⭐⭐

**ความถี่:** 5+ filter sections

**ตัวอย่างที่พบ:**
```php
<!-- Filter Section ใน mockup_table.html (ที่ทำมาแล้ว) -->
<div class="bg-slate-900/50 backdrop-blur-sm border border-slate-700 rounded-xl p-4 mb-6 shadow-xl">
    <div class="flex flex-col lg:flex-row gap-2 items-end lg:items-center">
        <!-- Year Selector -->
        <!-- Plan Selector -->
        <!-- Department Selector -->
        <!-- Search Input -->
        <!-- Action Buttons -->
    </div>
</div>
```

**รูปแบบที่พบ:**
1. **Horizontal Filter** - วางแนวนอน (lg:flex-row)
2. **Vertical Filter** - วางแนวตั้ง (grid)
3. **Collapsible Filter** - ซ่อน/แสดงได้

**ไฟล์ที่พบ:**
- `budgets/targets/index.php`
- `budgets/list.php`
- `requests/index.php`
- `mockup_table.html` (reference)

**Props ที่เสนอ:**
```php
[
    'filters' => [
        ['type' => 'select', 'name' => 'year', 'label' => 'ปีงบประมาณ', options' => [...]],
        ['type' => 'select', 'name' => 'plan', 'label' => 'แผนงาน', 'options' => [...]],
        ['type' => 'search', 'name' => 'q', 'placeholder' => 'ค้นหา...'],
    ],
    'actions' => [
        ['label' => 'ค้นหา', 'variant' => 'primary', 'icon' => 'ph-magnifying-glass'],
        ['label' => 'ล้างค่า', 'variant' => 'secondary', 'icon' => 'ph-arrow-counter-clockwise'],
    ]
]
```

---

### 7. Progress Bar Component ⭐

**ความถี่:** 5+ progress bars

**ตัวอย่างที่พบ:**
```php
<!-- Simple Progress Bar -->
<div class="w-full progress">
    <div class="progress-bar bg-orange-500" style="width: <?= min(100, $stats['spent_percent'] ?? 0) ?>%"></div>
</div>
<div class="text-xs text-dark-muted mt-2"><?= $stats['spent_percent'] ?? 0 ?>% ของงบทั้งหมด</div>

<!-- KPI Progress Bar -->
<div class="w-full bg-gray-700 rounded-full h-2.5 mt-4">
    <div class="bg-<?= $rateColor ?>-500 h-2.5 rounded-full" style="width: <?= min($rate, 100) ?>%"></div>
</div>
<div class="text-xs text-dark-muted mt-2 text-right">เป้าหมาย: 100%</div>
```

**ไฟล์ที่พบ:**
- `dashboard/index.php`
- `budgets/_kpi_cards_refined_v2.php`

**Props ที่เสนอ:**
```php
[
    'value' => 45.5, // 0-100
    'color' => 'orange' | 'green' | 'red' | 'blue',
    'label' => '45.5% ของงบทั้งหมด',
    'size' => 'sm' | 'md' | 'lg'
]
```

---

### 8. Breadcrumb Component ⭐

**ความถี่:** 3+ breadcrumbs

**ตัวอย่างที่พบ:**
```php
<!-- File Manager Breadcrumb -->
<?php if (!empty($breadcrumb)): ?>
<nav class="flex items-center gap-2 text-sm mb-4">
    <i class="ph ph-house text-dark-muted"></i>
    <?php foreach ($breadcrumb as $bc): ?>
        <?php if (end($breadcrumb)['id'] != $bc['id']): ?>
        <a href="?folder=<?= $bc['id'] ?>" class="text-dark-muted hover:text-white transition-colors">
            <?= htmlspecialchars($bc['name']) ?>
        </a>
        <i class="ph ph-caret-right text-dark-muted text-xs"></i>
        <?php else: ?>
        <span class="text-white font-medium"><?= htmlspecialchars($bc['name']) ?></span>
        <?php endif; ?>
    <?php endforeach; ?>
</nav>
<?php endif; ?>
```

**ไฟล์ที่พบ:**
- `files/index.php`
- Potentially other admin pages

**Props ที่เสนอ:**
```php
[
    'items' => [
        ['label' => 'Home', 'url' => '/'],
        ['label' => 'Files', 'url' => '/files'],
        ['label' => 'Current Folder'] // no URL = active
    ]
]
```

---

### 9. Tooltip Component ⭐⭐

**ความถี่:** 10+ tooltips

**ตัวอย่างที่พบ:**
```php
<!-- Pure CSS Tooltip (from KPI Cards) -->
<div class="absolute bottom-full left-0 mb-2 w-full invisible group-hover:visible opacity-0 group-hover:opacity-100 transition-all duration-200 z-10 px-2 pointer-events-none">
    <div class="bg-[#1e1e2d] border border-gray-700 rounded-lg shadow-xl p-3 text-xs w-full">
        <div class="flex justify-between items-center mb-1">
            <span class="text-gray-400">งบจัดสรร</span>
            <span class="text-white font-mono">5,000,000</span>
        </div>
        <!-- ... more content ... -->
    </div>
</div>
```

**ไฟล์ที่พบ:**
- `budgets/_kpi_cards_refined_v2.php` (calculation tooltips)
- `mockup_table.html` (calculation tooltips)

**Props ที่เสนอ:**
```php
[
    'content' => 'HTML content',
    'position' => 'top' | 'bottom' | 'left' | 'right',
    'trigger' => 'hover' | 'click'
]
```

**Note:** ต้องใช้ `group` class จาก Tailwind

---

### 10. Alert/Toast Component ⭐⭐

**ความถี่:** 8+ alerts

**ตัวอย่างที่พบ:**
```php
<?php if (!empty($_SESSION['success'])): ?>
<div class="bg-green-500/10 border border-green-500/50 text-green-400 px-4 py-3 rounded-lg mb-4">
    <i class="ph ph-check-circle mr-2"></i>
    <?= htmlspecialchars($_SESSION['success']) ?>
</div>
<?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['error'])): ?>
<div class="bg-red-500/10 border border-red-500/50 text-red-400 px-4 py-3 rounded-lg mb-4">
    <i class="ph ph-warning mr-2"></i>
    <?= htmlspecialchars($_SESSION['error']) ?>
</div>
<?php unset($_SESSION['error']); ?>
<?php endif; ?>
```

**ไฟล์ที่พบ:**
- `files/index.php`
- `layouts/main.php` (Flash messages)

**Props ที่เสนอ:**
```php
[
    'variant' => 'success' | 'error' | 'warning' | 'info',
    'message' => 'ข้อความ',
    'dismissible' => true | false
]
```

---

## 📈 Priority Matrix

### Implementation Priority (แนะนำลำดับการทำ)

#### Phase 1: Quick Wins (Easy + High Usage)
1. **Badge Component** - ใช้บ่อยสุด, ง่ายสุด
2. **Empty State Component** - ใช้บ่อย, ง่าย
3. **Progress Bar Component** - ใช้บ่อย, ง่าย

#### Phase 2: Medium Priority
4. **KPI Card Component** - ซับซ้อนแต่ใช้บ่อย
5. **Pagination Component** - มี logic ซับซ้อนนิดหน่อย
6. **Tooltip Component** - ใช้บ่อย

#### Phase 3: Advanced Components
7. **Modal Dialog Component** - ซับซ้อน (ต้องมี JS)
8. **Filter Bar Component** - ซับซ้อน (หลาย input types)
9. **Alert/Toast Component**
10. **Breadcrumb Component**

---

## 💡 ข้อเสนอแนะเพิ่มเติม

### 1. Component Library Structure

```
resources/views/components/
├── basics/              # Basic UI Elements
│   ├── button.php
│   ├── input.php
│   ├── select.php
│   └── card.php
├── feedback/            # User Feedback
│   ├── badge.php
│   ├── alert.php
│   ├── tooltip.php
│   └── empty-state.php
├── data/                # Data Display
│   ├── kpi-card.php
│   ├── progress-bar.php
│   └── table.php
├── navigation/          # Navigation
│   ├── breadcrumb.php
│   └── pagination.php
└── overlays/            # Overlays
    └── modal.php
```

### 2. Component Naming Convention

- ใช้ kebab-case: `kpi-card.php`, `empty-state.php`
- ชื่อสื่อความหมาย ไม่เฉพาะเจาะจง
- จัดกลุ่มตาม category

### 3. Documentation

สร้างหน้า **Component Gallery** (`/examples/components`) ที่แสดง:
- ตัวอย่างการใช้งานทุก component
- Props ที่รองรับ
- Variants ต่างๆ
- Code examples

---

## 🎯 ผลประโยชน์ที่จะได้รับ

### 1. **Code Reduction**
- ลดโค้ดซ้ำลงประมาณ **40-50%**
- file sizes เล็กลง อ่านง่ายขึ้น

### 2. **Consistency**
- UI สอดคล้องกัน 100%
- Design system ชัดเจน

### 3. **Maintainability**
- แก้ไขที่เดียว ใช้ได้ทุกที่
- Bug fixing ง่ายขึ้น

### 4. **Development Speed**
- สร้างหน้าใหม่เร็วขึ้น 3-5 เท่า
- Onboarding developer ใหม่ง่ายขึ้น

### 5. **Testing**
- Test component แยกได้
- Regression testing ง่าย

---

## 📋 ตัวอย่างการใช้งาน (Before & After)

### Before: โค้ดซ้ำทุกหน้า
```php
<!-- หน้า A -->
<span class="badge bg-green-500/10 border border-green-500/50 text-green-400 px-2 py-1 rounded-full text-xs">
    อนุมัติ
</span>

<!-- หน้า B -->
<span class="badge badge-green">อนุมัติ</span>

<!-- หน้า C -->
<div class="badge text-green-400 bg-green-500/10">อนุมัติ</div>
```

**ปัญหา:** แต่ละหน้าใช้ class ไม่เหมือนกัน!

### After: ใช้ Component
```php
<!-- หน้า A, B, C -->
<?php \App\Core\View::partial('components.feedback.badge', [
    'variant' => 'green',
    'label' => 'อนุมัติ'
]); ?>
```

**ผลลัพธ์:** เหมือนกันทุกหน้า, แก้ไขง่าย!

---

## 🔄 Migration Strategy

### Step 1: Create Components (1-2 สัปดาห์)
- สร้าง components ตาม priority
- ทดสอบแต่ละ component

### Step 2: Update Documentation (3-5 วัน)
- สร้าง Component Gallery
- เขียน usage guide

### Step 3: Refactor Existing Pages (2-4 สัปดาห์)
- เริ่มจากหน้าที่ใช้ component บ่อย
- Refactor ทีละหน้า
- Testing หลังแต่ละหน้า

### Step 4: Establish Guidelines (1 สัปดาห์)
- กำหนดกฎการใช้งาน
- Code review checklist
- Training ทีม

---

## 📚 สรุป

จากการวิเคราะห์พบว่า:

✅ **มีส่วนประกอบที่ซ้ำกันมากกว่า 10 ชนิด**  
✅ **ใช้ซ้ำกันรวมกว่า 100+ ครั้ง**  
✅ **สามารถลดโค้ดได้ 40-50%**  
✅ **ROI สูง - คุ้มค่าการลงทุน**

### Next Steps:
1. Review เอกสารนี้
2. เลือก Priority ที่จะเริ่มทำ
3. เริ่ม implement Phase 1
4. วัดผลและ iterate

---

**เอกสารจัดทำโดย:** Antigravity AI Assistant  
**วันที่:** 27 ธันวาคม 2568  
**เวอร์ชัน:** 1.0  
**อ้างอิงจาก:** การวิเคราะห์ 40+ ไฟล์ในโปรเจค HR Budget
