<?php
/**
 * Budget Execution Controller
 * 
 * Dimensional budget execution tracking - Uses fact_budget_execution table
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Models\BudgetExecution;
use App\Models\Organization;

class BudgetExecutionController
{
    /**
     * Show execution tracking dashboard (Using budget_trackings)
     */
    public function index(): void
    {
        Auth::require();

        // Get fiscal year from query parameter or default to 2569
        $fiscalYear = (int) ($_GET['year'] ?? 2569);

        // Get filters from query params
        $filters = [
            'org_id' => $_GET['org'] ?? null,
            'plan_name' => $_GET['plan'] ?? null,
            'search' => $_GET['search'] ?? null
        ];

        // 1. Get KPI Stats from budget_trackings
        $statsSql = "SELECT 
                        COALESCE(SUM(bt.allocated), 0) as total_allocated,
                        COALESCE(SUM(bt.transfer), 0) as total_transfer,
                        COALESCE(SUM(bt.disbursed), 0) as total_disbursed,
                        COALESCE(SUM(bt.pending), 0) as total_pending,
                        COALESCE(SUM(bt.po), 0) as total_po
                     FROM budget_trackings bt
                     INNER JOIN disbursement_records dr ON bt.disbursement_record_id = dr.id
                     INNER JOIN disbursement_sessions ds ON dr.session_id = ds.id
                     WHERE ds.fiscal_year = ?";
        $statsParams = [$fiscalYear];
        if ($filters['org_id']) {
            $statsSql .= " AND ds.organization_id = ?";
            $statsParams[] = $filters['org_id'];
        }
        $statsRow = \App\Core\Database::queryOne($statsSql, $statsParams);
        
        $totalAllocated = (float)($statsRow['total_allocated'] ?? 0);
        $totalTransfer = (float)($statsRow['total_transfer'] ?? 0);
        $totalDisbursed = (float)($statsRow['total_disbursed'] ?? 0);
        $totalPending = (float)($statsRow['total_pending'] ?? 0);
        $totalPo = (float)($statsRow['total_po'] ?? 0);
        $totalSpending = $totalDisbursed + $totalPo;
        $totalNet = $totalAllocated + $totalTransfer;
        $totalBalance = $totalNet - $totalSpending;
        $percentSpending = $totalNet > 0 ? ($totalSpending / $totalNet) * 100 : 0;
        
        $stats = [
            'total_allocated' => $totalAllocated,
            'total_budget_act' => $totalAllocated,
            'transfer_change_amount' => $totalTransfer,
            'total_transfer' => $totalTransfer,
            'total_disbursed' => $totalDisbursed,
            'total_pending' => $totalPending,
            'total_po' => $totalPo,
            'total_spending' => $totalSpending,
            'total_net' => $totalNet,
            'total_balance' => $totalBalance,
            'total_remaining' => $totalBalance,
            'percent_spending' => $percentSpending
        ];
        
        // 2. Get Detail Breakdown by Project -> Activity
        $dataSql = "SELECT 
                        p.id as project_id,
                        p.name_th as project_name,
                        a.id as activity_id,
                        a.name_th as activity_name,
                        MAX(o.name_th) as org_name,
                        COALESCE(SUM(bt.allocated), 0) as allocated,
                        COALESCE(SUM(bt.transfer), 0) as transfer,
                        COALESCE(SUM(bt.disbursed), 0) as disbursed,
                        COALESCE(SUM(bt.po), 0) as po,
                        COALESCE(SUM(bt.pending), 0) as pending,
                        COALESCE(SUM(CASE WHEN ds.record_month IN (10,11,12) THEN bt.disbursed ELSE 0 END), 0) as q1,
                        COALESCE(SUM(CASE WHEN ds.record_month IN (1,2,3) THEN bt.disbursed ELSE 0 END), 0) as q2,
                        COALESCE(SUM(CASE WHEN ds.record_month IN (4,5,6) THEN bt.disbursed ELSE 0 END), 0) as q3,
                        COALESCE(SUM(CASE WHEN ds.record_month IN (7,8,9) THEN bt.disbursed ELSE 0 END), 0) as q4
                    FROM budget_trackings bt
                    INNER JOIN disbursement_records dr ON bt.disbursement_record_id = dr.id
                    INNER JOIN disbursement_sessions ds ON dr.session_id = ds.id
                    INNER JOIN activities a ON dr.activity_id = a.id
                    INNER JOIN projects p ON a.project_id = p.id
                    INNER JOIN organizations o ON ds.organization_id = o.id
                    WHERE ds.fiscal_year = ?
                    GROUP BY p.id, a.id 
                    ORDER BY p.id, a.id";
                    
        $rawRows = \App\Core\Database::query($dataSql, [$fiscalYear]);
        
        // Group by Project
        $budgetData = [];
        foreach ($rawRows as $row) {
            $pid = $row['project_id'];
            if (!isset($budgetData[$pid])) {
                $budgetData[$pid] = [
                    'project_id' => $pid,
                    'item_name' => $row['project_name'],
                    'org_name' => $row['org_name'], // Taking first org, technically could be mixed
                    'allocated' => 0,
                    'transfer' => 0,
                    'total_spending_amount' => 0,
                    'balance_amount' => 0,
                    'q1' => 0, 'q2' => 0, 'q3' => 0, 'q4' => 0,
                    'activities' => []
                ];
            }
            
            // Calc Activity Values
            $alloc = (float)$row['allocated'];
            $trans = (float)$row['transfer'];
            $spent = (float)$row['disbursed'];
            $po = (float)$row['po'];
            $pending = (float)$row['pending'];
            $totalSpend = $spent + $po + $pending;
            $net = $alloc + $trans;
            $bal = $net - $totalSpend;
            
            // Add Activity
            $budgetData[$pid]['activities'][] = [
                'activity_name' => $row['activity_name'],
                'allocated' => $alloc,
                'transfer' => $trans,
                'spent' => $spent,
                'po' => $po,
                'total_spending' => $totalSpend,
                'net_budget' => $net,
                'balance' => $bal,
                'q1' => $row['q1'],
                'q2' => $row['q2'],
                'q3' => $row['q3'],
                'q4' => $row['q4']
            ];
            
            // Accumulate Project Totals
            $budgetData[$pid]['allocated'] += $alloc;
            $budgetData[$pid]['transfer'] += $trans;
            $budgetData[$pid]['total_spending_amount'] += $totalSpend;
            $budgetData[$pid]['balance_amount'] += $bal;
            $budgetData[$pid]['q1'] += $row['q1'];
            $budgetData[$pid]['q2'] += $row['q2'];
            $budgetData[$pid]['q3'] += $row['q3'];
            $budgetData[$pid]['q4'] += $row['q4'];
        }
        
        // Calculate KPI for each project
        foreach ($budgetData as &$proj) {
            $net = $proj['allocated'] + $proj['transfer'];
            $proj['percent_disburse_incl_po'] = $net > 0 ? ($proj['total_spending_amount'] / $net * 100) : 0;
            // $proj['item_name'] is already set
        }
        unset($proj); // Break ref

        // Sort by Allocated Amount DESC
        uasort($budgetData, fn($a, $b) => $b['allocated'] <=> $a['allocated']);

        // 3. Get Chart Data (Top 5 Items + Others)
        $chartItems = $budgetData;
        
        $topItems = array_slice($chartItems, 0, 5);
        $others = array_slice($chartItems, 5);
        $otherSum = array_reduce($others, fn($carry, $item) => 
            $carry + ($item['allocated'] ?? 0), 0
        );

        $catLabels = array_map(fn($item) => $item['item_name'] ?? 'Unknown', $topItems);
        $catValues = array_map(fn($item) => (float)($item['allocated'] ?? 0), $topItems);

        if ($otherSum > 0) {
            $catLabels[] = 'อื่นๆ';
            $catValues[] = $otherSum;
        }

        $categoryChartData = [
            'labels' => $catLabels,
            'values' => $catValues
        ];
        
        // 4. Get Organization Chart Data
        $orgDataSql = "SELECT 
                          o.name_th as name,
                          COALESCE(SUM(bt.allocated), 0) as allocated
                       FROM budget_trackings bt
                       INNER JOIN disbursement_records dr ON bt.disbursement_record_id = dr.id
                       INNER JOIN disbursement_sessions ds ON dr.session_id = ds.id
                       INNER JOIN organizations o ON ds.organization_id = o.id
                       WHERE ds.fiscal_year = ?
                       GROUP BY o.id
                       ORDER BY SUM(bt.allocated) DESC
                       LIMIT 6";
        $orgData = \App\Core\Database::query($orgDataSql, [$fiscalYear]);
        
        $orgChartData = [
            'labels' => array_column($orgData, 'name'),
            'values' => array_map(fn($r) => (float)$r['allocated'], $orgData)
        ];

        // 5. Get Filter Options
        $fiscalYears = [['fiscal_year' => 2569], ['fiscal_year' => 2568]];
        $organizations = Organization::all();
        $plans = [];

        View::setLayout('main');
        View::render('budgets/execution', [
            'title' => 'ผลการเบิกจ่ายงบประมาณ',
            'currentPage' => 'execution',
            'stats' => $stats,
            'budgetData' => $budgetData,
            'fiscalYear' => $fiscalYear,
            'fiscalYears' => array_map(fn($y) => ['value' => $y['fiscal_year'], 'label' => 'ปี ' . $y['fiscal_year']], $fiscalYears),
            'filters' => $filters,
            'organizations' => $organizations,
            'plans' => $plans,
            'categoryChartData' => $categoryChartData,
            'orgChartData' => $orgChartData
        ]);
    }

    /**
     * Export Execution Data to Excel
     */
    public function export(): void
    {
        Auth::require();
        
        $fiscalYear = (int) ($_GET['year'] ?? $this->getCurrentFiscalYear());
        $filters = [
            'org_id' => $_GET['org'] ?? null,
            'plan_name' => $_GET['plan'] ?? null,
            'search' => $_GET['search'] ?? null
        ];
        
        // Redirect to export script
        $queryParams = http_build_query(array_merge(['year' => $fiscalYear], array_filter($filters)));
        header('Location: ' . View::url('/export_execution.php?' . $queryParams));
        exit;
    }

    /**
     * Get current fiscal year
     */
    private function getCurrentFiscalYear(): int
    {
        $config = require __DIR__ . '/../../config/app.php';
        return $config['fiscal_year']['current'] ?? 2568;
    }
}
