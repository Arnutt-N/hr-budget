<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Core\Router;
use App\Models\BudgetTarget;
use App\Models\TargetType;
use App\Models\Organization;
use App\Models\BudgetCategory;

class BudgetTargetController
{
    private function checkAuth()
    {
        Auth::require();
        // Maybe checks specific permission, or just admin?
        // Let's assume admin or specific role. For now, check admin for setup.
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
        $user = $this->checkAuth();
        
        $filters = [
            'fiscal_year' => $_GET['year'] ?? (date('Y') + 543), // Default to Thai year
            'target_type_id' => $_GET['type'] ?? '',
        ];

        $targets = BudgetTarget::all($filters);
        $types = TargetType::all();
        
        View::render('budgets/targets/index', [
            'title' => 'ตั้งค่าเป้าหมายงบประมาณ',
            'currentPage' => 'budget-targets',
            'targets' => $targets,
            'types' => $types,
            'filters' => $filters,
            'auth' => $user,
        ], 'main');
    }

    public function create()
    {
        $user = $this->checkAuth();
        
        $types = TargetType::all();
        $orgs = Organization::getForSelect();
        $categories = BudgetCategory::getForSelect();

        View::render('budgets/targets/form', [
            'title' => 'เพิ่มเป้าหมายงบประมาณ',
            'currentPage' => 'budget-targets',
            'mode' => 'create',
            'target' => null,
            'types' => $types,
            'orgs' => $orgs,
            'categories' => $categories,
            'auth' => $user,
        ], 'main');
    }

    public function store()
    {
        $this->checkAuth();
        $data = $_POST;
        
        // Basic validation ...
        
        try {
            BudgetTarget::create([
                'target_type_id' => $data['target_type_id'],
                'fiscal_year' => $data['fiscal_year'],
                'quarter' => !empty($data['quarter']) ? $data['quarter'] : null,
                'organization_id' => !empty($data['organization_id']) ? $data['organization_id'] : null,
                'category_id' => !empty($data['category_id']) ? $data['category_id'] : null,
                'target_percent' => !empty($data['target_percent']) ? $data['target_percent'] : null,
                'target_amount' => !empty($data['target_amount']) ? str_replace(',', '', $data['target_amount']) : null,
                'notes' => $data['notes'] ?? '',
                'created_by' => Auth::user()['id'] ?? null
            ]);

            $_SESSION['flash_success'] = 'บันทึกเรียบร้อย';
            Router::redirect('/budgets/targets?year=' . $data['fiscal_year']);
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error: ' . $e->getMessage();
            $_SESSION['form_data'] = $data;
            Router::redirect('/budgets/targets/create');
        }
    }

    public function edit(int $id)
    {
        $user = $this->checkAuth();
        $target = BudgetTarget::find($id);
        
        if (!$target) {
            Router::redirect('/budgets/targets');
            return;
        }
        
        $types = TargetType::all();
        $orgs = Organization::getForSelect();
        $categories = BudgetCategory::getForSelect();

        View::render('budgets/targets/form', [
            'title' => 'แก้ไขเป้าหมาย',
            'currentPage' => 'budget-targets',
            'mode' => 'edit',
            'target' => $target,
            'types' => $types,
            'orgs' => $orgs,
            'categories' => $categories,
            'auth' => $user,
        ], 'main');
    }

    public function update(int $id)
    {
        $this->checkAuth();
        $data = $_POST;
        
        try {
            BudgetTarget::update($id, [
                'target_type_id' => $data['target_type_id'],
                'fiscal_year' => $data['fiscal_year'],
                'quarter' => !empty($data['quarter']) ? $data['quarter'] : null,
                'organization_id' => !empty($data['organization_id']) ? $data['organization_id'] : null,
                'category_id' => !empty($data['category_id']) ? $data['category_id'] : null,
                'target_percent' => !empty($data['target_percent']) ? $data['target_percent'] : null,
                'target_amount' => !empty($data['target_amount']) ? str_replace(',', '', $data['target_amount']) : null,
                'notes' => $data['notes'] ?? ''
            ]);

            $_SESSION['flash_success'] = 'แก้ไขเรียบร้อย';
            Router::redirect('/budgets/targets?year=' . $data['fiscal_year']);
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error: ' . $e->getMessage();
            Router::redirect("/budgets/targets/{$id}/edit");
        }
    }

    public function destroy(int $id)
    {
        $this->checkAuth();
        BudgetTarget::delete($id);
        $_SESSION['flash_success'] = 'ลบเรียบร้อย';
        Router::redirect('/budgets/targets'); // Return to list. Ideally preserve filters.
    }
}
