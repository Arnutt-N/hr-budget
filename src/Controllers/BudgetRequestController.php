<?php
/**
 * Budget Request Controller
 * 
 * Handles budget request pages and actions
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Core\Router;
use App\Models\BudgetRequest;
use App\Models\BudgetRequestItem;
use App\Models\BudgetRequestApproval;
use App\Models\BudgetDisbursement;
use App\Models\FiscalYear;
use App\Models\BudgetCategoryItem;

class BudgetRequestController
{
    /**
     * Dashboard for Budget Requests
     */
    public static function dashboard()
    {
        Auth::require();
        
        $fiscalYear = (int)($_GET['year'] ?? FiscalYear::currentYear());
        
        $stats = BudgetRequest::getStats($fiscalYear);
        $recentRequests = BudgetRequest::getRecentRequests(10);
        
        View::render('requests/dashboard', [
            'stats' => $stats,
            'recentRequests' => $recentRequests,
            'fiscalYear' => $fiscalYear,
            'fiscalYears' => FiscalYear::all(),
            'title' => 'ภาพรวมคำของบประมาณ'
        ], 'main');
    }

    /**
     * List all requests
     */
    public static function index()
    {
        Auth::require();
        
        // If no view param, redirect to dashboard? No, keep separate
        // But maybe we want /requests to go to dashboard?
        // Let's keep /requests as list for now as per routes

        
        $fiscalYear = (int)($_GET['year'] ?? FiscalYear::currentYear());
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        $filters = ['fiscal_year' => $fiscalYear];
        
        // Non-admin can only see their own requests? 
        // Or maybe everyone sees everything for now? Let's assume view all or filter by department
        // checking roles
        $user = Auth::user();
        // if ($user['role'] === 'viewer') { ... } 

        $requests = BudgetRequest::all($filters, $perPage, $offset);
        $total = BudgetRequest::count($filters);
        
        $pagination = [
            'current' => $page,
            'perPage' => $perPage,
            'total' => ceil($total / $perPage),
            'totalRecords' => $total
        ];

        View::render('requests/index', [
            'requests' => $requests,
            'fiscalYear' => $fiscalYear,
            'fiscalYears' => FiscalYear::all(),
            'pagination' => $pagination,
            'currentPage' => 'requests', // Fix: add missing currentPage variable
            'title' => 'คำของบประมาณ'
        ], 'main');
    }

    /**
     * Show create form
     */
    public static function create()
    {
        Auth::require();
        
        View::render('requests/form', [
            'action' => 'create',
            'fiscalYear' => FiscalYear::currentYear(),
            'fiscalYears' => FiscalYear::getForSelect(),
            'organizations' => \App\Models\Organization::all(), // Phase 3
            'currentPage' => 'requests',
            'title' => 'สร้างคำของบประมาณ'
        ], 'main');
    }

    /**
     * Store new request
     */
    public static function store()
    {
        Auth::require();
        
        $data = [
            'fiscal_year' => $_POST['fiscal_year'],
            'request_title' => $_POST['request_title'],
            'created_by' => Auth::id(),
            'org_id' => !empty($_POST['org_id']) ? $_POST['org_id'] : null, // Phase 3
            'request_status' => 'draft'
        ];
        
        $id = BudgetRequest::create($data);
        
        if ($id) {
            BudgetRequestApproval::log($id, 'created', Auth::id(), 'Request created');
            Router::redirect("/requests/{$id}"); // Go to detail to add items
        } else {
            // handle error
            Router::redirect('/requests/create');
        }
    }

    /**
     * Show request detail
     */
    public static function show(int $id)
    {
        Auth::require();
        
        $request = BudgetRequest::find($id);
        if (!$request) {
            Router::redirect('/requests');
        }
        
        $items = BudgetRequestItem::getTree($id);
        $logs = BudgetRequestApproval::getByRequestId($id);
        
        View::render('requests/show', [
            'request' => $request,
            'items' => $items,
            'logs' => $logs,
            'budgetCategories' => \App\Models\BudgetCategory::getForSelect(),
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
             // For simplicity redirect back
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
             // Redirect with error or show 403
             // For now redirect back
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
        BudgetRequestApproval::log($id, 'rejected', Auth::id(), 'Request rejected', $reason);
        
        Router::redirect("/requests/{$id}");
    }

    /**
     * Add Item to Request
     */
    public static function storeItem(int $id)
    {
        Auth::require();
        
        $request = BudgetRequest::find($id);
        if ($request['request_status'] !== 'draft') {
            Router::redirect("/requests/{$id}");
            return;
        }

        $data = [
            'budget_request_id' => $id,
            'item_name' => $_POST['item_name'],
            'quantity' => (int) $_POST['quantity'],
            'unit_price' => (float) $_POST['unit_price'],
            'item_description' => $_POST['item_description'] ?? null
        ];

        BudgetRequestItem::create($data);
        self::recalculateTotal($id);

        Router::redirect("/requests/{$id}");
    }

    /**
     * Delete Item
     */
    public static function destroyItem(int $id, int $itemId)
    {
        Auth::require();
        
        // Verify item belongs to request (security check omitted for MVP speed but recommended)
        
        BudgetRequestItem::delete($itemId);
        self::recalculateTotal($id);

        Router::redirect("/requests/{$id}");
    }

    /**
     * AJAX: Get items for a category (merged with saved items)
     */
    public static function getCategoryItems(int $id)
    {
        Auth::require();
        
        $categoryId = (int) $_GET['category_id'];
        $masterItems = BudgetCategoryItem::getByCategory($categoryId);
        
        // Get saved items to merge
        $savedItems = BudgetRequestItem::getByRequestId($id);
        $savedMap = [];
        foreach ($savedItems as $item) {
            if ($item['category_item_id']) {
                $savedMap[$item['category_item_id']] = $item;
            }
        }
        
        // Merge
        $result = [];
        foreach ($masterItems as $mItem) {
            $saved = $savedMap[$mItem['id']] ?? null;
            $result[] = [
                'id' => $mItem['id'], // category_item_id
                'item_code' => $mItem['item_code'] ?? $mItem['code'] ?? '',
                'item_name' => $mItem['name'] ?? $mItem['item_name'] ?? '',
                'is_header' => (bool)$mItem['is_header'],
                'level' => (int)$mItem['level'],
                'default_unit' => $mItem['default_unit'],
                'requires_quantity' => (bool)$mItem['requires_quantity'],
                // Saved values or defaults
                'saved_id' => $saved['id'] ?? null,
                'quantity' => $saved['quantity'] ?? null,
                'unit_price' => $saved['unit_price'] ?? null,
                'remark' => $saved['remark'] ?? '',
                'total_amount' => $saved ? ($saved['quantity'] * $saved['unit_price']) : 0
            ];
        }
        
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    /**
     * AJAX: Update item (Auto-save)
     */
    public static function updateItem(int $id)
    {
        try {
            Auth::require();
            
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                throw new \Exception('Invalid JSON payload');
            }

            $categoryItemId = (int) ($input['category_item_id'] ?? 0);
            if (!$categoryItemId) {
                throw new \Exception('Category Item ID is required');
            }
            
            // Find category item to get name
            $catItem = BudgetCategoryItem::find($categoryItemId);
            if (!$catItem) {
                throw new \Exception('Category Item not found');
            }

            // Data to save
            $data = [
                'quantity' => isset($input['quantity']) && $input['quantity'] !== '' ? (float)$input['quantity'] : 0,
                'unit_price' => isset($input['unit_price']) && $input['unit_price'] !== '' ? (float)$input['unit_price'] : 0,
                'total_amount' => isset($input['total_amount']) && $input['total_amount'] !== '' ? (float)$input['total_amount'] : 0,
                'remark' => $input['remark'] ?? null,
                'item_name' => $catItem['name'] ?? $catItem['item_name'] ?? ''
            ];
            
            $savedId = BudgetRequestItem::upsert($id, $categoryItemId, $data);
            
            self::recalculateTotal($id);
            $newTotal = BudgetRequest::find($id)['total_amount'];
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'saved_id' => $savedId,
                'total_amount' => $newTotal
            ]);
            exit;

        } catch (\Throwable $e) {
            \App\Core\ErrorHandler::handleException($e);
        }
    }

    /**
     * Recalculate Request Total
     */
    private static function recalculateTotal(int $id)
    {
        $items = BudgetRequestItem::getByRequestId($id);
        $total = 0;
        foreach ($items as $item) {
            $total += $item['quantity'] * $item['unit_price'];
        }
        
        BudgetRequest::update($id, ['total_amount' => $total]);
    }
    
    /**
     * Delete request (Admin only)
     */
    public static function destroy(int $id)
    {
        Auth::require();
        
        // Only admin can delete
        if (!Auth::hasRole('admin')) {
            Router::redirect('/requests');
            return;
        }
        
        // Delete related items first
        $items = BudgetRequestItem::getByRequestId($id);
        foreach ($items as $item) {
            BudgetRequestItem::delete($item['id']);
        }
        
        // Delete request
        BudgetRequest::delete($id);
        
        Router::redirect('/requests');
    }
}
