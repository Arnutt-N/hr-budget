# Budget Request Form - Technical Summary
**Date:** 2026-01-13 01:44  
**File:** `resources/views/requests/form.php`  
**Status:** ✅ Production Ready

---

## 1. Input Types & Edit Permissions

| Field | HTML Type | Editable | Condition |
|-------|-----------|----------|-----------|
| **จำนวน (คน)** | `text` + `inputmode="numeric"` | ✅ | Leaf items only |
| **ราคาต่อหน่วย** | `text` + `inputmode="decimal"` | ✅ | Leaf items only |
| **วงเงิน (บาท)** | `text` + `inputmode="decimal"` | ✅ | Leaf items only (auto-calculated) |
| **หมายเหตุ** | `text` | ✅ | Leaf items only |

**Disable Logic:**
- Items with `children[]` (Parent) → `disabled readonly`
- Items without children (Leaf) → Editable
- Parent inputs show `cursor: not-allowed` on hover

---

## 2. Calculation & Aggregation Logic

```
Leaf Item (Level N)
    ├── quantity × unit_price = amount
    └── Triggers: updateRow() → updateParentTotals() → updateTabTotals()

Parent Item (Level N-1)
    ├── sum(child amounts) = parent amount
    └── Aggregates: qty, price, amount from ALL direct children

Tab Total
    └── sum(ALL leaf item amounts in this tab)
```

**JavaScript Functions:**

| Function | Purpose |
|----------|---------|
| `updateRow(tr)` | qty × price = amount for single row |
| `updateParentTotals(parentId)` | Sum children amounts to parent |
| `updateAllParentTotals()` | Loop all leafs, call updateParentTotals |
| `updateTabTotals()` | Sum leaf amounts per tab, update footer |

---

## 3. Tab Totals Display

**Locations Updated:**
- `tab-summary[data-tab="{id}"]` → Summary bar in tab header
- `tab-total-badge[data-tab="{id}"]` → Badge on tab button
- `footer-qty-{id}` → tfoot quantity column
- `footer-amount-{id}` → tfoot amount column

**Calculation Source:** Only **Leaf Items** (`tr.item-row:not(.parent-row)`)

---

## 4. Buttons & Actions

| Button | Selector | Action |
|--------|----------|--------|
| **กลับ** | `<a>` in header | Navigate to `/requests` |
| **ล้างค่า** | `.btn-clear-form` | Clear all inputs in current tab (Modal confirm) |
| **บันทึก** | `.btn-save` | Prepare JSON + Submit form |
| **ยืนยัน** | `.btn-confirm-selection` | POST to `/requests/{id}/confirm` (Modal confirm) |
| **ยกเลิก** | `<a>` to `/revoke` | Visible only when status = confirmed |

---

## 5. Data Submission Flow

**Problem:** PHP `max_input_vars` limit (1000 default)

**Solution:** JSON serialization

```javascript
// prepareJSON() function
const data = {};
document.querySelectorAll('.item-row').forEach(row => {
    data[row.dataset.id] = {
        quantity: parseNumber(...),
        unit_price: parseNumber(...),
        amount: parseNumber(...),
        note: ...
    };
});
document.getElementById('items_json').value = JSON.stringify(data);
```

**Backend:** Receives `items_json`, decodes, saves to `budget_request_items`

---

## 6. Error Prevention Measures

| Issue | Prevention | Implementation |
|-------|------------|----------------|
| **Mouse Scroll on Input** | ✅ `e.preventDefault()` + blur | Wheel event listener with `passive: false` |
| **Negative Numbers** | ✅ `Math.max(0, value)` | In `getVal()` function |
| **Invalid Characters** | ✅ `inputmode="decimal/numeric"` | Mobile shows number keyboard |
| **Format Errors** | ✅ `parseNumber()` | Strips commas, returns 0 for NaN |
| **Display Format** | ✅ `formatOnBlur()` | Adds comma separators |
| **Data Loss on Tab Switch** | ✅ Auto-save | `prepareJSON()` called before tab change |
| **Disabled Input Confusion** | ✅ Visual cursor | `cursor: not-allowed` on parent inputs |

---

## 7. Tab Switching Behavior

**Data Persistence:**
- ✅ Data NOT lost when switching tabs (DOM remains)
- ✅ Auto-save `prepareJSON()` on tab switch
- ❌ Not saved to DB until "บันทึก" clicked

**UI Behavior:**
```javascript
// Tab click handler
1. Reset all tabs to inactive style
2. Hide all tab contents (.hidden class)
3. Set clicked tab to active style
4. Show target content
5. prepareJSON() // Auto-save
6. updateTabTotals() // Recalculate
```

---

## 8. UX Enhancements (2026-01-13)

| Enhancement | Description |
|-------------|-------------|
| **Cursor Disabled** | Parent row inputs show `not-allowed` cursor |
| **Auto-save on Tab Change** | `prepareJSON()` before switching |
| **Scroll Prevention** | Wheel event blocked on numeric inputs |
| **Negative Validation** | `Math.max(0, value)` in getVal() |

---

## 9. CSS Classes Reference

| Class | Purpose |
|-------|---------|
| `.item-row` | All table rows |
| `.parent-row` | Rows with children (disabled inputs) |
| `.inp-quantity` | Quantity input |
| `.inp-unit-price` | Unit price input |
| `.inp-amount` | Amount input |
| `.inp-parent-qty` | Disabled qty for parents |
| `.inp-parent-price` | Disabled price for parents |
| `.tab-content` | Tab content container |
| `.tab-btn` | Tab button |
| `.toggle-children` | Expand/collapse button |

---

## 10. Data Attributes

| Attribute | Element | Purpose |
|-----------|---------|---------|
| `data-id` | `tr.item-row` | Item ID |
| `data-parent` | `tr.item-row` | Parent item ID |
| `data-has-children` | `tr.item-row` | "1" or "0" |
| `data-category` | `tr.item-row` | Category/Tab ID |
| `data-expanded` | `.toggle-children` | "true" or "false" |
| `data-tab` | Various | Tab ID for totals |
| `data-tab-target` | `.tab-btn` | Target content ID |
