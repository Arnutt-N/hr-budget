# Research: Modern Forms & Data Tables for HR Budget

This document outlines the research and design guidelines for creating modern, beautiful, and functional input forms and data tables for the HR Budget project. The designs are tailored to the existing **Tailwind CSS** stack and **Dark/Glassmorphism** theme.

## 1. Design Philosophy

The project uses a dark theme with glassmorphism components. All new elements must align with this aesthetic:
- **Backgrounds**: Deep dark blues (`#0f172a`, `#1e293b`).
- **Accent**: Primary Blue (`#0ea5e9` to `#0284c7`).
- **Text**: High contrast for readability, muted for secondary labels.
- **Glass Effect**: `rgba(30, 41, 59, 0.7)` with blur for panels and sticky headers.

## 2. Input Forms

Forms in a budget application must focus on **accuracy** and **speed** of data entry.

### 2.1 Modern Input Styles
Inputs should feel tactile and interactive.
- **State**: Default, Hover, Focus, Error, Disabled.
- **Style**:
    - Background: Darker than the card (`#0f172a` or `#334155` depending on context).
    - Border: Thin, subtle (`border-slate-700`), glowing on focus (`ring-2 ring-primary-500/50`).
    - Text: White/Off-white (`text-slate-100`).
    - Placeholder: Muted (`text-slate-500`).

```html
<!-- Example of a Modern Input Field -->
<div class="group relative">
    <label for="budget_amount" class="block text-sm font-medium text-slate-400 mb-1">
        งบประมาณที่จัดสรร (Allocated)
    </label>
    <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <span class="text-slate-500">฿</span>
        </div>
        <input 
            type="number" 
            name="budget_amount" 
            id="budget_amount"
            class="block w-full pl-8 pr-12 py-2.5 bg-slate-900 border border-slate-700 rounded-lg 
                   text-slate-100 placeholder-slate-500
                   focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500
                   transition-all duration-200"
            placeholder="0.00"
        >
        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
            <span class="text-slate-500 text-sm">THB</span>
        </div>
    </div>
</div>
```

### 2.2 Currency & Number Formatting
For financial data, standard text inputs are prone to error.
- **Recommendation**: Use a light JavaScript wrapper (e.g., plain JS or a lightweight lib) to handle commas automatically on blur/focus.
- **Alignment**: Right-aligned text for numbers is standard practice.

### 2.3 Selects and Dropdowns
Native selects can look ugly in dark mode on Windows.
- **Custom Style**: Use a wrapper div to style the arrow and background.
```html
<div class="relative">
    <select class="appearance-none block w-full pl-3 pr-10 py-2.5 bg-slate-900 ...">
        <option>2568</option>
        <option>2569</option>
    </select>
    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
        <svg class="h-4 w-4 fill-current" ...>...</svg>
    </div>
</div>
```

## 3. Data Tables

Tables are the core of the application. They must handle high density without feeling cluttered.

### 3.1 Structural Design
- **Sticky Headers**: Essential for long lists. Use `sticky top-0 z-10`.
- **Row Hover**: Helps track the active line across wide tables.
- **Zebra Striping**: Optional, but subtle striping (`odd:bg-slate-800/50`) helps readability.

### 3.2 Numeric Alignment
- **Rule**: All monetary values must be **Right Aligned**.
- **Font**: Use a monospaced font or tabular-nums (`font-mono` or `tabular-nums`) for numbers to ensure digits align vertically.

### 3.3 Status Badges
Use the existing badge classes `badge badge-blue`, etc., but ensure they are vertically centered.

### 3.4 Table Example Code
```html
<div class="overflow-x-auto rounded-xl border border-slate-700 shadow-xl">
    <table class="w-full text-left text-sm text-slate-400">
        <thead class="bg-slate-800 text-xs uppercase font-semibold text-slate-300">
            <tr>
                <th class="px-6 py-4 sticky left-0 z-20 bg-slate-800">รายการ (Item)</th>
                <th class="px-4 py-4 text-right">งบจัดสรร</th>
                <th class="px-4 py-4 text-right">ใช้ไป (Spent)</th>
                <th class="px-4 py-4 text-right">คงเหลือ</th>
                <th class="px-4 py-4 text-center">สถานะ</th>
                <th class="px-4 py-4 text-center">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-700 bg-slate-900/50">
            <!-- Row 1 -->
            <tr class="hover:bg-slate-800/50 transition-colors duration-150 group">
                <td class="px-6 py-4 font-medium text-slate-100 sticky left-0 bg-slate-900/50 group-hover:bg-slate-800/50 backdrop-blur-sm">
                    ค่าวัสดุสำนักงาน
                </td>
                <td class="px-4 py-4 text-right font-mono text-slate-200">100,000</td>
                <td class="px-4 py-4 text-right font-mono text-emerald-400">45,000</td>
                <td class="px-4 py-4 text-right font-mono text-slate-200">55,000</td>
                <td class="px-4 py-4 text-center">
                    <span class="badge badge-green">Normal</span>
                </td>
                <td class="px-4 py-4 text-center">
                    <button class="text-slate-400 hover:text-primary-400 transition-colors">
                        <i class="fas fa-edit"></i>
                    </button>
                </td>
            </tr>
        </tbody>
        <!-- Footer / Total -->
        <tfoot class="bg-slate-800 font-bold text-slate-100">
             <tr>
                <td class="px-6 py-4 sticky left-0 bg-slate-800">รวมทั้งสิ้น</td>
                <td class="px-4 py-4 text-right">100,000</td>
                <td class="px-4 py-4 text-right">45,000</td>
                <td class="px-4 py-4 text-right">55,000</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
</div>
```

## 4. Specific Recommendations for HR Budget

### 4.1 "Smart" Inputs for Budget Tracking
With `budget_trackings` needing multiple fields (Allocated, Transfer, PO, Spent), consider a **Matrix Input Form** or **Editable Table**.
- Instead of opening a modal for each item, allow inline editing for "Current Month Spending".
- Highlight changed cells before saving.

### 4.2 Hierarchy Visualization
The project has Categories and Items.
- Use **indentation** and **collapsible rows** in tables to show hierarchy.
- Parent rows (Categories) should calculate subtotals automatically.

### 4.3 Responsive Design
- On small screens, switch from `<table>` to Card View (`grid grid-cols-1`).
- Hide less important columns (e.g., created_at) on mobile.

## 5. Next Steps
1. Create a reusable `form-input` component (partial view).
2. Implement the updated Table CSS in `app.css` (augmenting existing `.table`).
3. Apply this design to the `Budget Tracking` page as a pilot.
