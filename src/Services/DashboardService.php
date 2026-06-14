<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * Read-only dashboard aggregations for the SPA.
 *
 * Ported from the legacy web App\Controllers\DashboardController:
 *  - summary()            ← getDashboardStats()  (budget_trackings, BE year)
 *  - monthlyExpenditure() ← getChartData()       (budget_transactions, CE year)
 *
 * SQL is kept driver-portable (no MySQL-only YEAR()/MONTH()) so the service is
 * unit-testable against SQLite in-memory. Buddhist-era ↔ Common-era: the
 * budget_transactions.created_at timestamp is stored in CE, so a BE fiscal
 * year maps to the calendar year (fiscalYear - 543).
 */
final class DashboardService
{
    /** Thai month labels in fiscal-year order (Oct → Sep). */
    private const MONTH_LABELS = [
        'ต.ค.', 'พ.ย.', 'ธ.ค.', 'ม.ค.', 'ก.พ.', 'มี.ค.',
        'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.',
    ];

    private const BE_CE_OFFSET = 543;

    private const FISCAL_START_MONTH = 10; // October

    /**
     * Aggregate budget figures for one fiscal year (Buddhist era).
     *
     * @return array{
     *   fiscal_year:int, allocated:float, transfer:float, total_budget:float,
     *   disbursed:float, pending:float, po:float, total_used:float,
     *   remaining:float, used_percent:float
     * }
     */
    public function summary(int $fiscalYear): array
    {
        $row = Database::queryOne(
            "SELECT
                COALESCE(SUM(allocated), 0) AS allocated,
                COALESCE(SUM(transfer), 0)  AS transfer,
                COALESCE(SUM(disbursed), 0) AS disbursed,
                COALESCE(SUM(pending), 0)   AS pending,
                COALESCE(SUM(po), 0)        AS po
             FROM budget_trackings
             WHERE fiscal_year = ?",
            [$fiscalYear]
        ) ?? [];

        $allocated = (float) ($row['allocated'] ?? 0);
        $transfer  = (float) ($row['transfer'] ?? 0);
        $disbursed = (float) ($row['disbursed'] ?? 0);
        $pending   = (float) ($row['pending'] ?? 0);
        $po        = (float) ($row['po'] ?? 0);

        $totalBudget = $allocated + $transfer;
        $totalUsed   = $disbursed + $pending + $po;
        $remaining   = $totalBudget - $totalUsed;
        $usedPercent = $totalBudget > 0 ? ($totalUsed / $totalBudget) * 100 : 0.0;

        return [
            'fiscal_year'  => $fiscalYear,
            'allocated'    => $allocated,
            'transfer'     => $transfer,
            'total_budget' => $totalBudget,
            'disbursed'    => $disbursed,
            'pending'      => $pending,
            'po'           => $po,
            'total_used'   => $totalUsed,
            'remaining'    => $remaining,
            'used_percent' => round($usedPercent, 1),
        ];
    }

    /**
     * Monthly expenditure totals bucketed into 12 fiscal months (Oct → Sep).
     *
     * The legacy getChartData() filtered by calendar year (YEAR = fiscalYear-543),
     * which wrongly dropped Oct–Dec of the fiscal year. We instead scan the real
     * fiscal window: a BE fiscal year Y starts on Oct 1 of CE year (Y-543-1) and
     * ends Sep 30 of CE year (Y-543). Months are bucketed so October is the first
     * bar.
     *
     * @return array{labels: list<string>, data: list<float>}
     */
    public function monthlyExpenditure(int $fiscalYear): array
    {
        $ceYear = $fiscalYear - self::BE_CE_OFFSET;
        $start  = sprintf('%04d-10-01 00:00:00', $ceYear - 1); // fiscal year opens Oct 1 (prior CE year)
        $end    = sprintf('%04d-10-01 00:00:00', $ceYear);      // closes Sep 30 (exclusive next Oct 1)

        $rows = Database::query(
            "SELECT amount, created_at
             FROM budget_transactions
             WHERE transaction_type = 'expenditure'
               AND created_at >= ?
               AND created_at <  ?",
            [$start, $end]
        );

        $data = array_fill(0, 12, 0.0);
        foreach ($rows as $r) {
            $createdAt = (string) ($r['created_at'] ?? '');
            if (strlen($createdAt) < 7) {
                continue; // malformed / null timestamp
            }
            $month = (int) substr($createdAt, 5, 2); // 'YYYY-MM-DD ...'
            if ($month < 1 || $month > 12) {
                continue;
            }
            $index = ($month - self::FISCAL_START_MONTH + 12) % 12;
            $data[$index] += (float) ($r['amount'] ?? 0);
        }

        return [
            'labels' => self::MONTH_LABELS,
            'data'   => array_values($data),
        ];
    }
}
