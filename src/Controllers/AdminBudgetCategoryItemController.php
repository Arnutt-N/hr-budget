<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Core\Router;
use App\Models\BudgetCategoryItem;

class AdminBudgetCategoryItemController
{
    private function checkAdmin()
    {
        Auth::require();
        $user = Auth::user();
        if (!$user || !isset($user['role']) || $user['role'] !== 'admin') {
            $_SESSION['flash_error'] = 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้';
            Router::redirect('/dashboard');
            exit;
        }
        return $user;
    }

    public function index()
    {
        $user = $this->checkAdmin();
        
        // Check if we should include deleted items
        $includeDeleted = isset($_GET['show_deleted']) && $_GET['show_deleted'] == '1';
        
        // Get all items (always include inactive for admin view)
        $items = BudgetCategoryItem::getAll(true, $includeDeleted);
        
        // Build tree structure for display
        $tree = $this->buildTree($items);
        
        View::render('admin/category-items/index', [
            'title' => 'จัดการหมวดหมู่รายจ่าย',
            'currentPage' => 'admin-category-items',
            'items' => $items,
            'tree' => $tree,
            'showDeleted' => $includeDeleted,
            'auth' => $user,
        ], 'main');
    }

    private function buildTree(array $items, ?int $parentId = null): array
    {
        $tree = [];
        foreach ($items as $item) {
            if (($item['parent_id'] ?? null) == $parentId) {
                $item['children'] = $this->buildTree($items, $item['id']);
                $tree[] = $item;
            }
        }
        return $tree;
    }

    public function create()
    {
        $user = $this->checkAdmin();
        
        // Get all active items for parent selection
        $parents = BudgetCategoryItem::getAll(true, false);
        
        View::render('admin/category-items/form', [
            'title' => 'เพิ่มหมวดหมู่รายจ่ายใหม่',
            'currentPage' => 'admin-category-items',
            'mode' => 'create',
            'item' => null,
            'parents' => $parents,
            'auth' => $user,
        ], 'main');
    }

    public function store()
    {
        $user = $this->checkAdmin();
        $data = $_POST;
        
        $errors = $this->validate($data);
        
        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode(', ', $errors);
            $_SESSION['form_data'] = $data;
            Router::redirect('/admin/category-items/create');
            return;
        }

        try {
            // Calculate level based on parent
            $level = 0;
            if (!empty($data['parent_id'])) {
                $parent = BudgetCategoryItem::find($data['parent_id']);
                $level = $parent ? (int)$parent['level'] + 1 : 0;
            }

            BudgetCategoryItem::create([
                'name' => trim($data['name']),
                'code' => trim($data['code']),
                'parent_id' => !empty($data['parent_id']) ? (int)$data['parent_id'] : null,
                'level' => $level,
                'description' => trim($data['description'] ?? ''),
                'sort_order' => (int)($data['sort_order'] ?? 0),
                'is_active' => isset($data['is_active']) ? 1 : 0,
                'created_by' => $user['id'],
            ]);

            $_SESSION['flash_success'] = 'เพิ่มหมวดหมู่รายจ่ายสำเร็จ';
            Router::redirect('/admin/category-items');
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error: ' . $e->getMessage();
            $_SESSION['form_data'] = $data;
            Router::redirect('/admin/category-items/create');
        }
    }

    public function edit(int $id)
    {
        $user = $this->checkAdmin();
        $item = BudgetCategoryItem::find($id);
        
        if (!$item) {
            $_SESSION['flash_error'] = 'ไม่พบหมวดหมู่รายจ่าย';
            Router::redirect('/admin/category-items');
            return;
        }

        // Get all items for parent selection (exclude self and descendants)
        $parents = BudgetCategoryItem::getAll(true, false);
        
        View::render('admin/category-items/form', [
            'title' => 'แก้ไขหมวดหมู่รายจ่าย',
            'currentPage' => 'admin-category-items',
            'mode' => 'edit',
            'item' => $item,
            'parents' => $parents,
            'auth' => $user,
        ], 'main');
    }

    public function update(int $id)
    {
        $user = $this->checkAdmin();
        $data = $_POST;
        
        // Prevent self-parent
        if (!empty($data['parent_id']) && $data['parent_id'] == $id) {
            $_SESSION['flash_error'] = 'หมวดหมู่แม่ต้องไม่ใช่ตัวเอง';
            Router::redirect("/admin/category-items/{$id}/edit");
            return;
        }

        $errors = $this->validate($data, $id);
        
        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode(', ', $errors);
            $_SESSION['form_data'] = $data;
            Router::redirect("/admin/category-items/{$id}/edit");
            return;
        }

        try {
            // Calculate level based on parent
            $level = 0;
            if (!empty($data['parent_id'])) {
                $parent = BudgetCategoryItem::find($data['parent_id']);
                $level = $parent ? (int)$parent['level'] + 1 : 0;
            }

            BudgetCategoryItem::update($id, [
                'name' => trim($data['name']),
                'code' => trim($data['code']),
                'parent_id' => !empty($data['parent_id']) ? (int)$data['parent_id'] : null,
                'level' => $level,
                'description' => trim($data['description'] ?? ''),
                'sort_order' => (int)($data['sort_order'] ?? 0),
                'is_active' => isset($data['is_active']) ? 1 : 0,
                'updated_by' => $user['id'],
            ]);

            $_SESSION['flash_success'] = 'แก้ไขหมวดหมู่รายจ่ายสำเร็จ';
            Router::redirect('/admin/category-items');
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error: ' . $e->getMessage();
            Router::redirect("/admin/category-items/{$id}/edit");
        }
    }

    public function destroy(int $id)
    {
        $this->checkAdmin();
        
        // Soft delete
        BudgetCategoryItem::softDelete($id);
        
        $_SESSION['flash_success'] = 'ลบหมวดหมู่รายจ่ายสำเร็จ';
        Router::redirect('/admin/category-items');
    }

    public function restore(int $id)
    {
        $this->checkAdmin();
        
        BudgetCategoryItem::restore($id);
        
        $_SESSION['flash_success'] = 'กู้คืนหมวดหมู่รายจ่ายสำเร็จ';
        Router::redirect('/admin/category-items?show_deleted=1');
    }

    public function toggleActive(int $id)
    {
        $this->checkAdmin();
        
        BudgetCategoryItem::toggleActive($id);
        
        $_SESSION['flash_success'] = 'เปลี่ยนสถานะสำเร็จ';
        Router::redirect('/admin/category-items');
    }

    private function validate(array $data, ?int $excludeId = null): array
    {
        $errors = [];
        
        if (empty($data['name'])) {
            $errors[] = 'กรุณาระบุชื่อหมวดหมู่';
        }
        
        if (empty($data['code'])) {
            $errors[] = 'กรุณาระบุรหัสหมวดหมู่';
        }
        
        return $errors;
    }
}
