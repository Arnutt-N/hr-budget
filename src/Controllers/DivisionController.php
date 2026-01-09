<?php
/**
 * Division Controller (MVP)
 * 
 * Basic CRUD for managing Divisions (หน่วยงาน/กอง/สำนัก)
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Core\Router;
use App\Models\Division;

class DivisionController
{
    /**
     * List all divisions
     */
    public function index(): void
    {
        Auth::require();
        
        // Admin check
        if (!$this->isAdmin()) {
            $_SESSION['flash_error'] = 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้';
            Router::redirect('/dashboard');
            return;
        }
        
        $divisions = Division::all();
        
        View::render('admin/divisions/index', [
            'title' => 'จัดการหน่วยงาน',
            'currentPage' => 'admin-divisions',
            'divisions' => $divisions,
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
        
        View::render('admin/divisions/form', [
            'title' => 'เพิ่มหน่วยงานใหม่',
            'currentPage' => 'admin-divisions',
            'mode' => 'create',
            'division' => null,
            'auth' => Auth::user(),
        ], 'main');
    }
    
    /**
     * Store new division
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
            Router::redirect('/admin/divisions/create');
            return;
        }
        
        // Create
        try {
            Division::create([
                'code' => trim($_POST['code']),
                'name_th' => trim($_POST['name_th']),
                'name_en' => trim($_POST['name_en'] ?? ''),
                'short_name' => trim($_POST['short_name'] ?? ''),
                'type' => $_POST['type'] ?? 'central',
                'sort_order' => (int) ($_POST['sort_order'] ?? 0)
            ]);
            
            $_SESSION['flash_success'] = 'เพิ่มหน่วยงานสำเร็จ';
            Router::redirect('/admin/divisions');
            
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
            $_SESSION['form_data'] = $_POST;
            Router::redirect('/admin/divisions/create');
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
        
        $division = Division::find($id);
        
        if (!$division) {
            $_SESSION['flash_error'] = 'ไม่พบหน่วยงาน';
            Router::redirect('/admin/divisions');
            return;
        }
        
        View::render('admin/divisions/form', [
            'title' => 'แก้ไขหน่วยงาน',
            'currentPage' => 'admin-divisions',
            'mode' => 'edit',
            'division' => $division,
            'auth' => Auth::user(),
        ], 'main');
    }
    
    /**
     * Update division
     */
    public function update(int $id): void
    {
        Auth::require();
        
        if (!$this->isAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $division = Division::find($id);
        if (!$division) {
            $_SESSION['flash_error'] = 'ไม่พบหน่วยงาน';
            Router::redirect('/admin/divisions');
            return;
        }
        
        // Validate
        $errors = $this->validate($_POST, $id);
        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode(', ', $errors);
            $_SESSION['form_data'] = $_POST;
            Router::redirect("/admin/divisions/{$id}/edit");
            return;
        }
        
        // Update
        try {
            \App\Core\Database::update('divisions', [
                'code' => trim($_POST['code']),
                'name_th' => trim($_POST['name_th']),
                'name_en' => trim($_POST['name_en'] ?? ''),
                'short_name' => trim($_POST['short_name'] ?? ''),
                'type' => $_POST['type'] ?? 'central',
                'sort_order' => (int) ($_POST['sort_order'] ?? 0)
            ], 'id = ?', [$id]);
            
            $_SESSION['flash_success'] = 'แก้ไขหน่วยงานสำเร็จ';
            Router::redirect('/admin/divisions');
            
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
            $_SESSION['form_data'] = $_POST;
            Router::redirect("/admin/divisions/{$id}/edit");
        }
    }
    
    /**
     * Delete division
     */
    public function destroy(int $id): void
    {
        Auth::require();
        
        if (!$this->isAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $division = Division::find($id);
        if (!$division) {
            $_SESSION['flash_error'] = 'ไม่พบหน่วยงาน';
            Router::redirect('/admin/divisions');
            return;
        }
        
        try {
            \App\Core\Database::delete('divisions', 'id = ?', [$id]);
            $_SESSION['flash_success'] = 'ลบหน่วยงานสำเร็จ';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'ไม่สามารถลบได้: ' . $e->getMessage();
        }
        
        Router::redirect('/admin/divisions');
    }
    
    /**
     * Validate input
     */
    private function validate(array $data, ?int $excludeId = null): array
    {
        $errors = [];
        
        if (empty($data['code'])) {
            $errors[] = 'กรุณาระบุรหัสหน่วยงาน';
        } elseif (strlen($data['code']) > 20) {
            $errors[] = 'รหัสหน่วยงานต้องไม่เกิน 20 ตัวอักษร';
        } else {
            // Check unique
            $existing = Division::findByCode($data['code']);
            if ($existing && $existing['id'] !== $excludeId) {
                $errors[] = 'รหัสหน่วยงานซ้ำ';
            }
        }
        
        if (empty($data['name_th'])) {
            $errors[] = 'กรุณาระบุชื่อหน่วยงาน';
        } elseif (strlen($data['name_th']) > 255) {
            $errors[] = 'ชื่อหน่วยงานต้องไม่เกิน 255 ตัวอักษร';
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
