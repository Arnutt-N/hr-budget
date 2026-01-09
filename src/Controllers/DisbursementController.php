<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Core\Router;
use App\Core\Request;
use App\Models\DisbursementHeader;
use App\Models\DisbursementDetail;
use App\Models\Organization;
use App\Models\FiscalYear;
use App\Models\Plan;
use App\Models\Project;
use App\Models\Activity;
use App\Models\ExpenseType;

class DisbursementController
{
    /**
     * List all disbursements
     */
    public function index()
    {
        Auth::require();
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        // Simple fetch for now, can add filters later
        // Need to add 'with' relations if Model supports it, or just use loops in view (N+1 issue but ok for MVP)
        // Or manual query in Model
        
        // For MVP, fetching all descending by created_at
        // Todo: Implement pagination in Model
        $disbursements = DisbursementHeader::all(); // This might return array
        
        // Manual sorting/pagination if Model::all returns array
        usort($disbursements, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));
        $total = count($disbursements);
        $disbursements = array_slice($disbursements, $offset, $limit);
        
        // Enrich with Organization Name
        foreach ($disbursements as &$d) {
            $org = Organization::find($d['organization_id']);
            $d['organization_name'] = $org ? $org['name_th'] : '-'; // Organization uses name_th
            
            // Count items
            $res = \App\Core\Database::queryOne("SELECT COUNT(*) as count, SUM(item_0+item_1+item_2+item_3+item_4+item_5) as total FROM disbursement_details WHERE header_id = ?", [$d['id']]);
            $d['items_count'] = $res['count'] ?? 0;
            $d['total_amount'] = $res['total'] ?? 0;
        }

        View::render('disbursements/index', [
            'title' => 'รายการเบิกจ่ายงบประมาณ',
            'disbursements' => $disbursements,
            'currentPage' => 'disbursements',
            'page' => $page,
            'totalPages' => ceil($total / $limit)
        ], 'main');
    }

    /**
     * Show Create Header Form
     */
    public function create()
    {
        Auth::require();
        
        $fiscalYears = FiscalYear::getForSelect();
        $organizations = Organization::all();
        
        View::render('disbursements/create', [
            'title' => 'สร้างรายการเบิกจ่าย',
            'currentPage' => 'disbursements',
            'fiscalYears' => $fiscalYears,
            'organizations' => $organizations
        ], 'main');
    }

    /**
     * Store Header
     */
    public function store()
    {
        Auth::require();
        
        $data = $_POST;
        // Validation
        if (empty($data['fiscal_year']) || empty($data['month']) || empty($data['organization_id']) || empty($data['record_date'])) {
            $_SESSION['flash_error'] = 'กรุณากรอกข้อมูลให้ครบถ้วน';
            Router::redirect('/budgets/disbursements/create');
            return;
        }

        $id = DisbursementHeader::create([
            'fiscal_year' => $data['fiscal_year'],
            'month' => $data['month'],
            'organization_id' => $data['organization_id'],
            'record_date' => $data['record_date'],
            'status' => 'draft',
            'created_by' => Auth::id()
        ]);

        if ($id) {
            $_SESSION['flash_success'] = 'สร้างรายการสำเร็จ';
            Router::redirect("/budgets/disbursements/{$id}");
        } else {
            $_SESSION['flash_error'] = 'เกิดข้อผิดพลาดในการบันทึก';
            Router::redirect('/budgets/disbursements/create');
        }
    }

    /**
     * Show Header Details
     */
    public function show($id)
    {
        Auth::require();
        
        $header = DisbursementHeader::find($id);
        if (!$header) {
            Router::redirect('/budgets/disbursements');
            return;
        }

        $organization = Organization::find($header['organization_id']);
        $header['organization_name'] = $organization ? $organization['name_th'] : '-';
        
        // Get details with relations
        $details = \App\Core\Database::query("
            SELECT d.*, 
                   e.name_th as expense_type_name,
                   p.name_th as plan_name,
                   pj.name_th as project_name,
                   a.name_th as activity_name,
                   pj.name_th as output_name
            FROM disbursement_details d
            LEFT JOIN expense_types e ON d.expense_type_id = e.id
            LEFT JOIN plans p ON d.plan_id = p.id
            LEFT JOIN projects pj ON d.output_id = pj.id
            LEFT JOIN activities a ON d.activity_id = a.id
            WHERE d.header_id = ?
            ORDER BY d.id ASC
        ", [$id]);

        View::render('disbursements/show', [
            'title' => 'รายละเอียดการเบิกจ่าย',
            'currentPage' => 'disbursements',
            'header' => $header,
            'organization' => $organization,
            'details' => $details
        ], 'main');
    }

    /**
     * Show Edit Header Form
     */
    public function edit($id)
    {
        Auth::require();
        
        $header = DisbursementHeader::find($id);
        if (!$header) {
            Router::redirect('/budgets/disbursements');
            return;
        }
        
        $fiscalYears = FiscalYear::getForSelect();
        $organizations = Organization::all();

        View::render('disbursements/edit', [
            'title' => 'แก้ไขรายการเบิกจ่าย',
            'currentPage' => 'disbursements',
            'header' => $header,
            'fiscalYears' => $fiscalYears,
            'organizations' => $organizations
        ], 'main');
    }

    /**
     * Update Header
     */
    public function update($id)
    {
        Auth::require();
        
        $data = $_POST;
        DisbursementHeader::update($id, [
            'fiscal_year' => $data['fiscal_year'],
            'month' => $data['month'],
            'organization_id' => $data['organization_id'],
            'record_date' => $data['record_date']
        ]);
        
        $_SESSION['flash_success'] = 'บันทึกข้อมูลสำเร็จ';
        Router::redirect("/budgets/disbursements/{$id}");
    }

    /**
     * Delete Header
     */
    public function destroy($id)
    {
        Auth::require();
        
        DisbursementHeader::delete($id);
        $_SESSION['flash_success'] = 'ลบรายการสำเร็จ';
        Router::redirect('/budgets/disbursements');
    }

    // --- Items (Details) ---

    public function createItem($id)
    {
        Auth::require();

        $header = DisbursementHeader::find($id);
        if (!$header) return;

        $plans = Plan::getByFiscalYear($header['fiscal_year']);
        $expenseTypes = ExpenseType::where('is_active', 1);

        View::render('disbursements/items/create', [
            'title' => 'เพิ่มรายละเอียด',
            'currentPage' => 'disbursements',
            'header' => $header,
            'plans' => $plans,
            'expenseTypes' => $expenseTypes
        ], 'main');
    }

    public function storeItem($id)
    {
        Auth::require();
        
        $data = $_POST;
        // Validation basic
        if (empty($data['expense_type_id'])) {
             // Handle error
        }

        DisbursementDetail::create([
            'header_id' => $id,
            'plan_id' => $data['plan_id'] ?? null,
            'output_id' => $data['output_id'] ?? null,
            'activity_id' => $data['activity_id'] ?? null,
            'expense_type_id' => $data['expense_type_id'],
            'item_0' => $data['item_0'] ?? 0,
            'item_1' => $data['item_1'] ?? 0,
            'item_2' => $data['item_2'] ?? 0,
            'item_3' => $data['item_3'] ?? 0,
            'item_4' => $data['item_4'] ?? 0,
            'item_5' => $data['item_5'] ?? 0,
            'notes' => $data['notes'] ?? null
        ]);

        $_SESSION['flash_success'] = 'เพิ่มรายการสำเร็จ';
        Router::redirect("/budgets/disbursements/{$id}");
    }

    public function editItem($id, $itemId)
    {
        Auth::require();
        
        $header = DisbursementHeader::find($id);
        $detail = DisbursementDetail::find($itemId);
        
        $plans = Plan::getByFiscalYear($header['fiscal_year']);
        $expenseTypes = ExpenseType::where('is_active', 1)->get();
        
        // Inject current level options
        $outputs = $detail['plan_id'] ? Project::where('plan_id', $detail['plan_id'])->get() : [];
        $activities = $detail['output_id'] ? Activity::where('project_id', $detail['output_id'])->get() : [];

        View::render('disbursements/items/edit', [
            'title' => 'แก้ไขรายละเอียด',
            'currentPage' => 'disbursements',
            'header' => $header,
            'detail' => $detail,
            'plans' => $plans,
            'expenseTypes' => $expenseTypes,
            'outputs' => $outputs,
            'activities' => $activities
        ], 'main');
    }

    public function updateItem($id, $itemId)
    {
        Auth::require();
        $data = $_POST;
        
        DisbursementDetail::update($itemId, [
            'plan_id' => $data['plan_id'] ?? null,
            'output_id' => $data['output_id'] ?? null,
            'activity_id' => $data['activity_id'] ?? null,
            'expense_type_id' => $data['expense_type_id'],
            'item_0' => $data['item_0'] ?? 0,
            'item_1' => $data['item_1'] ?? 0,
            'item_2' => $data['item_2'] ?? 0,
            'item_3' => $data['item_3'] ?? 0,
            'item_4' => $data['item_4'] ?? 0,
            'item_5' => $data['item_5'] ?? 0,
            'notes' => $data['notes'] ?? null
        ]);

        $_SESSION['flash_success'] = 'แก้ไขรายการสำเร็จ';
        Router::redirect("/budgets/disbursements/{$id}");
    }

    public function destroyItem($id, $itemId)
    {
        Auth::require();
        DisbursementDetail::delete($itemId);
        $_SESSION['flash_success'] = 'ลบรายการสำเร็จ';
        Router::redirect("/budgets/disbursements/{$id}");
    }

    // --- API ---

    public function getOutputs()
    {
        $parentId = $_GET['parent_id'] ?? 0;
        $outputs = Project::where('plan_id', $parentId)->get();
        echo json_encode($outputs);
        exit;
    }

    public function getActivities()
    {
        $parentId = $_GET['parent_id'] ?? 0;
        $activities = Activity::where('project_id', $parentId)->get();
        echo json_encode($activities);
        exit;
    }
}
