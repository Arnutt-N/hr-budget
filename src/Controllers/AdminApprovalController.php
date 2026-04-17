<?php
/**
 * Admin Approval Controller
 * Handles configuration of approval workflows
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Core\Router;
use App\Models\ApprovalSetting;
use App\Models\Approver;
use App\Models\User;
use App\Models\Organization;

class AdminApprovalController
{
    /**
     * Show approval settings page
     */
    public static function index()
    {
        Auth::require();
        
        // Strict Check: Only Super Admin
        if (!Auth::hasRole('super_admin')) {
            $_SESSION['flash_error'] = 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้';
            Router::redirect('/');
            return;
        }
        
        $isEnabled = ApprovalSetting::isEnabled('budget_request_approval');
        $approvers = Approver::all();
        $users = User::all(); // For adding new approver
        $organizations = Organization::all(); // For referencing orgs
        
        View::render('admin/approvals/index', [
            'isEnabled' => $isEnabled,
            'approvers' => $approvers,
            'users' => $users,
            'organizations' => $organizations,
            'currentPage' => 'admin_approvals',
            'title' => 'ตั้งค่าการอนุมัติ'
        ], 'main');
    }
    
    /**
     * Toggle approval workflow
     */
    public static function toggle()
    {
        Auth::require();
        if (!Auth::hasRole('super_admin')) {
             echo json_encode(['success' => false, 'error' => 'Unauthorized']);
             return;
        }
        
        $enabled = isset($_POST['enabled']) && $_POST['enabled'] === 'true';
        
        if (ApprovalSetting::setEnabled('budget_request_approval', $enabled)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    }
    
    /**
     * Add approver
     */
    public static function addApprover()
    {
        Auth::require();
        if (!Auth::hasRole('super_admin')) {
             Router::redirect('/admin/approvals');
             return;
        }
        
        $userId = $_POST['user_id'] ?? null;
        $orgId = $_POST['org_id'] ?? null;
        
        if (!$userId || !$orgId) {
            $_SESSION['flash_error'] = 'กรุณาระบุผู้ใช้งานและหน่วยงาน';
            Router::redirect('/admin/approvals');
            return;
        }
        
        if (Approver::add((int)$userId, (int)$orgId)) {
            $_SESSION['flash_success'] = 'เพิ่มผู้อนุมัติเรียบร้อยแล้ว';
        } else {
            $_SESSION['flash_error'] = 'เกิดข้อผิดพลาด หรือผู้อนุมัตินี้มีอยู่แล้ว';
        }
        
        Router::redirect('/admin/approvals');
    }
    
    /**
     * Remove approver
     */
    public static function removeApprover(int $id)
    {
        Auth::require();
        if (!Auth::hasRole('super_admin')) {
             Router::redirect('/admin/approvals');
             return;
        }
        
        if (Approver::remove($id)) {
            $_SESSION['flash_success'] = 'ลบผู้อนุมัติเรียบร้อยแล้ว';
        } else {
            $_SESSION['flash_error'] = 'เกิดข้อผิดพลาดในการลบ';
        }
        
        Router::redirect('/admin/approvals');
    }
}
