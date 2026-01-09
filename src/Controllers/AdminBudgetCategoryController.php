<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Core\Router;
use App\Models\BudgetCategory;

class AdminBudgetCategoryController
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
        
        // Get all categories flattened for table display, or tree?
        // Let's get tree to show hierarchy conceptually, or just all() and sort in view?
        // The model has getTree(). Let's use getTree() and maybe flatten it with depth for the table.
        // Actually, let's use a helper to flatten the tree for display with indentation
        $tree = BudgetCategory::getTree(false); // false = include inactive
        $categories = $this->flattenTree($tree);

        View::render('admin/categories/index', [
            'title' => 'จัดการหมวดหมู่งบประมาณ',
            'currentPage' => 'admin-categories',
            'categories' => $categories,
            'auth' => $user,
        ], 'main');
    }

    private function flattenTree(array $nodes, int $depth = 0): array
    {
        $result = [];
        foreach ($nodes as $node) {
            $node['depth'] = $depth;
            $children = $node['children'] ?? [];
            unset($node['children']);
            $result[] = $node;
            if (!empty($children)) {
                $result = array_merge($result, $this->flattenTree($children, $depth + 1));
            }
        }
        return $result;
    }

    public function create()
    {
        $user = $this->checkAdmin();
        $parents = BudgetCategory::getForSelect(false);

        View::render('admin/categories/form', [
            'title' => 'เพิ่มหมวดหมู่ใหม่',
            'currentPage' => 'admin-categories',
            'mode' => 'create',
            'category' => null,
            'parents' => $parents,
            'auth' => $user,
        ], 'main');
    }

    public function store()
    {
        $this->checkAdmin();

        $data = $_POST;
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode(', ', $errors);
            $_SESSION['form_data'] = $data;
            Router::redirect('/admin/categories/create');
            return;
        }

        try {
            BudgetCategory::create([
                'code' => trim($data['code']),
                'name_th' => trim($data['name_th']),
                'name_en' => trim($data['name_en'] ?? ''),
                'description' => trim($data['description'] ?? ''),
                'parent_id' => !empty($data['parent_id']) ? $data['parent_id'] : null,
                'level' => $this->calculateLevel($data['parent_id'] ?? null),
                'sort_order' => (int) ($data['sort_order'] ?? 0),
                'is_active' => isset($data['is_active']) ? 1 : 0,
                'is_plan' => isset($data['is_plan']) ? 1 : 0,
                'plan_name' => trim($data['plan_name'] ?? ''),
            ]);

            $_SESSION['flash_success'] = 'เพิ่มหมวดหมู่สำเร็จ';
            Router::redirect('/admin/categories');
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error: ' . $e->getMessage();
            $_SESSION['form_data'] = $data;
            Router::redirect('/admin/categories/create');
        }
    }

    public function edit(int $id)
    {
        $user = $this->checkAdmin();
        $category = BudgetCategory::find($id);
        
        if (!$category) {
            $_SESSION['flash_error'] = 'ไม่พบหมวดหมู่';
            Router::redirect('/admin/categories');
            return;
        }

        $parents = BudgetCategory::getForSelect(false);
        // Remove self and children from parents option to prevent cycles
        // For simplicity, we just pass all, but validation should prevent cyclic parent.
        // Or in view, disable selection of self/children.

        View::render('admin/categories/form', [
            'title' => 'แก้ไขหมวดหมู่',
            'currentPage' => 'admin-categories',
            'mode' => 'edit',
            'category' => $category,
            'parents' => $parents,
            'auth' => $user,
        ], 'main');
    }

    public function update(int $id)
    {
        $this->checkAdmin();
        $data = $_POST;
        
        // Remove self parent check logic here or in validate
        if (!empty($data['parent_id']) && $data['parent_id'] == $id) {
             $_SESSION['flash_error'] = 'หมวดหมู่แม่ต้องไม่ใช่ตัวเอง';
             Router::redirect("/admin/categories/{$id}/edit");
             return;
        }

        $errors = $this->validate($data, $id);

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode(', ', $errors);
            $_SESSION['form_data'] = $data;
            Router::redirect("/admin/categories/{$id}/edit");
            return;
        }

        try {
            BudgetCategory::update($id, [
                'code' => trim($data['code']),
                'name_th' => trim($data['name_th']),
                'name_en' => trim($data['name_en'] ?? ''),
                'description' => trim($data['description'] ?? ''),
                'parent_id' => !empty($data['parent_id']) ? $data['parent_id'] : null,
                'level' => $this->calculateLevel($data['parent_id'] ?? null),
                'sort_order' => (int) ($data['sort_order'] ?? 0),
                'is_active' => isset($data['is_active']) ? 1 : 0,
                'is_plan' => isset($data['is_plan']) ? 1 : 0,
                'plan_name' => trim($data['plan_name'] ?? ''),
            ]);

            $_SESSION['flash_success'] = 'แก้ไขหมวดหมู่สำเร็จ';
            Router::redirect('/admin/categories');
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error: ' . $e->getMessage();
            Router::redirect("/admin/categories/{$id}/edit");
        }
    }

    public function destroy(int $id)
    {
        $this->checkAdmin();
        // Check if has children?
        // Soft delete handles it, but maybe warn?
        // Logic: just delete.
        BudgetCategory::delete($id);
        $_SESSION['flash_success'] = 'ลบหมวดหมู่สำเร็จ';
        Router::redirect('/admin/categories');
    }

    private function validate(array $data, ?int $excludeId = null): array
    {
        $errors = [];
        if (empty($data['code'])) $errors[] = 'ระบุรหัสหมวดหมู่';
        if (empty($data['name_th'])) $errors[] = 'ระบุชื่อภาษาไทย';
        
        $existing = BudgetCategory::findByCode($data['code']);
        if ($existing && $existing['id'] != $excludeId) {
            $errors[] = 'รหัสซ้ำกับที่มีอยู่แล้ว';
        }
        
        return $errors;
    }

    private function calculateLevel(?int $parentId): int
    {
        if (!$parentId) return 0; // Root = 0 (in our logic, actually Root cat is level 0, children 1. If parent is selected, level = parent.level + 1)
        
        $parent = BudgetCategory::find($parentId);
        return $parent ? (int)$parent['level'] + 1 : 0;
    }
}
