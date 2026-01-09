# Research: Thai Text Encoding & Organization Filtering Issues

## Final Analysis (2025-12-31)

### CSV File: `docs/budget_structure2schema.csv`

**Encoding:** ✅ **UTF-8** (Thai text displays correctly when viewed directly)

**Structure:**
- Line 1: Headers (22 columns)
- Lines 2-111: Data rows

**Key Observation:**
| Row Range | กอง (Column 14) | กลุ่มงาน (Column 15) |
|-----------|-----------------|----------------------|
| 2-31      | กองบริหารทรัพยากรบุคคล | กลุ่มงานระบบข้อมูลบุคคลฯ |
| 32-110    | **EMPTY**       | **EMPTY**            |

---

## Root Cause #1: Encoding (Solved)

The CSV file is **already UTF-8**. The `fix_encoding()` function in `import_budget_csv.php` was incorrectly converting UTF-8 → TIS-620 → UTF-8, causing **double-encoding corruption**.

**Solution:** Remove or bypass the encoding conversion entirely since the source is already UTF-8.

---

## Root Cause #2: Organization Filtering (Critical)

### Problem
The Session is linked to `organization_id = 12` ("กองบริหารทรัพยากรบุคคล").

However, the filtering query:
```sql
SELECT DISTINCT activity_id 
FROM budget_line_items 
WHERE division_id = 12 AND fiscal_year = 2569
```
Returns **0 rows** because:

1. Only CSV rows 2-31 have "กอง" = "กองบริหารทรัพยากรบุคคล"
2. The import script stores this as `division_id`
3. But **rows 32-110 have NO Division** (empty column 14)
4. Those rows are stored with `division_id = NULL`

### Evidence from Debug Screenshot
- Organization ID 12: "กองบริหารทรัพยากรบุคคล" 
- Line Items for Org 12: **0**
- Total Line Items: **109**
- Budget Plans: **72**

The 109 line items exist, but NONE have `division_id = 12` because:
- Import uses `$divId` which is only set for rows with "กอง" column
- Most rows only have กระทรวง/กรม, so `division_id = NULL`

---

## Solution Options

### Option A: Change Filtering Logic
Filter by **Department** (กรม) instead of Division (กอง), since all rows have กรม = "สำนักงานปลัดกระทรวงยุติธรรม".

**Change in Controller:**
```php
// Instead of: WHERE division_id = ?
// Use: WHERE department_id = ?
```

### Option B: Disable Org Filtering for Tracking
Show all activities for the fiscal year, regardless of organization.

**Already implemented:** `$skipFiltering = true` when no items found.

### Option C: Fix CSV Data
Add "กอง" column values for all rows so they can be properly allocated.

---

## Recommended Action

**Short-term:** Option B (already active)
- Users see all activities, not filtered
- Works but not ideal

**Long-term:** Option A
- Update filter to use `department_id` instead of `division_id`
- Link sessions to departments, not divisions
- OR: Modify CSV to include Division for all rows

---

## Encoding Fix

**Current `fix_encoding()` function causes corruption.**

Replace with:
```php
function fix_encoding($str) {
    // CSV is already UTF-8, no conversion needed
    return $str;
}
```

Or simply remove all `fix_encoding()` calls and use raw `trim($row[x])`.

#### Solution
1. Determine actual CSV encoding via hex dump
2. If UTF-8: Remove conversion entirely
3. If TIS-620: Keep conversion but ensure it's applied correctly
4. Re-import data

---

### Filtering Issue

#### Why `allowedIds` is Empty

The filter logic queries:
```sql
SELECT DISTINCT activity_id 
FROM budget_line_items 
WHERE division_id = ? AND fiscal_year = ?
```

If this returns 0 rows, filtering is skipped.

#### Possible Causes

1. **No Line Items at All:** Import script might have failed silently
2. **Wrong `division_id`:** Session's org ID doesn't match imported org IDs
3. **Fiscal Year Mismatch:** Session FY 2569 but line items imported as 2568

**Test:**
```sql
SELECT COUNT(*) FROM budget_line_items WHERE fiscal_year = 2569;
SELECT DISTINCT division_id, COUNT(*) FROM budget_line_items GROUP BY division_id;
SELECT * FROM disbursement_sessions WHERE id = 10;
```

---

## Recommended Actions

### Step 1: Verify Import Status
Run diagnostic query to check if `budget_line_items` table was populated:
```sql
SELECT COUNT(*) as total FROM budget_line_items;
SELECT fiscal_year, COUNT(*) as c FROM budget_line_items GROUP BY fiscal_year;
```

### Step 2: Fix CSV Encoding Detection
Modify `import_budget_csv.php` to:
1. NOT convert if file is already UTF-8
2. Force TIS-620→UTF-8 conversion only if detected as TIS-620

```php
function detect_encoding($str) {
    // UTF-8 BOM check
    if (substr($str, 0, 3) === "\xEF\xBB\xBF") return 'UTF-8';
    
    // Check if valid UTF-8
    if (@preg_match('//u', $str) === 1) return 'UTF-8';
    
    // Assume TIS-620
    return 'TIS-620';
}

function fix_encoding($str, $source_encoding) {
    if ($source_encoding === 'UTF-8') return $str;
    return iconv('TIS-620', 'UTF-8//IGNORE', $str);
}
```

### Step 3: Link Session to Correct Organization
After re-import, the organization IDs might have changed. Run:
```sql
-- Find the org ID for "กองบริหารทรัพยากรบุคคล" that has line items
SELECT o.id, o.name_th, COUNT(bli.id) as c 
FROM organizations o 
JOIN budget_line_items bli ON o.id = bli.division_id 
WHERE o.name_th LIKE '%บริหารทรัพยากรบุคคล%'
GROUP BY o.id;

-- Update session to use correct org ID
UPDATE disbursement_sessions SET organization_id = [NEW_ID] WHERE id = 10;
```

---

## Next Steps

1. [ ] Run hex dump on CSV to determine encoding
2. [ ] Check `budget_line_items` count
3. [ ] Re-import with corrected encoding logic
4. [ ] Verify filtering works after import

---

## References

- [UTF-8 Mojibake](https://en.wikipedia.org/wiki/Mojibake#UTF-8)
- [TIS-620 Encoding](https://en.wikipedia.org/wiki/Thai_Industrial_Standard_620-2533)
- PHP `mb_detect_encoding()` limitations with Thai
