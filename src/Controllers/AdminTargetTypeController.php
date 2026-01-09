<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Core\Router;
use App\Models\TargetType;

class AdminTargetTypeController
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
        $types = TargetType::all(false); // get all including inactive

        View::render('admin/target-types/index', [
            'title' => 'จัดการประเภทเป้าหมาย',
            'currentPage' => 'admin-target-types',
            'types' => $types,
            'auth' => $user,
        ], 'main');
    }

    public function create()
    {
        $user = $this->checkAdmin();

        View::render('admin/target-types/form', [
            'title' => 'เพิ่มประเภทเป้าหมาย',
            'currentPage' => 'admin-target-types',
            'mode' => 'create',
            'type' => null,
            'auth' => $user,
        ], 'main');
    }

    public function store()
    {
        $this->checkAdmin();
        $data = $_POST;
        
        // Simple validation
        if (empty($data['code']) || empty($data['name_th'])) {
            $_SESSION['flash_error'] = 'กรุณาระบุรหัสและชื่อประเภท';
            $_SESSION['form_data'] = $data;
            Router::redirect('/admin/target-types/create');
            return;
        }

        try {
            TargetType::create([
                'code' => trim($data['code']),
                'name_th' => trim($data['name_th']),
                'description' => trim($data['description'] ?? ''),
                'sort_order' => (int)($data['sort_order'] ?? 0),
                'is_active' => isset($data['is_active']) ? 1 : 0
            ]);

            $_SESSION['flash_success'] = 'เพิ่มสำเร็จ';
            Router::redirect('/admin/target-types');
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            Router::redirect('/admin/target-types/create');
        }
    }

    public function edit(int $id)
    {
        $user = $this->checkAdmin();
        $type = TargetType::find($id);

        if (!$type) {
            Router::redirect('/admin/target-types');
            return;
        }

        View::render('admin/target-types/form', [
            'title' => 'แก้ไขประเภทเป้าหมาย',
            'currentPage' => 'admin-target-types',
            'mode' => 'edit',
            'type' => $type,
            'auth' => $user,
        ], 'main');
    }

    public function update(int $id)
    {
        $this->checkAdmin();
        $data = $_POST;

        try {
            TargetType::update($id, [
                'code' => trim($data['code']),
                'name_th' => trim($data['name_th']),
                'description' => trim($data['description'] ?? ''),
                'sort_order' => (int)($data['sort_order'] ?? 0),
                'is_active' => isset($data['is_active']) ? 1 : 0
            ]);

            $_SESSION['flash_success'] = 'แก้ไขสำเร็จ';
            Router::redirect('/admin/target-types');
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            Router::redirect("/admin/target-types/{$id}/edit");
        }
    }

    public function destroy(int $id)
    {
        $this->checkAdmin();
        TargetType::delete($id);
        $_SESSION['flash_success'] = 'ลบสำเร็จ';
        Router::redirect('/admin/target-types');
    }
}
