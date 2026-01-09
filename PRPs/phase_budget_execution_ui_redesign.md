# ปรับปรุง UI หน้า ผลการเบิกจ่ายงบประมาณ

> **เอกสารนี้**: แผนปรับปรุง UI สำหรับหน้าติดตามผลการเบิกจ่าย (`/budgets`)

![สถานะปัจจุบันของหน้า](C:/Users/TOPP/.gemini/antigravity/brain/24483d72-d1f9-4a57-bbd9-ed2352fc1fef/budget_page_current_state_1766143739864.png)

---

## สรุปการเปลี่ยนแปลง

| ลำดับ | รายการ | เดิม | ใหม่ |
|-------|--------|------|------|
| 1 | เมนูซ้าย | ติดตามผลการเบิกจ่าย | ผลการเบิกจ่ายงบประมาณ |
| 2 | Debug message | มี Pre-Render Probe... | ลบออก |
| 3 | หัวข้อหลัก (h1) | ติดตามผลการเบิกจ่าย | ลบออก |
| 4 | หัวข้อรอง (p muted) | ติดตามผลการเบิกจ่ายงบประมาณตาม Dimension | ผลการเบิกจ่ายงบประมาณ |
| 5 | ลำดับฟิลเตอร์ | ปีงบประมาณ, หน่วยงาน, แผนงาน, ค้นหา | ปีงบประมาณ, แผนงาน, หน่วยงาน, ค้นหา |
| 6 | การ์ด KPI | 4 การ์ด (เดิม) | 4 การ์ดใหม่ + สูตรคำนวณ |
| 7 | ตารางรายละเอียด | 7 คอลัมน์เดิม | 9 คอลัมน์ใหม่ |

---

## รายละเอียดการเปลี่ยนแปลง

### 1. เมนูซ้าย Sidebar

#### [MODIFY] [main.php](file:///c:/laragon/www/hr_budget/resources/views/layouts/main.php)

**Line 106** - เปลี่ยนชื่อเมนู:
```diff
- <span class="ml-3 nav-text">ติดตามผลการเบิกจ่าย</span>
+ <span class="ml-3 nav-text">ผลการเบิกจ่ายงบประมาณ</span>
```

---

### 2. หน้า Budget Execution

#### [MODIFY] [execution.php](file:///c:/laragon\www\hr_budget\resources\views\budgets\execution.php)

##### 2.1 ลบ Pre-Render Probe (Line 1-5)
```diff
- <?php if (isset($preRenderProbe)): ?>
- <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-3 mb-4">
-     <p class="text-yellow-400 text-sm font-mono"><?= htmlspecialchars($preRenderProbe) ?></p>
- </div>
- <?php endif; ?>
```

##### 2.2 แก้ไข Header (Line 10-13)
```diff
  <div>
-     <h1 class="text-2xl font-bold text-white">ติดตามผลการเบิกจ่าย</h1>
-     <p class="text-dark-muted text-sm mt-1">ติดตามผลการเบิกจ่ายงบประมาณตาม Dimension</p>
+     <p class="text-dark-muted text-sm mt-1">ผลการเบิกจ่ายงบประมาณ</p>
  </div>
```

##### 2.3 เรียงลำดับ Filter Bar ใหม่ (Line 29-67)

**ลำดับใหม่:**
1. ปีงบประมาณ (คงเดิม)
2. แผนงาน (ย้ายขึ้นมา)
3. หน่วยงาน (ย้ายลง)
4. ค้นหา (คงเดิม)

> [!CAUTION]
> **หมายเหตุเรื่องข้อมูล Q1-Q4**
> ปัจจุบันตาราง `fact_budget_execution` (Dimensional Model) ยังไม่มีคอลัมน์ Q1-Q4 การเพิ่มคอลัมน์ใน UI จะแสดงผลเป็น 0 ไปก่อนจนกว่าจะมีการปรับปรุงระบบ Import ข้อมูลให้รองรับรายไตรมาส

##### 2.4 การ์ด KPI (4 การ์ดใหม่)

| การ์ด | ชื่อ | สูตรคำนวณ (Field ใน Database) | สี |
|-------|------|---------------------------|-----|
| 1 | **งบประมาณจัดสรร** | - งบประมาณจัดสรร: `budget_act_amount`<br>- โอน/เปลี่ยนแปลง: `transfer_change_amount`<br>- งบประมาณจัดสรร สุทธิ: `budget_allocated_amount` | Blue |
| 2 | **รวมเบิกจ่าย** | - เบิกจ่าย: `disbursed_amount`<br>- ขออนุมัติ + PO: `po_pending_amount`<br>- รวมเบิกจ่าย: `total_spending_amount` | Orange |
| 3 | **คงเหลือ** | `balance_amount` (หรือ `budget_allocated_amount` - `total_spending_amount`) | Green |
| 4 | **อัตราการเบิกจ่าย** | `percent_disburse_incl_po` % และ KPI indicator | Yellow/Green |

##### 2.5 ตารางรายละเอียด (Line 176-223)

**การ mapping ข้อมูลในตาราง:**

| คอลัมน์ใหม่ | ข้อมูลที่ใช้ (Data Mapping) |
|------------|------------------------|
| **หมวดหมู่** | `item_name` (จาก `dim_budget_structure`) |
| **งบจัดสรร** | `budget_allocated_amount` |
| **Q1 - Q4** | *(ยังไม่มีข้อมูลใน Fact Table - แสดง 0)* |
| **รวมเบิกจ่าย** | `total_spending_amount` |
| **คงเหลือ** | `balance_amount` |
| **KPI %** | `percent_disburse_incl_po` |

---

## ไฟล์ที่ต้องแก้ไข

| ไฟล์ | การแก้ไข |
|------|----------|
| [main.php](file:///c:/laragon/www/hr_budget/resources/views/layouts/main.php) | เปลี่ยนชื่อเมนู (line 106) |
| [execution.php](file:///c:/laragon/www/hr_budget/resources/views/budgets/execution.php) | ลบ Pre-Render, แก้ Header, เรียงฟิลเตอร์, แก้การ์ด, แก้ตาราง |

---

## หมายเหตุสำคัญ

> [!IMPORTANT]
> **สูตรการคำนวณ**
> - งบประมาณจัดสรร สุทธิ = งบประมาณจัดสรร +/- โอน/เปลี่ยนแปลง
> - รวมเบิกจ่าย = เบิกจ่าย + ขออนุมัติ + PO
> - คงเหลือ = งบประมาณจัดสรร สุทธิ - รวมเบิกจ่าย

> [!NOTE]
> การ์ด "อัตราการเบิกจ่าย" จะแสดงทั้ง % และสถานะ KPI (สีเขียวหาก >= 80%, สีเหลืองหากต่ำกว่า)

---

## Verification Plan

### Manual Testing
1. เปิดหน้า `http://localhost/hr_budget/public/budgets`
2. ตรวจสอบ:
   - [ ] เมนูซ้าย แสดง "ผลการเบิกจ่ายงบประมาณ"
   - [ ] ไม่มีข้อความ Pre-Render Probe
   - [ ] ไม่มีหัวข้อ "ติดตามผลการเบิกจ่าย"
   - [ ] แสดง "ผลการเบิกจ่ายงบประมาณ" ด้วย p muted
   - [ ] ฟิลเตอร์เรียงลำดับ: ปีงบประมาณ, แผนงาน, หน่วยงาน, ค้นหา
   - [ ] การ์ด 4 ใบ แสดงข้อมูลถูกต้อง
   - [ ] ตารางแสดง 9 คอลัมน์: หมวดหมู่, งบจัดสรร, Q1-Q4, รวมเบิกจ่าย, คงเหลือ, KPI %
