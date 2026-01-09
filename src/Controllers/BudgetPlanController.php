<?php
/**
 * Budget Plan Controller (MVP)
 * 
 * Basic CRUD for managing Budget Plans (แผนงาน/ผลผลิต/กิจกรรม/โครงการ)
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Core\Router;
use App\Models\BudgetPlan;
use App\Models\Organization;
use App\Models\FiscalYear;

class BudgetPlanController
{
    /**
     * List all plans
     */
    public function index(): void
    {
        Auth::require();
        
        if (!$this->isAdmin()) {
            $_SESSION['flash_error'] = 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้';
            Router::redirect('/dashboard');
            return;
        }
        
        $fiscalYear = (int) ($_GET['year'] ?? FiscalYear::currentYear());
        $plans = BudgetPlan::all($fiscalYear);
        $fiscalYears = FiscalYear::getForSelect();
        
        View::render('admin/plans/index', [
            'title' => 'จัดการแผนงาน/โครงการ',
            'currentPage' => 'admin-plans',
            'fiscalYear' => $fiscalYear,
            'fiscalYears' => $fiscalYears,
            'plans' => $plans,
            'auth' => Auth::user(),
        ], 'main');
    }
    
    /**
     * Show create form
     */
    public function create(): void
    {
        Auth::require();
        
        if (!$this->isAdmin()) {
            $_SESSION['flash_error'] = 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้';
            Router::redirect('/dashboard');
            return;
        }
        
        $fiscalYear = (int) ($_GET['year'] ?? FiscalYear::currentYear());
        $plans = BudgetPlan::all($fiscalYear);
        $organizations = Organization::getForSelect();
        
        View::render('admin/plans/form', [
            'title' => 'เพิ่มแผนงาน/โครงการใหม่',
            'currentPage' => 'admin-plans',
            'mode' => 'create',
            'plan' => null,
            'fiscalYear' => $fiscalYear,
            'plans' => $plans,
            'organizations' => $organizations,
            'auth' => Auth::user(),
        ], 'main');
    }
    
    /**
     * Store new plan
     */
    public function store(): void
    {
        Auth::require();
        
        if (!$this->isAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        // Validate
        $errors = $this->validate($_POST);
        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode(', ', $errors);
            $_SESSION['form_data'] = $_POST;
            Router::redirect('/admin/plans/create');
            return;
        }
        
        // Auto-calculate level from parent
        $level = 1;
        $parentId = !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null;
        if ($parentId) {
            $parent = BudgetPlan::find($parentId);
            if ($parent) {
                $level = $parent['level'] + 1;
            }
        }
        
        // Create
        try {
            BudgetPlan::create([
                'fiscal_year' => (int) $_POST['fiscal_year'],
                'code' => trim($_POST['code']),
                'name_th' => trim($_POST['name_th']),
                'name_en' => trim($_POST['name_en'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'plan_type' => $_POST['plan_type'],
                'parent_id' => $parentId,
                'division_id' => !empty($_POST['division_id']) ? (int) $_POST['division_id'] : null,
                'level' => $level,
                'sort_order' => (int) ($_POST['sort_order'] ?? 0)
            ]);
            
            $_SESSION['flash_success'] = 'เพิ่มแผนงาน/โครงการสำเร็จ';
            Router::redirect('/admin/plans');
            
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
            $_SESSION['form_data'] = $_POST;
            Router::redirect('/admin/plans/create');
        }
    }
    
    /**
     * Show edit form
     */
    public function edit(int $id): void
    {
        Auth::require();
        
        if (!$this->isAdmin()) {
            $_SESSION['flash_error'] = 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้';
            Router::redirect('/dashboard');
            return;
        }
        
        $plan = BudgetPlan::find($id);
        
        if (!$plan) {
            $_SESSION['flash_error'] = 'ไม่พบแผนงาน/โครงการ';
            Router::redirect('/admin/plans');
            return;
        }
        
        $fiscalYear = $plan['fiscal_year'];
        $plans = BudgetPlan::all($fiscalYear);
        $organizations = Organization::getForSelect();
        
        View::render('admin/plans/form', [
            'title' => 'แก้ไขแผนงาน/โครงการ',
            'currentPage' => 'admin-plans',
            'mode' => 'edit',
            'plan' => $plan,
            'fiscalYear' => $fiscalYear,
            'plans' => $plans,
            'organizations' => $organizations,
            'auth' => Auth::user(),
        ], 'main');
    }
    
    /**
     * Update plan
     */
    public function update(int $id): void
    {
        Auth::require();
        
        if (!$this->isAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $plan = BudgetPlan::find($id);
        if (!$plan) {
            $_SESSION['flash_error'] = 'ไม่พบแผนงาน/โครงการ';
            Router::redirect('/admin/plans');
            return;
        }
        
        // Validate
        $errors = $this->validate($_POST, $id);
        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode(', ', $errors);
            $_SESSION['form_data'] = $_POST;
            Router::redirect("/admin/plans/{$id}/edit");
            return;
        }
        
        // Auto-calculate level from parent
        $level = 1;
        $parentId = !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null;
        
        // Prevent self as parent
        if ($parentId === $id) {
            $_SESSION['flash_error'] = 'ไม่สามารถเลือกตัวเองเป็น parent ได้';
            $_SESSION['form_data'] = $_POST;
            Router::redirect("/admin/plans/{$id}/edit");
            return;
        }
        
        if ($parentId) {
            $parent = BudgetPlan::find($parentId);
            if ($parent) {
                $level = $parent['level'] + 1;
            }
        }
        
        // Update
        try {
            \App\Core\Database::update('plans', [
                'fiscal_year' => (int) $_POST['fiscal_year'],
                'code' => trim($_POST['code']),
                'name_th' => trim($_POST['name_th']),
                'name_en' => trim($_POST['name_en'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'plan_type' => $_POST['plan_type'],
                'parent_id' => $parentId,
                'division_id' => !empty($_POST['division_id']) ? (int) $_POST['division_id'] : null,
                'level' => $level,
                'sort_order' => (int) ($_POST['sort_order'] ?? 0)
            ], 'id = ?', [$id]);
            
            $_SESSION['flash_success'] = 'แก้ไขแผนงาน/โครงการสำเร็จ';
            Router::redirect('/admin/plans');
            
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
            $_SESSION['form_data'] = $_POST;
            Router::redirect("/admin/plans/{$id}/edit");
        }
    }
    
    /**
     * Delete plan
     */
    public function destroy(int $id): void
    {
        Auth::require();
        
        if (!$this->isAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $plan = BudgetPlan::find($id);
        if (!$plan) {
            $_SESSION['flash_error'] = 'ไม่พบแผนงาน/โครงการ';
            Router::redirect('/admin/plans');
            return;
        }
        
        try {
            \App\Core\Database::delete('plans', 'id = ?', [$id]);
            $_SESSION['flash_success'] = 'ลบแผนงาน/โครงการสำเร็จ';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'ไม่สามารถลบได้: ' . $e->getMessage();
        }
        
        Router::redirect('/admin/plans');
    }
    
    /**
     * Validate input
     */
    private function validate(array $data, ?int $excludeId = null): array
    {
        $errors = [];
        
        if (empty($data['fiscal_year'])) {
            $errors[] = 'กรุณาเลือกปีงบประมาณ';
        }
        
        if (empty($data['code'])) {
            $errors[] = 'กรุณาระบุรหัสแผนงาน/โครงการ';
        } elseif (strlen($data['code']) > 50) {
            $errors[] = 'รหัสต้องไม่เกิน 50 ตัวอักษร';
        } else {
            // Check unique per fiscal year
            $existing = \App\Core\Database::query(
                "SELECT * FROM plans WHERE code = ? AND fiscal_year = ?",
                [$data['code'], $data['fiscal_year']]
            );
            if (!empty($existing) && $existing[0]['id'] !== $excludeId) {
                $errors[] = 'รหัสซ้ำในปีงบประมาณนี้';
            }
        }
        
        if (empty($data['name_th'])) {
            $errors[] = 'กรุณาระบุชื่อแผนงาน/โครงการ';
        } elseif (strlen($data['name_th']) > 500) {
            $errors[] = 'ชื่อต้องไม่เกิน 500 ตัวอักษร';
        }
        
        if (empty($data['plan_type'])) {
            $errors[] = 'กรุณาเลือกประเภท';
        } elseif (!in_array($data['plan_type'], ['program', 'output', 'activity', 'project'])) {
            $errors[] = 'ประเภทไม่ถูกต้อง';
        }
        
        return $errors;
    }
    
    /**
     * Check if current user is admin
     */
    private function isAdmin(): bool
    {
        $user = Auth::user();
        return $user && isset($user['role']) && $user['role'] === 'admin';
    }
}
