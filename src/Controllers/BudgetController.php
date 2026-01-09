<?php
/**
 * Budget Controller
 * 
 * Handles budget dashboard, CRUD operations
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Core\Router;
use App\Models\Budget;
use App\Models\BudgetCategory;
use App\Models\FiscalYear;
use App\Models\BudgetRecord;
use App\Models\BudgetTracking;
use App\Models\BudgetExecution; // New Dimensional Model
use App\Models\Organization;    // New Dimension
use App\Models\ExpenseType;    // New
use App\Models\ExpenseGroup;   // New
use App\Models\ExpenseItem;    // New
use App\Models\Plan;           // New

class BudgetController
{
    /**
     * Budget Dashboard - now shows list view
     */
    public function dashboard(): void
    {
        // Redirect to same view as list
        $this->index();
    }

    /**
     * Budget List (using budget_trackings data)
     */
    public function index(): void
    {
        Auth::require();
        
        $fiscalYear = (int) ($_GET['year'] ?? 2569); // Default to 2569
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        // Get filters from query params
        $filters = [
            'org_id' => $_GET['org'] ?? null,
            'plan_name' => $_GET['plan'] ?? null,
            'search' => $_GET['search'] ?? null,
            'record_date' => $_GET['record_date'] ?? null,
        ];
        
        // Query from budget_trackings + disbursement_records
        $sql = "SELECT 
                    dr.id,
                    a.name_th as activity_name,
                    p.name_th as project_name,
                    pl.name_th as plan_name,
                    o.name_th as organization_name,
                    ds.record_date,
                    ds.record_month,
                    SUM(bt.allocated) as budget_allocated_amount,
                    SUM(bt.transfer) as transfer_change_amount,
                    SUM(bt.allocated + bt.transfer) as budget_net_balance,
                    SUM(bt.disbursed) as disbursed_amount,
                    SUM(bt.pending) as request_amount,
                    SUM(bt.po) as po_pending_amount,
                    SUM((bt.allocated + bt.transfer) - (bt.disbursed + bt.pending + bt.po)) as balance_amount
                FROM disbursement_records dr
                INNER JOIN disbursement_sessions ds ON dr.session_id = ds.id
                INNER JOIN activities a ON dr.activity_id = a.id
                INNER JOIN projects p ON a.project_id = p.id
                INNER JOIN plans pl ON p.plan_id = pl.id
                INNER JOIN organizations o ON ds.organization_id = o.id
                LEFT JOIN budget_trackings bt ON bt.disbursement_record_id = dr.id
                WHERE ds.fiscal_year = ?";
        
        $params = [$fiscalYear];
        
        if ($filters['org_id']) {
            $sql .= " AND ds.organization_id = ?";
            $params[] = $filters['org_id'];
        }
        
        $sql .= " GROUP BY dr.id ORDER BY ds.record_date DESC, dr.id DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        $budgets = \App\Core\Database::query($sql, $params);
        
        // Count total
        $countSql = "SELECT COUNT(DISTINCT dr.id) as cnt
                     FROM disbursement_records dr
                     INNER JOIN disbursement_sessions ds ON dr.session_id = ds.id
                     WHERE ds.fiscal_year = ?";
        $countParams = [$fiscalYear];
        if ($filters['org_id']) {
            $countSql .= " AND ds.organization_id = ?";
            $countParams[] = $filters['org_id'];
        }
        $totalRow = \App\Core\Database::queryOne($countSql, $countParams);
        $total = $totalRow['cnt'] ?? 0;
        $totalPages = (int) ceil($total / $perPage);
        
        // Get KPI stats
        $statsSql = "SELECT 
                        SUM(bt.allocated) as total_allocated,
                        SUM(bt.transfer) as transfer_change_amount,
                        SUM(bt.allocated + bt.transfer) as total_net_budget,
                        SUM(bt.disbursed) as total_disbursed,
                        SUM(bt.pending) as total_request,
                        SUM(bt.po) as total_po,
                        SUM(bt.disbursed + bt.pending + bt.po) as total_spending,
                        SUM((bt.allocated + bt.transfer) - (bt.disbursed + bt.pending + bt.po)) as total_balance
                     FROM budget_trackings bt
                     INNER JOIN disbursement_records dr ON bt.disbursement_record_id = dr.id
                     INNER JOIN disbursement_sessions ds ON dr.session_id = ds.id
                     INNER JOIN activities a ON dr.activity_id = a.id
                     INNER JOIN projects p ON a.project_id = p.id
                     INNER JOIN plans pl ON p.plan_id = pl.id
                     WHERE ds.fiscal_year = ?";
        $statsParams = [$fiscalYear];
        if ($filters['org_id']) {
            $statsSql .= " AND ds.organization_id = ?";
            $statsParams[] = $filters['org_id'];
        }
        $stats = \App\Core\Database::queryOne($statsSql, $statsParams) ?? [
            'total_allocated' => 0,
            'transfer_change_amount' => 0,
            'total_net_budget' => 0,
            'total_disbursed' => 0,
            'total_request' => 0,
            'total_po' => 0,
            'total_spending' => 0,
            'total_balance' => 0
        ];
        
        // Get organizations and plans for filter dropdowns
        $organizations = Organization::all();
        $plans = Plan::where('fiscal_year', $fiscalYear)
                    ->where('is_active', 1)
                    ->orderBy('sort_order', 'ASC')
                    ->get();
        
        // Fiscal years for dropdown
        $fiscalYears = FiscalYear::getForSelect();
        
        View::render('budgets/list', [
            'title' => 'รายการเบิกจ่ายงบประมาณ',
            'currentPage' => 'budgets',
            'fiscalYear' => $fiscalYear,
            'fiscalYears' => $fiscalYears,
            'budgets' => $budgets,
            'stats' => $stats,
            'filters' => $filters,
            'organizations' => $organizations,
            'plans' => $plans,
            'recordDates' => [],
            'auth' => Auth::user(),
            'pagination' => [
                'current' => $page,
                'total' => $totalPages,
                'perPage' => $perPage,
                'totalRecords' => $total,
            ],
        ], 'main');
    }

    /**
     * Create Budget Form (Redirects to Tracking Dashboard for Year)
     */
    public function create(): void
    {
        Auth::require();
        Auth::requirePermission('budgets.create');
        
        $fiscalYear = (int) ($_GET['year'] ?? FiscalYear::currentYear());
        
        // Reuse tracking logic
        $this->loadTrackingView($fiscalYear, 'create');
    }

    /**
     * Store New Budget (Legacy - Redirects to Tracking Save)
     * Kept for router compatibility, but UI now uses /tracking/save
     */
    public function store(): void
    {
        Router::redirect('/budgets/tracking');
    }

    /**
     * Edit Budget Form (Redirects to Tracking Dashboard for Year)
     */
    public function edit(int $id): void
    {
        Auth::require();
        Auth::requirePermission('budgets.edit');
        
        $budget = Budget::find($id);
        
        if (!$budget) {
            $_SESSION['flash_error'] = 'ไม่พบงบประมาณ';
            Router::redirect('/budgets/list');
            return;
        }
        
        $fiscalYear = (int) $budget['fiscal_year'];
        $this->loadTrackingView($fiscalYear, 'edit');
    }

    /**
     * Helper to load Tracking View
     */
    private function loadTrackingView(int $fiscalYear, string $mode): void
    {
        $categories = BudgetCategory::getAllWithItems();
        
        // Use Model instead of raw SQL
        $trackings = BudgetTracking::getByFiscalYearKeyed($fiscalYear);

        $fiscalYears = FiscalYear::getForSelect();
        
        View::render('budgets/tracking', [
            'mode' => $mode,
            'title' => 'Smart Budget Tracking',
            'currentPage' => 'budgets',
            'fiscalYear' => $fiscalYear,
            'categories' => $categories,
            'trackings' => $trackings,
            'fiscalYears' => $fiscalYears,
            'auth' => Auth::user(),
        ], 'main');
    }

    /**
     * Update Budget
     */
    public function update(int $id): void
    {
        Auth::require();
        Auth::requirePermission('budgets.edit');
        
        // Check if exists
        $budget = Budget::find($id);
        if (!$budget) {
            $_SESSION['flash_error'] = 'ไม่พบงบประมาณ';
            Router::redirect('/budgets/list');
            return;
        }
        
        // Validate input
        $errors = $this->validate($_POST);
        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode(', ', $errors);
            Router::redirect("/budgets/{$id}/edit");
            return;
        }
        
        // Update budget
        Budget::update($id, [
            'category_id' => (int) $_POST['category_id'],
            'fiscal_year' => (int) $_POST['fiscal_year'],
            'allocated_amount' => (float) $_POST['allocated_amount'],
            'spent_amount' => (float) ($_POST['spent_amount'] ?? 0),
            'target_amount' => (float) ($_POST['target_amount'] ?? 0),
            'transfer_in' => (float) ($_POST['transfer_in'] ?? 0),
            'transfer_out' => (float) ($_POST['transfer_out'] ?? 0),
            'status' => $_POST['status'] ?? 'draft',
            'notes' => $_POST['notes'] ?? null,
            'updated_by' => Auth::id(),
        ]);

        // Create NEW record for history tracking
        BudgetRecord::create([
            'budget_id' => $id,
            'record_date' => $_POST['record_date'] ?? date('Y-m-d'),
            'record_period' => $_POST['record_period'] ?? 'beginning',
            'transfer_allocation' => (float) ($_POST['transfer_allocation'] ?? 0),
            'spent_amount' => (float) ($_POST['spent_amount'] ?? 0),
            'request_amount' => (float) ($_POST['request_amount'] ?? 0),
            'po_amount' => (float) ($_POST['po_amount'] ?? 0),
            'notes' => $_POST['record_notes'] ?? null,
            'created_by' => Auth::id()
        ]);
        
        $_SESSION['flash_success'] = 'แก้ไขงบประมาณสำเร็จ';
        Router::redirect('/budgets/list');
    }

    /**
     * Delete Budget
     */
    public function destroy(int $id): void
    {
        Auth::require();
        Auth::requirePermission('budgets.delete');
        
        $budget = Budget::find($id);
        
        if (!$budget) {
            $_SESSION['flash_error'] = 'ไม่พบงบประมาณ';
            Router::redirect('/budgets/list');
            return;
        }
        
        Budget::delete($id);
        
        $_SESSION['flash_success'] = 'ลบงบประมาณสำเร็จ';
        Router::redirect('/budgets/list');
    }

    /**
     * Smart Budget Tracking Dashboard (Multi-Tab)
     */
    /**
     * Records List Page (Disbursement Tracking)
     */
    public function trackingIndex()
    {
        Auth::require();
        $user = Auth::user();
        $userId = Auth::id();
        $orgId = $user['organization_id'] ?? 0;
        
        // Check if user is admin
        $isAdmin = Auth::hasRole('admin') || Auth::hasRole('super_admin') || Auth::can('admin.*');
        
        // Get fiscal year filter
        $fiscalYear = (int) ($_GET['year'] ?? 2569);
        
        // Build query based on user role
        $sql = "SELECT ds.*, 
                o.name_th as organization_name,
                (SELECT COUNT(*) FROM disbursement_records dr WHERE dr.session_id = ds.id) as record_count,
                (SELECT SUM(bt.allocated) FROM budget_trackings bt 
                 JOIN disbursement_records dr ON bt.disbursement_record_id = dr.id 
                 WHERE dr.session_id = ds.id) as total_allocated,
                (SELECT SUM(bt.disbursed) FROM budget_trackings bt 
                 JOIN disbursement_records dr ON bt.disbursement_record_id = dr.id 
                 WHERE dr.session_id = ds.id) as total_disbursed
                FROM disbursement_sessions ds
                LEFT JOIN organizations o ON ds.organization_id = o.id
                WHERE ds.fiscal_year = ?";
        
        $params = [$fiscalYear];
        
        if (!$isAdmin && $orgId > 0) {
            $sql .= " AND ds.organization_id = ?";
            $params[] = $orgId;
        }
        
        $sql .= " ORDER BY ds.record_date DESC, ds.record_month DESC";
                
        $sessions = \App\Core\Database::query($sql, $params);
        
        // Get fiscal years for the create modal (simple format - year number only)
        $fiscalYears = FiscalYear::getForSelectSimple();
        
        // Check if user is admin - if so, provide organizations list
    $isAdmin = Auth::hasRole('admin') || Auth::hasRole('super_admin') || Auth::can('admin.*');
    $organizations = [];
    $departments = [];
    $divisions = [];
    
    if ($isAdmin) {
        $organizations = Organization::all();
        
        /*
         * Organization Hierarchy Classification
         * ======================================
         * โครงสร้าง 3 ระดับ:
         *   1. กระทรวง (Ministry)   - parent_id = NULL
         *   2. กรม (Department)     - parent_id ชี้ไปหากระทรวง
         *   3. กอง (Division)       - parent_id ชี้ไปหากรม
         * 
         * เมื่อเพิ่มข้อมูลใหม่ในอนาคต:
         *   - กรมใหม่: ต้องตั้ง parent_id ให้ชี้ไปหากระทรวง
         *   - กองใหม่: ต้องตั้ง parent_id ให้ชี้ไปหากรม
         */
        
        // Step 1: Identify ministries (top-level, no parent)
        $ministryIds = [];
        foreach ($organizations as $org) {
            if (empty($org['parent_id'])) {
                $ministryIds[] = $org['id'];
            }
        }
        
        // Step 2: Identify departments (parent is ministry)
        $departmentIds = [];
        foreach ($organizations as $org) {
            $parentId = $org['parent_id'] ?? null;
            if (!empty($parentId) && in_array($parentId, $ministryIds)) {
                $departmentIds[] = $org['id'];
                $departments[] = $org;
            }
        }
        
        // Step 3: Identify divisions (parent is department)
        foreach ($organizations as $org) {
            $parentId = $org['parent_id'] ?? null;
            if (!empty($parentId) && in_array($parentId, $departmentIds)) {
                $divisions[] = $org;
            }
        }
        
        // Note: Any org at level 4+ (e.g., กลุ่มงาน) would need similar logic if needed
    }
    
    // Get user's organization name for non-admin view
    $userOrgName = '';
    if ($orgId && !$isAdmin) {
        $userOrg = Organization::find($orgId);
        $userOrgName = $userOrg['name_th'] ?? $userOrg['name'] ?? 'หน่วยงานของคุณ';
    }
    
    View::render('budgets/tracking/index', [
        'title' => 'รายการบันทึกการเบิกจ่าย',
        'currentPage' => 'budgets',
        'sessions' => $sessions,
        'fiscalYear' => $fiscalYear,
        'fiscalYears' => $fiscalYears,
        'organizations' => $organizations,
        'departments' => $departments,
        'divisions' => $divisions,
        'isAdmin' => $isAdmin,
        'userOrgId' => $orgId,
        'userOrgName' => $userOrgName
    ], 'main');
    }

    /**
     * Tracking Route - Disbursement Records List
     */
    public function tracking(): void
    {
        $this->trackingIndex();
    }

    public function trackingCreate()
    {
        Auth::require();
        $fiscalYears = FiscalYear::getForSelect();
        View::render('budgets/tracking/create', [
            'title' => 'สร้างรายการเบิกจ่ายใหม่',
            'currentPage' => 'budgets',
            'fiscalYears' => $fiscalYears
        ], 'main');
    }
    
    public function storeSession()
    {
        Auth::require();
        $user = Auth::user();
        $userId = Auth::id();
        
        // Check if user is admin and submitted organization_id
        $isAdmin = Auth::hasRole('admin') || Auth::hasRole('super_admin') || Auth::can('admin.*');
        
        if ($isAdmin && !empty($_POST['organization_id'])) {
            // Admin selected a specific organization
            $orgId = (int) $_POST['organization_id'];
        } else {
            // Use user's organization
            $orgId = $user['organization_id'] ?? 0;
            
            // If user has no organization, try to get the first one
            if (!$orgId || $orgId == 0) {
                $firstOrg = \App\Core\Database::queryOne("SELECT id FROM organizations ORDER BY id LIMIT 1");
                if ($firstOrg) {
                    $orgId = $firstOrg['id'];
                } else {
                    $_SESSION['flash_error'] = 'ไม่พบข้อมูลหน่วยงาน กรุณาติดต่อผู้ดูแลระบบ';
                    header("Location: " . View::url("/budgets/tracking"));
                    exit;
                }
            }
        }
        
        $year = (int) $_POST['fiscal_year'];
        $month = (int) $_POST['month'];
        
        // Check existing session
        $sql = "SELECT id FROM disbursement_sessions 
                WHERE organization_id = ? AND fiscal_year = ? AND record_month = ?";
        $existing = \App\Core\Database::queryOne($sql, [$orgId, $year, $month]);
        
        if ($existing) {
            $sessionId = $existing['id'];
        } else {
            // Create new
            $sql = "INSERT INTO disbursement_sessions (organization_id, fiscal_year, record_month, record_date, created_by)
                    VALUES (?, ?, ?, CURDATE(), ?)";
            \App\Core\Database::query($sql, [$orgId, $year, $month, $userId]);
            $sessionId = \App\Core\Database::lastInsertId();
        }
        
        header("Location: " . View::url("/budgets/tracking/activities?session_id=" . $sessionId));
        exit;
    }
    
    /**
     * Delete disbursement session
     */
    public function deleteSession($id)
    {
        Auth::require();
        $user = Auth::user();
        $isAdmin = Auth::hasRole('admin') || Auth::hasRole('super_admin') || Auth::can('admin.*');
        
        $db = \App\Core\Database::getInstance();
        
        // Find session using prepared statement
        $stmt = $db->prepare("SELECT * FROM disbursement_sessions WHERE id = ?");
        $stmt->execute([$id]);
        $session = $stmt->fetch();
        
        if (!$session) {
            header('Location: ' . View::url('/budgets/tracking?error=Not found'));
            exit;
        }
        
        // Check ownership (if not admin)
        if (!$isAdmin && $session['organization_id'] != $user['organization_id']) {
            header('Location: ' . View::url('/budgets/tracking?error=Unauthorized'));
            exit;
        }
        
        try {
            $db->beginTransaction();
            
            // Delete budget trackings (via disbursement_records)
            $stmt = $db->prepare("DELETE bt FROM budget_trackings bt 
                       INNER JOIN disbursement_records dr ON bt.disbursement_record_id = dr.id 
                       WHERE dr.session_id = ?");
            $stmt->execute([$id]);
                       
            // Delete disbursement records
            $stmt = $db->prepare("DELETE FROM disbursement_records WHERE session_id = ?");
            $stmt->execute([$id]);
            
            // Delete session
            $stmt = $db->prepare("DELETE FROM disbursement_sessions WHERE id = ?");
            $stmt->execute([$id]);
            
            $db->commit();
            
            header('Location: ' . View::url('/budgets/tracking?success=Deleted successfully'));
            exit;
            
        } catch (\Exception $e) {
            $db->rollBack();
            header('Location: ' . View::url('/budgets/tracking?error=' . urlencode($e->getMessage())));
            exit;
        }
    }

    public function activities()
    {
        Auth::require();
        $sessionId = (int) ($_GET['session_id'] ?? 0);
        
        // Get Session
        $session = \App\Core\Database::queryOne("SELECT * FROM disbursement_sessions WHERE id = ?", [$sessionId]);
        if (!$session) {
            header("Location: " . View::url("/budgets/tracking"));
            exit;
        }
        
        // Context Info
        $orgId = $session['organization_id'] ?? 0;
        $fiscalYear = $session['fiscal_year'];
        
        $tree = [];
        
        try {
            // 1. Get "Official" activity IDs from source_of_truth_mappings
            // This table is populated by the Python ETL layer (Data Cleaning)
            $allowedIds = [];
            if ($orgId) {
                $checkSql = "SELECT activity_id 
                             FROM source_of_truth_mappings 
                             WHERE organization_id = ? AND fiscal_year = ? AND is_official = 1";
                $rows = \App\Core\Database::query($checkSql, [$orgId, $fiscalYear]);
                $allowedIds = array_column($rows, 'activity_id');
            }

            // 2. Fetch Dimensional Data
            // Level 1: Plans
            $plans = Plan::getByFiscalYear($fiscalYear);
            
            // Level 2: Projects
            $projects = \App\Models\Project::where('fiscal_year', $fiscalYear)->get();
            
            // Level 3: Activities (with session record status)
            $sqlAct = "SELECT a.*, dr.id as record_id, dr.status as record_status 
                       FROM activities a 
                       LEFT JOIN disbursement_records dr ON dr.activity_id = a.id AND dr.session_id = ? 
                       WHERE a.fiscal_year = ? 
                       ORDER BY a.sort_order, a.id";
            $activities = \App\Core\Database::query($sqlAct, [$sessionId, $fiscalYear]);

            // 3. Build Hierarchy Tree
            $planMap = [];
            $projectMap = [];

            // 3.1 Setup Plans
            foreach ($plans as $p) {
                $p['type'] = 'program'; 
                $p['children'] = [];
                $planMap[$p['id']] = $p;
            }

            // 3.2 Attach Projects to Plans
            foreach ($projects as $pj) {
                $pj['type'] = 'project';
                $pj['children'] = [];
                $projectMap[$pj['id']] = $pj;
                
                if (isset($planMap[$pj['plan_id']])) {
                    $planMap[$pj['plan_id']]['children'][] = &$projectMap[$pj['id']];
                }
            }

            // 3.3 Attach Activities to Projects (Filtering by source_of_truth_mappings)
            foreach ($activities as $act) {
                // If orgId is set, filter by allowed IDs. If not (Admin), show everything.
                if ($orgId && !in_array($act['id'], $allowedIds)) {
                    continue;
                }
                
                $act['type'] = 'activity';
                if (isset($projectMap[$act['project_id']])) {
                    $projectMap[$act['project_id']]['children'][] = $act;
                }
            }

            // 4. Final Cleanup: Remove empty branches (Plans/Projects with no activities)
            if ($orgId) {
                $tree = [];
                foreach ($planMap as $planId => &$plan) {
                    $filteredProjects = [];
                    if (!empty($plan['children'])) {
                        foreach ($plan['children'] as $project) {
                            $projId = $project['id'];
                            if (isset($projectMap[$projId]) && !empty($projectMap[$projId]['children'])) {
                                $filteredProjects[] = $projectMap[$projId];
                            }
                        }
                    }
                    
                    if (!empty($filteredProjects)) {
                        $plan['children'] = $filteredProjects;
                        $tree[] = $plan;
                    }
                }
            } else {
                $tree = array_values($planMap);
            }
            
        } catch (\PDOException $e) {
            error_log("Activities tree build failed: " . $e->getMessage());
        }
        
        // Query expense type status for each record
        // Only show expense types that have budget allocations for this organization
        $expenseTypeStatusSql = "SELECT DISTINCT 
                                        dr.id as record_id, 
                                        et.id as expense_type_id, 
                                        et.name_th as expense_type_name,
                                        COUNT(bt.id) > 0 as has_data
                                 FROM disbursement_records dr
                                 CROSS JOIN expense_types et
                                 INNER JOIN budget_line_items bli ON bli.division_id = ? AND bli.expense_type_id = et.id
                                 LEFT JOIN budget_trackings bt ON bt.disbursement_record_id = dr.id AND bt.expense_type_id = et.id
                                 WHERE dr.session_id = ? AND et.is_active = 1
                                 GROUP BY dr.id, et.id
                                 ORDER BY et.sort_order";
        $expenseTypeStatusRows = \App\Core\Database::query($expenseTypeStatusSql, [$orgId, $sessionId]);
        
        // Organize by record ID
        $recordExpenseTypes = [];
        foreach ($expenseTypeStatusRows as $row) {
            if (!isset($recordExpenseTypes[$row['record_id']])) {
                $recordExpenseTypes[$row['record_id']] = [];
            }
            $recordExpenseTypes[$row['record_id']][] = [
                'id' => $row['expense_type_id'],
                'name' => $row['expense_type_name'],
                'has_data' => (bool)$row['has_data']
            ];
        }
        
        View::render('budgets/tracking/activities', [
            'title' => 'เลือกกิจกรรมที่ต้องบันทึก',
            'currentPage' => 'budgets',
            'session' => $session,
            'tree' => $tree,
            'orgId' => $orgId,
            'recordExpenseTypes' => $recordExpenseTypes
        ], 'main');
    }

    public function createRecord()
    {
        Auth::require();
        $sessionId = $_POST['session_id'];
        $activityId = $_POST['activity_id'];
        
        $db = \App\Core\Database::getInstance();
        
        // Check existing
        $sql = "SELECT id FROM disbursement_records WHERE session_id = ? AND activity_id = ?";
        $existing = \App\Core\Database::queryOne($sql, [$sessionId, $activityId]);
        
        if ($existing) {
            $recordId = $existing['id'];
        } else {
            \App\Core\Database::query("INSERT INTO disbursement_records (session_id, activity_id) VALUES (?, ?)", [$sessionId, $activityId]);
            $recordId = \App\Core\Database::lastInsertId();
        }
        
        header("Location: " . BASE_URL . "/budgets/tracking/{$recordId}/form");
        exit;
    }

    public function disbursementForm($id)
    {
        Auth::require();
        $db = \App\Core\Database::getInstance();
        
        // Fetch Record & Context
        $sql = "SELECT dr.*, ds.fiscal_year, ds.record_month, ds.record_date, ds.organization_id,
                       a.name_th as activity_name,
                       p.name_th as project_name,
                       pl.name_th as plan_name
                FROM disbursement_records dr
                JOIN disbursement_sessions ds ON dr.session_id = ds.id
                JOIN activities a ON dr.activity_id = a.id
                JOIN projects p ON a.project_id = p.id
                JOIN plans pl ON p.plan_id = pl.id
                WHERE dr.id = ?";
        $record = \App\Core\Database::queryOne($sql, [$id]);
        
        if (!$record) {
            echo "Record not found"; exit;
        }

        $typeId = (int) ($_GET['type_id'] ?? 1); // Default to first type
        $expenseType = ExpenseType::find($typeId);
        
        // Use organization_id from session to filter pertinent items
        $organizationId = $record['organization_id'] ?? null;
        $groups = ExpenseGroup::getAllWithItemsByType($typeId, $organizationId);

        // Fetch Trackings
        $trackings = \App\Core\Database::query("SELECT * FROM budget_trackings WHERE disbursement_record_id = ?", [$id]);
        $trackingMap = [];
        foreach ($trackings as $t) {
            $trackingMap[$t['expense_item_id']] = $t;
        }
        
        // Tabs
        $tabs = ExpenseType::where('is_active', 1)->orderBy('sort_order', 'ASC')->get();
        
        // Query saved status per expense type for this record
        $tabStatusSql = "SELECT expense_type_id, COUNT(*) as cnt 
                         FROM budget_trackings 
                         WHERE disbursement_record_id = ? 
                         GROUP BY expense_type_id";
        $tabStatusRows = \App\Core\Database::query($tabStatusSql, [$id]);
        $tabStatus = array_column($tabStatusRows, 'cnt', 'expense_type_id');

        View::render('budgets/tracking/form', [
            'title' => 'บันทึกเบิกจ่าย',
            'currentPage' => 'budgets',
            'record' => $record,
            'expenseType' => $expenseType,
            'groups' => $groups,
            'trackings' => $trackingMap,
            'tabs' => $tabs,
            'activeTypeId' => $typeId,
            'tabStatus' => $tabStatus
        ], 'main');
    }
    
    public function saveDisbursement($id)
    {
        Auth::require();
        $recordId = (int) $id;
        
        // Get raw POST data for budget items
        // Expected format: items[expense_item_id][field] = value
        $items = $_POST['items'] ?? [];
        
        if (empty($items)) {
            // No items to save
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                 echo json_encode(['success' => true, 'message' => 'No changes']);
                 exit;
            }
            header("Location: " . BASE_URL . "/budgets/tracking/{$recordId}/form");
            exit;
        }
        
        $db = \App\Core\Database::getInstance();
        
        // Fetch Record to get context
        $record = \App\Core\Database::queryOne("SELECT * FROM disbursement_records dr 
                                 JOIN disbursement_sessions ds ON dr.session_id = ds.id 
                                 WHERE dr.id = ?", [$recordId]);
        if (!$record) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Record not found']);
            exit;
        }
        
        $fiscalYear = $record['fiscal_year'];
        $orgId = $record['organization_id'];
        $activityId = $record['activity_id'];
        $recordMonth = $record['record_month'];
        
        \App\Core\Database::beginTransaction();
        try {
            foreach ($items as $itemId => $data) {
                // Determine expense ids
                // We reuse logic from BudgetTracking model but simpler
                $itemQuery = "SELECT expense_group_id, expense_type_id FROM expense_items WHERE id = ?";
                $itemInfo = \App\Core\Database::queryOne($itemQuery, [$itemId]);
                
                if (!$itemInfo) continue;
                
                // Upsert into budget_trackings with disbursement_record_id
                $sql = "INSERT INTO budget_trackings 
                        (disbursement_record_id, fiscal_year, organization_id, activity_id, 
                         expense_item_id, expense_group_id, expense_type_id,
                         record_month, allocated, transfer, disbursed, po, pending) 
                        VALUES (:rid, :year, :org, :act, :item, :grp, :typ, :mon, :alloc, :trans, :disb, :po, :pend)
                        ON DUPLICATE KEY UPDATE
                        allocated = VALUES(allocated),
                        transfer = VALUES(transfer),
                        disbursed = VALUES(disbursed),
                        po = VALUES(po),
                        pending = VALUES(pending)";
                        
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':rid' => $recordId,
                    ':year' => $fiscalYear,
                    ':org' => $orgId,
                    ':act' => $activityId,
                    ':item' => $itemId,
                    ':grp' => $itemInfo['expense_group_id'],
                    ':typ' => $itemInfo['expense_type_id'],
                    ':mon' => $recordMonth,
                    ':alloc' => (float)($data['allocated'] ?? 0),
                    ':trans' => (float)($data['transfer'] ?? 0),
                    ':disb' => (float)($data['disbursed'] ?? 0),
                    ':po' => (float)($data['po'] ?? 0),
                    ':pend' => (float)($data['pending'] ?? 0)
                ]);
            }
            
            // Mark Record as Completed
            \App\Core\Database::query("UPDATE disbursement_records SET status = 'completed', updated_at = NOW() WHERE id = ?", [$recordId]);
            
            \App\Core\Database::commit();
            
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                 echo json_encode(['success' => true]);
                 exit;
            }
            
            // Redirect back to Activities List (Part 1)
            $sessionId = $record['session_id'];
            header("Location: " . BASE_URL . "/budgets/tracking/activities?session_id={$sessionId}");
            exit;
            
        } catch (\Exception $e) {
            \App\Core\Database::rollback();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    /**
     * AJAX: Get Content for a Tab
     */
    public function getTrackingTab(): void
    {
        Auth::require();

        $fiscalYear = (int) ($_GET['year'] ?? FiscalYear::currentYear());
        $typeId = (int) $_GET['type_id']; // Changed from category_id
        $orgId = !empty($_GET['org_id']) ? (int) $_GET['org_id'] : null;

        // Get Expense Type info
        $expenseType = ExpenseType::find($typeId);
        
        // Get all groups with items for this type
        $groups = ExpenseGroup::getAllWithItemsByType($typeId);
        
        // Get Tracking Data for this year & org
        $trackings = BudgetTracking::getByFiscalYearKeyed($fiscalYear, $orgId);
        
        // Render Partial
        View::render('budgets/partials/tracking_tab', [
            'expenseType' => $expenseType,
            'groups' => $groups,
            'trackings' => $trackings,
            'fiscalYear' => $fiscalYear,
            'orgId' => $orgId
        ]);
        exit;
    }

    private function extractBranch($allCats, $rootId, &$result) {
        foreach ($allCats as $cat) {
            if ($cat['id'] == $rootId) {
                // Found the root, add it and its children (which are embedded in getAllWithItems logic?)
                // Wait, BudgetCategory::getAllWithItems() returns flat list of categories with 'items' array.
                // It does NOT nest children categories. It just adds items.
                // We need to rebuild tree or filter flat list?
                // Actually Organization::buildTree does nesting. BudgetCategory::getAllWithItems just attaches items to flat list.
                // So we need to find children based on parent_id.
                
                // Let's restart logic:
                // Find self
                $result[] = $cat;
                // Find children
                $this->appendChildren($allCats, $cat['id'], $result);
                break;
            }
        }
    }
    
    private function appendChildren($allCats, $parentId, &$result) {
        foreach ($allCats as $cat) {
            if ($cat['parent_id'] == $parentId) {
                $result[] = $cat;
                $this->appendChildren($allCats, $cat['id'], $result);
            }
        }
    }

    /**
     * Save Smart Budget Tracking (via Model)
     */
    public function saveTracking(): void
    {
        Auth::require();
        
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['items'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            exit;
        }

        $fiscalYear = (int) $input['fiscalYear'];
        $orgId = !empty($input['orgId']) ? (int) $input['orgId'] : null;
        $items = $input['items'];

        try {
            $count = BudgetTracking::bulkUpsert($fiscalYear, $items, $orgId);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'updated' => $count]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Validate budget input
     */
    private function validate(array $data): array
    {
        $errors = [];
        
        if (empty($data['category_id'])) {
            $errors[] = 'กรุณาเลือกหมวดหมู่';
        }
        
        if (empty($data['fiscal_year'])) {
            $errors[] = 'กรุณาเลือกปีงบประมาณ';
        }
        
        if (!isset($data['allocated_amount']) || $data['allocated_amount'] < 0) {
            $errors[] = 'งบประมาณจัดสรรต้องมากกว่าหรือเท่ากับ 0';
        }
        
        return $errors;
    }
}
