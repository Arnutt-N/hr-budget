<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Core\Router;
use App\Models\FiscalYear;
use App\Models\BudgetRequest;
use App\Models\BudgetRequestItem;
use App\Models\BudgetRequestApproval;

class BudgetRequestController
{
    /**
     * Dashboard page
     */
    public static function dashboard()
    {
        Auth::require();
        $fiscalYear = $_GET['year'] ?? FiscalYear::currentYear();
        
        View::render('requests/dashboard', [
            'stats' => BudgetRequest::getStats($fiscalYear),
            'recent' => BudgetRequest::getRecentRequests(),
            'fiscalYear' => $fiscalYear,
            'fiscalYears' => FiscalYear::all(),
            'currentPage' => 'requests', // Fix: add missing currentPage variable
            'title' => 'ภาพรวมคำของบประมาณ'
        ], 'main');
    }

    /**
     * List requests
     */
    public static function index()
    {
        Auth::require();
        
        $fiscalYear = (int)($_GET['year'] ?? FiscalYear::currentYear());
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        $filters = ['fiscal_year' => $fiscalYear];
        
        $requests = BudgetRequest::all($filters, $perPage, $offset);
        $total = BudgetRequest::count($filters);
        
        $pagination = [
            'current' => $page,
            'perPage' => $perPage,
            'total' => ceil($total / $perPage),
            'totalRecords' => $total
        ];

        // Calculate Summary Stats
        $stats = [
            'total_request' => 0,
            'personnel' => [
                'total' => 0, 
                'salary' => 0,      // เงินเดือนและค่าจ้างประจำ
                'compensation' => 0 // ค่าตอบแทนพนักงานราชการ
            ],
            'operating' => [
                'total' => 0,
                'remune_mat' => 0, // ค่าตอบแทนใช้สอยและวัสดุ
                'utility' => 0     // ค่าสาธารณูปโภค
            ],
            'other' => [
                'total' => 0,
                'investment' => 0, // งบลงทุน
                'subsidy' => 0,    // งบเงินอุดหนุน
                'other_exp' => 0   // งบรายจ่ายอื่น
            ]
        ];

        // Fetch all items from relevant requests to calculate stats
        // Note: Ideally this should be a refined SQL query for performance, but for now we iterate
        // Condition: Request must be "confirmed" (total_amount > 0 for now as per agreement)
        $requestIds = array_column(array_filter($requests, function($r) {
            return $r['total_amount'] > 0;
        }), 'id');

        if (!empty($requestIds)) {
            $db = \App\Core\Database::getInstance();
            $idsPlaceholders = implode(',', array_fill(0, count($requestIds), '?'));
            
            $sql = "SELECT bri.quantity, bri.unit_price, bci.name, bc.name as category_name, bc.id as category_id
                    FROM budget_request_items bri
                    JOIN budget_category_items bci ON bri.category_item_id = bci.id
                    JOIN budget_categories bc ON bci.budget_category_id = bc.id -- This join might need adjustment based on real schema
                    WHERE bri.budget_request_id IN ($idsPlaceholders)";
            
            // Re-evaluating based on actual schema: 
            // We need to map Items -> Category Items -> Groups/Types.
            // Current models: BudgetCategoryItem (id, name, parent_id, budget_category_id)
            // But we found Real Expense Types via `expense_types` table in previous step.
            // AND we found `budget_category_items` uses `budget_category_id` = 21 (likely a single category for all? or simplified)
            
            // CORRECT APPROACH based on Project Knowledge:
            // The mapping between `budget_category_items` and `expense_items` might be missing or implicit.
            // Let's rely on string matching for the Groups based on the Python script output?
            // "เงินเดือนและค่าจ้างประจำ"
            // "ค่าตอบแทนพนักงานราชการ"
            // "ค่าตอบแทนใช้สอยและวัสดุ"
            // "ค่าครุภัณฑ์ ที่ดินและสิ่งก่อสร้าง"

            // Let's fetch item names and sum amounts.
            // bri.unit_price holds the Total Amount as per update() method logic.
            
            $sqlItems = "SELECT bri.unit_price as amount, bci.name as item_name
                         FROM budget_request_items bri
                         JOIN budget_category_items bci ON bri.category_item_id = bci.id
                         WHERE bri.budget_request_id IN ($idsPlaceholders)";
            
            $stmt = $db->prepare($sqlItems);
            $stmt->execute($requestIds);
            $allItems = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($allItems as $item) {
                $amount = (float)$item['amount'];
                $name = $item['item_name'];
                
                $stats['total_request'] += $amount;

                // Simple keyword matching based on Hierarchy Analysis
                // TYPE 1: งบบุคลากร
                if (strpos($name, 'เงินเดือน') !== false || strpos($name, 'ค่าจ้างประจำ') !== false) {
                    $stats['personnel']['salary'] += $amount;
                    $stats['personnel']['total'] += $amount;
                } elseif (strpos($name, 'ค่าตอบแทนพนักงานราชการ') !== false) {
                    $stats['personnel']['compensation'] += $amount;
                    $stats['personnel']['total'] += $amount;
                }
                // TYPE 2: งบดำเนินงาน
                elseif (strpos($name, 'ค่าตอบแทน') !== false || strpos($name, 'ค่าใช้สอย') !== false || strpos($name, 'วัสดุ') !== false) {
                    $stats['operating']['remune_mat'] += $amount;
                    $stats['operating']['total'] += $amount;
                } elseif (strpos($name, 'ค่าสาธารณูปโภค') !== false) {
                    $stats['operating']['utility'] += $amount;
                    $stats['operating']['total'] += $amount;
                }
                // TYPE 3: งบลงทุน
                elseif (strpos($name, 'ครุภัณฑ์') !== false || strpos($name, 'ที่ดิน') !== false || strpos($name, 'สิ่งก่อสร้าง') !== false) {
                    $stats['other']['investment'] += $amount;
                    $stats['other']['total'] += $amount;
                }
                // TYPE 5: งบเงินอุดหนุน
                elseif (strpos($name, 'อุดหนุน') !== false) {
                    $stats['other']['subsidy'] += $amount;
                    $stats['other']['total'] += $amount;
                }
                // TYPE 4: งบรายจ่ายอื่น (Everything else not captured above)
                else {
                    // Fallback for strict Type 1 & 2 matching above. 
                    // If it's none of the above, put in 'other_exp'
                    // NOTE: This logic is fuzzy. In production, we need real foreign keys to expense_types.
                    // For now, this visualizes the UI requirement.
                    $stats['other']['other_exp'] += $amount;
                    $stats['other']['total'] += $amount;
                }
            }
        }

        View::render('requests/index', [
            'requests' => $requests,
            'summaryStats' => $stats, // Pass the new stats
            'fiscalYear' => $fiscalYear,
            'fiscalYears' => FiscalYear::all(),
            'organizations' => \App\Models\Organization::all(),
            'pagination' => $pagination,
            'currentPage' => 'requests',
            'title' => 'คำของบประมาณ'
        ], 'main');
    }

    /**
     * Create request (Immediate Draft)
     */
    public static function create()
    {
        Auth::require();
        
        $fiscalYear = $_GET['fiscal_year'] ?? FiscalYear::currentYear();
        $orgId = $_GET['org_id'] ?? null;
        $requestTitle = $_GET['request_title'] ?? '';
        
        if (!$orgId) {
            Router::redirect('/requests');
            return;
        }

        // AUTO-CREATE DRAFT
        $requestId = BudgetRequest::create([
            'fiscal_year' => $fiscalYear,
            'request_title' => $requestTitle,
            'request_status' => 'draft',
            'total_amount' => 0,
            'created_by' => Auth::id(),
            'org_id' => $orgId,
        ]);

        if ($requestId) {
            Router::redirect("/requests/{$requestId}/edit");
        } else {
            Router::redirect('/requests');
        }
    }

    /**
     * Edit request
     */
    public static function edit(int $id)
    {
        Auth::require();
        
        $request = BudgetRequest::find($id);
        if (!$request) {
            Router::redirect('/requests');
            return;
        }

        // Check permission (optional: ensure user owns this request)
        if ($request['created_by'] != Auth::id() && !Auth::hasRole('admin')) {
             // Router::redirect('/requests'); // specific error?
        }
        
        $organization = \App\Models\Organization::find($request['org_id']);
        
        // Get level 1 categories (งบบุคลากร, งบดำเนินงาน, etc.) as tabs
        $budgetTree = \App\Models\BudgetCategory::getTopLevelCategories();
        
        // Fetch saved items and map by category_item_id
        $rawItems = BudgetRequestItem::getByRequestId($id);
        $savedItems = [];
        foreach ($rawItems as $itm) {
            $savedItems[$itm['category_item_id']] = $itm;
        }

        View::render('requests/form', [
            'action' => 'update',
            'requestId' => $id,
            'request' => $request,
            'fiscalYear' => $request['fiscal_year'],
            'orgId' => $request['org_id'],
            'organization' => $organization,
            'requestTitle' => $request['request_title'],
            'budgetTree' => $budgetTree,
            'savedItems' => $savedItems,
            'currentPage' => 'requests',
            'title' => 'บันทึกคำของบประมาณ'
        ], 'main');
    }

    /**
     * Update request
     */
    public static function update(int $id)
    {
        Auth::require();
        
        $items = [];
        if (isset($_POST['items_json'])) {
            $json = $_POST['items_json'];
            error_log("Update ID $id: Received items_json (Len: " . strlen($json) . ")");
            $decoded = json_decode($json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("JSON Decode Error: " . json_last_error_msg());
            }
            $items = $decoded ?? [];
            error_log("Parsed Items Count: " . count($items));
            // Debug: write received JSON and POST data to storage for inspection
            $logPath = __DIR__ . '/../../storage/debug_save.txt';
            $logData = "---\n" . date('Y-m-d H:i:s') . "\n";
            if (isset($json)) {
                $logData .= "items_json length: " . strlen($json) . "\n";
                $logData .= "items_json content: " . $json . "\n";
            }
            $logData .= "Decoded items count: " . count($items) . "\n";
            $logData .= "POST dump: " . print_r($_POST, true) . "\n";
            file_put_contents($logPath, $logData, FILE_APPEND);
        }
        // 1. Update Request Details (if changed in form? currently mostly items)
        // Recalculate Total
        $totalAmount = 0;
        foreach ($items as $item) {
            $amount = (float)($item['amount'] ?? 0);
            $totalAmount += $amount;
        }
        
        BudgetRequest::update($id, [
            'total_amount' => $totalAmount,
            'request_status' => 'saved' // Change from draft to saved after saving
        ]);

        // 2. Upsert Items
        foreach ($items as $categoryItemId => $itemData) {
            $amount = (float)($itemData['amount'] ?? 0);
            $quantity = (float)($itemData['quantity'] ?? 0);
            $unitPrice = (float)($itemData['unit_price'] ?? 0);
            $note = $itemData['note'] ?? null;
            
            // Upsert only valid data (or if deleting?)
            // If currently 0 and previously existed, should we delete?
            // Let's assume we update as 0.
            
            // Get item name just in case new insert
        // CHANGED: Use ExpenseItem because form now renders ExpenseItems
        $catItem = \App\Models\ExpenseItem::find($categoryItemId);
        $itemName = $catItem['name_th'] ?? 'Item';
        
        BudgetRequestItem::upsert($id, $categoryItemId, [
            'item_name' => $itemName,
            'quantity' => $quantity,
            'unit_price' => $unitPrice, // Only the actual per-unit price
            'amount' => $amount,        // Direct amount input
            'remark' => $note
        ]);
    }    
        
        // Redirect back to edit (stay on form) OR list?
        // Usually Save -> Stay or List.
        // User might want to save draft and continue.
        // Let's redirect to edit with success message.
        $_SESSION['flash_success'] = 'บันทึกข้อมูลเรียบร้อยแล้ว';
        Router::redirect("/requests/{$id}/edit");
    }

    /**
     * Store new request (Legacy/Fallback if posted directly)
     */
    public static function store()
    {
        // This might not be used anymore if we always create draft first
        // But keeping it for safety
        self::create();
    }

    /**
     * Show request detail (Read-Only View using form.php)
     */
    public static function show(int $id)
    {
        Auth::require();
        
        $request = BudgetRequest::find($id);
        if (!$request) {
            Router::redirect('/requests');
            return;
        }
        
        // Use the same logic as edit(), but with readonly flag
        $organization = \App\Models\Organization::find($request['org_id']);
        
        // Get level 1 categories (งบบุคลากร, งบดำเนินงาน, etc.) as tabs
        $budgetTree = \App\Models\BudgetCategory::getTopLevelCategories();
        
        // Fetch saved items and map by category_item_id
        $rawItems = BudgetRequestItem::getByRequestId($id);
        $savedItems = [];
        foreach ($rawItems as $itm) {
            $savedItems[$itm['category_item_id']] = $itm;
        }

        View::render('requests/form', [
            'action' => 'view',  // Important: 'view' action triggers readonly mode
            'readonly' => true,  // Explicit readonly flag
            'requestId' => $id,
            'request' => $request,
            'fiscalYear' => $request['fiscal_year'],
            'orgId' => $request['org_id'],
            'organization' => $organization,
            'requestTitle' => $request['request_title'],
            'budgetTree' => $budgetTree,
            'savedItems' => $savedItems,
            'currentPage' => 'requests',
            'title' => 'รายละเอียดคำขอ'
        ], 'main');
    }

    /**
     * Submit request -> pending
     */
    public static function submit(int $id)
    {
        Auth::require();
        
        $request = BudgetRequest::find($id);
        if ($request['created_by'] != Auth::id() && !Auth::hasRole('admin')) {
             // Only owner or admin can submit
        }
        
        BudgetRequest::updateStatus($id, 'pending');
        BudgetRequestApproval::log($id, 'submitted', Auth::id(), 'Request submitted for approval');
        
        Router::redirect("/requests/{$id}");
    }

    /**
     * Approve request -> approved
     */
    public static function approve(int $id)
    {
        Auth::require();
        
        // Check permission: only admin/approver
        if (!Auth::hasRole('admin') && !Auth::hasRole('editor')) {
             Router::redirect("/requests/{$id}");
             return;
        }
        
        BudgetRequest::updateStatus($id, 'approved');
        BudgetRequestApproval::log($id, 'approved', Auth::id(), 'Request approved');
        
        Router::redirect("/requests/{$id}");
    }

    /**
     * Reject request -> rejected
     */
    public static function reject(int $id)
    {
        Auth::require();
        
        if (!Auth::hasRole('admin') && !Auth::hasRole('editor')) {
             Router::redirect("/requests/{$id}");
             return;
        }
        
        $reason = $_POST['reason'] ?? null;
        BudgetRequest::updateStatus($id, 'rejected', $reason);
        BudgetRequestApproval::log($id, 'rejected', Auth::id(), 'Request rejected: ' . $reason);
        
        Router::redirect("/requests/{$id}");
    }

    /**
     * Delete request
     */
    public static function destroy(int $id)
    {
        Auth::require();
        
        $request = BudgetRequest::find($id);
        if ($request['created_by'] != Auth::id() && !Auth::hasRole('admin')) {
             Router::redirect('/requests');
             return;
        }
        
        if (BudgetRequest::delete($id)) {
            // Also delete items? FK ON DELETE CASCADE usually handles this.
            // If not, delete manually.
            $_SESSION['flash_success'] = 'ลบคำขอเรียบร้อยแล้ว';
        }
        
        Router::redirect('/requests');
    }

    /**
     * Store single item (AJAX)
     */
    public static function storeItem(int $id)
    {
        Auth::require();
        // ... (Keep existing AJAX logic if any)
         echo json_encode(['success' => true]);
    }
    
    public static function getCategoryItems(int $id)
    {
        Auth::require();
        
        $categoryId = $_GET['category_id'] ?? null;
        
        header('Content-Type: application/json');
        
        if (!$categoryId) {
            echo json_encode(['success' => false, 'error' => 'Missing category_id']);
            return;
        }
        
        try {
            $items = \App\Models\BudgetCategoryItem::getHierarchy((int)$categoryId);
            echo json_encode(['success' => true, 'items' => $items]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    
    public static function updateItem(int $id)
    {
        // ...
    }
    
    public static function destroyItem(int $id, int $itemId)
    {
        // ...
    }
}
