<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Model;

/**
 * ตัวคำนวณงบบุคลากร — อัตรากำลัง *ผลิต* ยอดลง budget_line_items (source='computed')
 * ตามสูตร PRPs/2026-08-09_personnel-budget-schema-design.md §สูตร
 *
 * แกนหน่วยงานของ budget_line_items = department_id (= organizations.id, org_type department)
 * คีย์เดิมของแถว computed = (fiscal_year, department_id, expense_item_id, source='computed')
 */
final class PersonnelBudgetService
{
    /** ei 2 = เงินเดือน (อัตราเดิม) — คนครองอัตรา */
    private const EI_SALARY_EXISTING = 2;
    /** ei 3 = เงินเดือน (อัตราใหม่) — อัตราว่างที่บรรจุกลางปี */
    private const EI_SALARY_NEW = 3;

    /**
     * คำนวณงบบุคลากรของปีงบ
     *
     * @param int  $fiscalYearId fiscal_years.id
     * @param bool $dryRun       true = คืนผลอย่างเดียว ไม่เขียน budget_line_items
     *
     * @return array{lines: array<string,float>, written?: int}
     *         lines key = "expense_item_id:organization_id"
     */
    public function compute(int $fiscalYearId, bool $dryRun = false): array
    {
        $fy = Database::queryOne("SELECT * FROM fiscal_years WHERE id = ?", [$fiscalYearId]);
        if ($fy === null) {
            return ['lines' => []];
        }

        $policy = Database::queryOne(
            "SELECT * FROM personnel_budget_policies WHERE fiscal_year_id = ? AND deleted_at IS NULL",
            [$fiscalYearId]
        );
        $referenceDate = $policy['reference_date'] ?? date('Y-m-d');
        $calcMode = $policy['calc_mode'] ?? 'prorate';
        $vacancyRule = $policy['vacancy_rule'] ?? null;

        $rounds = $this->fetchIncludedRounds($fiscalYearId);
        $incrementPct = $this->fetchIncrementPolicies($fiscalYearId);
        $progress = $this->fetchProgressByOrg($rounds);
        $scales = $this->fetchScales();
        $allowanceTypes = $this->fetchAllowanceTypes();
        $ratesByType = $this->fetchRates();

        // lines["ei:org"] = ยอดรวม
        $lines = [];

        $versions = $this->fetchEffectiveVersions($referenceDate);
        $entitlementsByPosition = $this->fetchEntitlements($referenceDate);
        foreach ($versions as $v) {
            // อัตราว่าง: นับเฉพาะ vacant_funded ที่ผ่านเกณฑ์สรรหาของปีนั้น
            if ($v['occupancy'] !== 'occupied') {
                if ($v['occupancy'] !== 'vacant_funded' || !$this->passesVacancyRule((int) $v['position_id'], $fiscalYearId, $vacancyRule)) {
                    continue;
                }
            }

            $months = $calcMode === 'snapshot' ? 12 : (int) $v['months_counted'];
            $ei = $v['occupancy'] === 'occupied' ? self::EI_SALARY_EXISTING : self::EI_SALARY_NEW;

            $salary = $this->projectedSalary($v, $rounds, $progress, $incrementPct, $scales);
            $lines = $this->addLine($lines, $ei, (int) $v['organization_id'], $salary * $months);

            // สิทธิ์เงินเพิ่มของตำแหน่งนี้ (แกนบัญชี: expense_type=1 เท่านั้น · นโยบายอัตราว่าง)
            foreach ($entitlementsByPosition[(int) $v['position_id']] ?? [] as $typeId) {
                $type = $allowanceTypes[$typeId] ?? null;
                if ($type === null || !(int) $type['is_active']) {
                    continue;
                }
                if ($v['occupancy'] !== 'occupied' && !(int) $type['vacant_eligible']) {
                    continue;
                }
                if ($type['expense_item_id'] === null) {
                    continue; // ไม่มีสะพาน = ไม่รู้จะลงรายการไหน
                }

                $rate = $this->rate($typeId, $v, $allowanceTypes, $ratesByType, $referenceDate);
                $lines = $this->addLine(
                    $lines,
                    (int) $type['expense_item_id'],
                    (int) $v['organization_id'],
                    $rate * $months
                );
            }
        }

        if ($dryRun) {
            return ['lines' => $lines];
        }

        $written = $this->writeLines($lines, (int) $fy['year']);
        return ['lines' => $lines, 'written' => $written];
    }

    /** @return array<int,array> key = allowance_type_id */
    private function fetchAllowanceTypes(): array
    {
        $types = [];
        foreach (Database::query("SELECT * FROM allowance_types WHERE deleted_at IS NULL") as $t) {
            $types[(int) $t['id']] = $t;
        }
        return $types;
    }

    /** @return array<int,array[]> key = allowance_type_id, เรียงใหม่สุดก่อน */
    private function fetchRates(): array
    {
        $rates = [];
        foreach (
            Database::query(
                "SELECT * FROM allowance_rates WHERE deleted_at IS NULL ORDER BY effective_from DESC"
            ) as $r
        ) {
            $rates[(int) $r['allowance_type_id']][] = $r;
        }
        return $rates;
    }

    /** @return array<int,array[]> key = round id */
    private function fetchIncludedRounds(int $fiscalYearId): array
    {
        $rounds = [];
        foreach (
            Database::query(
                "SELECT * FROM salary_raise_rounds
                 WHERE fiscal_year_id = ? AND include_in_budget = 1 AND deleted_at IS NULL",
                [$fiscalYearId]
            ) as $r
        ) {
            $rounds[(int) $r['id']] = $r;
        }
        return $rounds;
    }

    /** @return array<string,float> key = employee_category */
    private function fetchIncrementPolicies(int $fiscalYearId): array
    {
        $policies = [];
        foreach (
            Database::query(
                "SELECT * FROM salary_increment_policies WHERE fiscal_year_id = ? AND deleted_at IS NULL",
                [$fiscalYearId]
            ) as $p
        ) {
            $policies[$p['employee_category']] = (float) $p['max_percent'];
        }
        return $policies;
    }

    /** @return array<int,bool> key = organization_id ที่เลื่อนเสร็จแล้ว (รอบใดรอบหนึ่ง) */
    private function fetchProgressByOrg(array $rounds): array
    {
        if ($rounds === []) {
            return [];
        }
        $completed = [];
        $ids = implode(',', array_map('intval', array_keys($rounds)));
        foreach (
            Database::query(
                "SELECT DISTINCT organization_id FROM salary_raise_progress
                 WHERE round_id IN ($ids) AND status = 'completed' AND deleted_at IS NULL"
            ) as $row
        ) {
            $completed[(int) $row['organization_id']] = true;
        }
        return $completed;
    }

    /** @return array<string,float> key = "category|level" → max_amount ณ ไม่มีข้อมูลช่วงเวลา (แบบง่าย: แถวล่าสุดที่มีผล) */
    private function fetchScales(): array
    {
        $scales = [];
        foreach (Database::query("SELECT * FROM salary_scales WHERE deleted_at IS NULL") as $s) {
            $scales[$s['employee_category'] . '|' . $s['level_code']] = (float) $s['max_amount'];
        }
        return $scales;
    }

    /** @return array[] เวอร์ชันที่มีผล ณ referenceDate ของอัตราที่ยัง active+approved */
    private function fetchEffectiveVersions(string $referenceDate): array
    {
        return Database::query(
            "SELECT pv.*, p.employee_category
             FROM position_versions pv
             JOIN positions p ON p.id = pv.position_id AND p.deleted_at IS NULL
             WHERE pv.deleted_at IS NULL
               AND pv.effective_from <= :ref
               AND (pv.effective_to IS NULL OR pv.effective_to >= :ref)
               AND pv.lifecycle = 'active'
               AND pv.approval_status = 'approved'",
            ['ref' => $referenceDate]
        );
    }

    /**
     * projected_salary ตามสูตร: หน่วยเสร็จ=actual · ไม่เสร็จ=ประมาณ x% เพดานขั้นสูง
     * (อัตราว่างไม่มีคนให้เลื่อน — ใช้ฐานเดิม)
     */
    private function projectedSalary(
        array $v,
        array $rounds,
        array $progress,
        array $incrementPct,
        array $scales
    ): float {
        $base = (float) $v['base_salary'];

        if ($rounds === [] || $v['occupancy'] !== 'occupied') {
            return $base;
        }

        $orgId = (int) $v['organization_id'];
        if (isset($progress[$orgId])) {
            return $base; // actual — หน่วยนี้เลื่อนเสร็จแล้ว
        }

        $pct = $incrementPct[$v['employee_category']] ?? 0.0;
        $projected = $base * (1 + $pct / 100);

        $key = $v['employee_category'] . '|' . $v['level_code'];
        if ($v['level_code'] !== null && isset($scales[$key])) {
            $projected = min($projected, $scales[$key]); // เพดานขั้นสูง
        }

        return round($projected, 2);
    }

    /** @return array<int,int[]> key = position_id → รายการ allowance_type_id ที่มีสิทธิ์ ณ referenceDate */
    private function fetchEntitlements(string $referenceDate): array
    {
        $byPosition = [];
        foreach (
            Database::query(
                "SELECT position_id, allowance_type_id FROM position_allowances
                 WHERE deleted_at IS NULL
                   AND effective_from <= :ref
                   AND (effective_to IS NULL OR effective_to >= :ref)",
                ['ref' => $referenceDate]
            ) as $row
        ) {
            $byPosition[(int) $row['position_id']][] = (int) $row['allowance_type_id'];
        }
        return $byPosition;
    }

    /**
     * rate(a, p) ตามสูตร §สูตร:
     *   ไม่มีแถวอัตรา = 0 (ไม่มีสิทธิ์)
     *   derived → ค่าของ type ต้นทางในตำแหน่งเดียวกัน · ต้นทาง 0/ไม่มี → fallback → 0
     *   percent → base_salary × percent · ไม่งั้น → amount
     * (กราฟ derived ไร้วงจรถูกบังคับตอนบันทึกแล้ว — ที่นี่กันชั้น depth กันลูปข้อมูลเสีย)
     */
    private function rate(
        int $typeId,
        array $version,
        array $allowanceTypes,
        array $ratesByType,
        string $referenceDate,
        int $depth = 0
    ): float {
        if ($depth > 5) {
            return 0.0; // กันวงวนข้อมูลเสีย
        }

        $rate = $this->matchRate($typeId, $version, $ratesByType, $referenceDate);
        if ($rate === null) {
            return 0.0;
        }

        if ($rate['derives_from_type_id'] !== null) {
            $x = $this->rate((int) $rate['derives_from_type_id'], $version, $allowanceTypes, $ratesByType, $referenceDate, $depth + 1);
            if ($x > 0) {
                return $x;
            }
            return $rate['fallback_amount'] !== null ? (float) $rate['fallback_amount'] : 0.0;
        }

        if ($rate['percent'] !== null) {
            return round((float) $version['base_salary'] * (float) $rate['percent'] / 100, 2);
        }

        return $rate['amount'] !== null ? (float) $rate['amount'] : 0.0;
    }

    /** หาแถวอัตราที่ตรงระดับ/สาย และมีผล ณ วันอ้างอิง (เฉพาะเจาะจงก่อน — แถว NULL level/line ใช้รอง) */
    private function matchRate(int $typeId, array $version, array $ratesByType, string $referenceDate): ?array
    {
        $candidates = $ratesByType[$typeId] ?? [];
        $generic = null;
        foreach ($candidates as $r) {
            if ($r['effective_from'] > $referenceDate) {
                continue;
            }
            if ($r['effective_to'] !== null && $r['effective_to'] < $referenceDate) {
                continue;
            }

            $levelMatch = $r['level_code'] === null || $r['level_code'] === $version['level_code'];
            $lineMatch = $r['line_code'] === null || $r['line_code'] === $version['line_code'];
            if ($levelMatch && $lineMatch) {
                if ($r['level_code'] === null && $r['line_code'] === null) {
                    $generic = $r; // เก็บไว้เป็นตัวรอง
                } else {
                    return $r; // เฉพาะเจาะจงชนะ
                }
            }
        }
        return $generic;
    }

    /** อัตรานี้ผ่านเกณฑ์ vacancy_rule ของปีงบหรือไม่ (ไม่มีเกณฑ์ = ไม่นับเลย — ปลอดภัยกว่า) */
    private function passesVacancyRule(int $positionId, int $fiscalYearId, ?string $vacancyRule): bool
    {
        if ($vacancyRule === null) {
            return false;
        }
        $row = Database::queryOne(
            "SELECT id FROM vacancy_recruitment
             WHERE position_id = ? AND fiscal_year_id = ? AND type = ?
               AND deleted_at IS NULL",
            [$positionId, $fiscalYearId, $vacancyRule]
        );
        return $row !== null;
    }

    /** @param array<string,float> $lines */
    private function addLine(array $lines, int $expenseItemId, int $organizationId, float $amount): array
    {
        if ($amount == 0.0) {
            return $lines;
        }
        $key = $expenseItemId . ':' . $organizationId;
        $lines[$key] = round(($lines[$key] ?? 0.0) + $amount, 2);
        return $lines;
    }

    /** เขียนแถว computed แทนของเดิม (ลบเฉพาะ computed ของปี+แกนเดียวกัน) คืนจำนวนแถวที่เขียน */
    private function writeLines(array $lines, int $fiscalYear): int
    {
        Database::beginTransaction();
        try {
            Database::delete(
                'budget_line_items',
                "fiscal_year = ? AND source = 'computed' AND deleted_at IS NULL",
                [$fiscalYear]
            );

            $written = 0;
            foreach ($lines as $key => $amount) {
                [$ei, $org] = explode(':', $key);
                Database::insert('budget_line_items', [
                    'fiscal_year' => $fiscalYear,
                    'department_id' => (int) $org,
                    'expense_item_id' => (int) $ei,
                    'source' => 'computed',
                    'allocated_pba' => $amount,
                    'status' => 'active',
                ]);
                $written++;
            }

            Database::commit();
            return $written;
        } catch (\Throwable $e) {
            Database::rollback();
            throw $e;
        }
    }
}
