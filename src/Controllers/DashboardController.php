<?php
/**
 * Dashboard Controller
 * 
 * Main dashboard page - Uses LEGACY budgets table
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Core\Database;
use App\Models\FiscalYear;

class DashboardController
{
    /**
     * Show main dashboard (Legacy)
     */
    public function index(): void
    {
        Auth::require();

        // Get fiscal year from query parameter or use current
        $fiscalYear = (int) ($_GET['year'] ?? $this->getCurrentFiscalYear());

        // Get dashboard statistics from legacy budgets table
        $stats = $this->getDashboardStats($fiscalYear);
        $recentActivities = $this->getRecentActivities();
        $budgetByCategory = $this->getBudgetByCategory($fiscalYear);

        // Get fiscal years for dropdown
        $fiscalYears = FiscalYear::getForSelect();

        View::setLayout('main');
        View::render('dashboard/index', [
            'title' => 'ภาพรวมงบประมาณ',
            'currentPage' => 'dashboard',
            'stats' => $stats,
            'recentActivities' => $recentActivities,
            'budgetByCategory' => $budgetByCategory,
            'quarterlyData' => $this->getQuarterlyData($fiscalYear),
            'trend' => $this->getMonthlyTrendData($fiscalYear),
            'fiscalYear' => $fiscalYear,
            'fiscalYears' => $fiscalYears,
        ]);
    }

    /**
     * Get dashboard statistics from NEW budget_tracking table
     */
    private function getDashboardStats(int $fiscalYear): array
    {
        // Query from budget_trackings table which tracks Allocated, Transfer, Disbursed, Pending, PO
        // We sum EVERYTHING for the fiscal year to get the global picture
        $sql = "SELECT 
                    COALESCE(SUM(allocated), 0) as allocated,
                    COALESCE(SUM(transfer), 0) as transfer,
                    COALESCE(SUM(disbursed), 0) as disbursed,
                    COALESCE(SUM(pending), 0) as pending,
                    COALESCE(SUM(po), 0) as po
                FROM budget_trackings 
                WHERE fiscal_year = ?";
                
        $data = Database::queryOne($sql, [$fiscalYear]);

        $allocated = (float) ($data['allocated'] ?? 0);
        $transfer = (float) ($data['transfer'] ?? 0);
        $spent = (float) ($data['disbursed'] ?? 0);
        $pending = (float) ($data['pending'] ?? 0);
        $po = (float) ($data['po'] ?? 0);

        // Total Budget = Allocated + Transfer
        $totalBudget = $allocated + $transfer;
        
        // Total Used = Spent + Pending + PO (If we want to show 'Usage rate')
        // OR just Spent if we want 'Disbursement rate'
        // The user example splits 'Spent' (80) and 'Remaining' (20).
        // 100 (Allocated) - 80 (Spent) = 20 (Remaining)
        // User example includes Request/PO breakdown under 'Spent' in text, but calculation 'Remaining' 20 implies Remaining = Budget - Spent.
        // Wait, the user text: "เบิก 80, ขอ 0, PO 0". "รวมเบิกจ่าย 80".
        // If Requests were 10, would Remaining be 10?
        // Usually, Remaining = Budget - (Spent + PO + Pending).
        // Let's stick to the standard: Remaining = Budget - Spent - PO - Pending (Available).
        // BUT, looking at the user's specific text: "รวมเบิกจ่าย 80.00" (Total Spent) -> "เบิก 80, ขอ 0, PO 0".
        // And "คงเหลือ 20.00".
        // It seems Total Usage = Spent + Request + PO.
        
        $totalUsed = $spent + $pending + $po;
        $remaining = $totalBudget - $totalUsed;
        
        // Rate based on Total Used? Or just Spent?
        // User example: 100 allocated, 80 used. Rate 80%.
        $spentPercent = $totalBudget > 0 ? ($totalUsed / $totalBudget) * 100 : 0;

        return [
            'allocated' => $allocated,
            'transfer_in' => $transfer > 0 ? $transfer : 0, // Simplified for UI
            'transfer_out' => $transfer < 0 ? abs($transfer) : 0,
            'spent' => $totalUsed, // Show Total Usage as 'Spent' in the big card, or maybe separate? User label is "เบิกจ่ายแล้ว" (Disbursed). 
                                   // But user breakdown shows "รวมเบิกจ่าย" (Total Disbursed) which includes PO/Request in their text logic context?
                                   // Actually, "รวมเบิกจ่าย" usually means Total Disbursement.
                                   // Let's treat 'spent' key as 'Total Used' for the card to match the user's "80.00" expectation.
            'real_spent' => $spent,
            'pending' => $pending,
            'po' => $po,
            'remaining' => $remaining,
            'spent_percent' => round($spentPercent, 1),
            'pending_requests' => 0 // Legacy field, simplified
        ];
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities(int $limit = 5): array
    {
        return Database::query(
            "SELECT al.*, u.name as user_name 
             FROM activity_logs al
             LEFT JOIN users u ON al.user_id = u.id
             ORDER BY al.created_at DESC
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Get budget breakdown by Plan (formerly Category)
     */
    private function getBudgetByCategory(int $fiscalYear): array
    {
        return Database::query(
            "SELECT 
                pl.name_th as category_name,
                pl.code as category_code,
                COALESCE(SUM(bt.allocated + bt.transfer), 0) as allocated,
                COALESCE(SUM(bt.disbursed + bt.pending + bt.po), 0) as spent
             FROM plans pl
             LEFT JOIN projects p ON p.plan_id = pl.id
             LEFT JOIN activities a ON a.project_id = p.id
             LEFT JOIN disbursement_records dr ON dr.activity_id = a.id
             LEFT JOIN budget_trackings bt ON bt.disbursement_record_id = dr.id AND bt.fiscal_year = ?
             WHERE pl.fiscal_year = ? AND pl.is_active = 1
             GROUP BY pl.id, pl.name_th, pl.code
             ORDER BY pl.sort_order",
            [$fiscalYear, $fiscalYear]
        );
    }

    /**
     * Get budget data with quarterly breakdown by Plan
     */
    private function getQuarterlyData(int $fiscalYear): array
    {
        // Join plans -> projects -> activities -> records -> sessions/trackings
        return Database::query(
            "SELECT 
                pl.name_th as category_name,
                COALESCE(SUM(bt.allocated + bt.transfer), 0) as allocated,
                COALESCE(SUM(CASE 
                    WHEN ds.record_month IN (10,11,12) THEN (bt.disbursed + bt.pending + bt.po) 
                    ELSE 0 
                END), 0) as q1,
                COALESCE(SUM(CASE 
                    WHEN ds.record_month IN (1,2,3) THEN (bt.disbursed + bt.pending + bt.po)
                    ELSE 0 
                END), 0) as q2,
                COALESCE(SUM(CASE 
                    WHEN ds.record_month IN (4,5,6) THEN (bt.disbursed + bt.pending + bt.po)
                    ELSE 0 
                END), 0) as q3,
                COALESCE(SUM(CASE 
                    WHEN ds.record_month IN (7,8,9) THEN (bt.disbursed + bt.pending + bt.po)
                    ELSE 0 
                END), 0) as q4,
                COALESCE(SUM(bt.disbursed + bt.pending + bt.po), 0) as total_spent,
                COALESCE(SUM((bt.allocated + bt.transfer) - (bt.disbursed + bt.pending + bt.po)), 0) as remaining
             FROM plans pl
             LEFT JOIN projects p ON p.plan_id = pl.id
             LEFT JOIN activities a ON a.project_id = p.id
             LEFT JOIN disbursement_records dr ON dr.activity_id = a.id
             LEFT JOIN disbursement_sessions ds ON dr.session_id = ds.id
             LEFT JOIN budget_trackings bt ON bt.disbursement_record_id = dr.id AND bt.fiscal_year = ?
             WHERE pl.fiscal_year = ? AND pl.is_active = 1
             GROUP BY pl.id, pl.name_th
             ORDER BY pl.sort_order",
            [$fiscalYear, $fiscalYear]
        );
    }

    /**
     * Get monthly spending trend data for chart (12 months)
     */
    private function getMonthlyTrendData(int $fiscalYear): array
    {
        $monthlyData = Database::query(
            "SELECT 
                ds.record_month as month,
                SUM(bt.disbursed + bt.pending + bt.po) as total
             FROM budget_trackings bt
             JOIN disbursement_records dr ON bt.disbursement_record_id = dr.id
             JOIN disbursement_sessions ds ON dr.session_id = ds.id
             WHERE bt.fiscal_year = ? 
             GROUP BY ds.record_month
             ORDER BY ds.record_month",
            [$fiscalYear]
        );
        
        // Initialize 12 months array (Oct to Sep)
        $trend = array_fill(0, 12, 0);
        
        // Map data to fiscal year order (Oct=0, Nov=1, ..., Sep=11)
        foreach ($monthlyData as $row) {
            $month = (int)$row['month'];
            // Convert calendar month to fiscal month index
            $fiscalIndex = ($month >= 10) ? ($month - 10) : ($month + 2);
            $trend[$fiscalIndex] = (float)$row['total'];
        }
        
        return $trend;
    }

    /**
     * Get current fiscal year
     */
    private function getCurrentFiscalYear(): int
    {
        $config = require __DIR__ . '/../../config/app.php';
        return $config['fiscal_year']['current'] ?? 2568;
    }

    /**
     * Get monthly spending data for chart
     */
    public function getChartData(): void
    {
        Auth::require();

        $fiscalYear = $this->getCurrentFiscalYear();

        // Get monthly spending data
        $monthlyData = Database::query(
            "SELECT 
                MONTH(created_at) as month,
                SUM(amount) as total
             FROM budget_transactions
             WHERE transaction_type = 'expenditure'
               AND YEAR(created_at) = ?
             GROUP BY MONTH(created_at)
             ORDER BY month",
            [$fiscalYear - 543]
        );

        // Format for Chart.js
        $labels = ['ต.ค.', 'พ.ย.', 'ธ.ค.', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.'];
        $data = array_fill(0, 12, 0);

        foreach ($monthlyData as $row) {
            $monthIndex = ($row['month'] - 10 + 12) % 12;
            $data[$monthIndex] = (float) $row['total'];
        }

        header('Content-Type: application/json');
        echo json_encode([
            'labels' => $labels,
            'data' => $data
        ]);
    }
}
