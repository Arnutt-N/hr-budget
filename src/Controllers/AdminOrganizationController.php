<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Core\Router;
use App\Models\Organization;

class AdminOrganizationController
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
        
        // Get dropdown options
        $ministries = Organization::getMinistries(false);
        $departments = Organization::getDepartments(false);
        $regionLabels = Organization::getRegionLabels();
        
        // Build filter array from GET params
        $filters = [];
        if (!empty($_GET['ministry_id'])) $filters['ministry_id'] = $_GET['ministry_id'];
        if (!empty($_GET['department_id'])) $filters['department_id'] = $_GET['department_id'];
        if (!empty($_GET['region'])) $filters['region'] = $_GET['region'];
        if (!empty($_GET['search'])) $filters['search'] = trim($_GET['search']);
        
        // Get organizations using search
        if (!empty($filters)) {
            $organizations = Organization::search($filters);
        } else {
            // Default: show all
            $organizations = Organization::all(false);
        }

        View::render('admin/organizations/index', [
            'title' => 'จัดการโครงสร้างหน่วยงาน',
            'currentPage' => 'admin-organizations',
            'organizations' => $organizations,
            'ministries' => $ministries,
            'departments' => $departments,
            'regionLabels' => $regionLabels,
            'filters' => $filters,
            'auth' => $user,
        ], 'main');
    }

    private function flattenTree(array $nodes, int $depth = 0): array
    {
        $result = [];
        foreach ($nodes as $node) {
            $node['depth'] = $depth;
            $children = $node['children'] ?? [];
            $node['has_children'] = !empty($children); // Flag for UI
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
        $parents = Organization::getForSelect();
        
        // Detect AJAX request for Modal
        $isAjax = !empty($_GET['ajax']);
        $layout = $isAjax ? '' : 'main';

        View::render('admin/organizations/form', [
            'title' => 'เพิ่มหน่วยงานใหม่',
            'currentPage' => 'admin-organizations',
            'mode' => 'create',
            'organization' => null,
            'parents' => $parents,
            'auth' => $user,
        ], $layout);
    }

    public function store()
    {
        $this->checkAdmin();
        $data = $_POST;
        $errors = $this->validate($data);

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode(', ', $errors);
            $_SESSION['form_data'] = $data;
            Router::redirect('/admin/organizations/create');
            return;
        }

        try {
            Organization::create([
                'code' => trim($data['code']),
                'name_th' => trim($data['name_th']),
                'abbreviation' => trim($data['abbreviation'] ?? ''),
                'parent_id' => !empty($data['parent_id']) ? $data['parent_id'] : null,
                'level' => $this->calculateLevel($data['parent_id'] ?? null),
                'budget_allocated' => (float) str_replace(',', '', $data['budget_allocated'] ?? '0'),
                'sort_order' => (int) ($data['sort_order'] ?? 0),
                'is_active' => isset($data['is_active']) ? 1 : 0,
                // New fields
                'org_type' => $data['org_type'] ?? 'division',
                'region' => $data['region'] ?? 'central',
                'province_code' => !empty($data['province_code']) ? trim($data['province_code']) : null,
                'provincial_group' => trim($data['provincial_group'] ?? ''),
                'provincial_zone' => trim($data['provincial_zone'] ?? ''),
                'inspection_zone' => trim($data['inspection_zone'] ?? ''),
                'custom_zone' => trim($data['custom_zone'] ?? ''),
                'contact_phone' => trim($data['contact_phone'] ?? ''),
                'contact_email' => trim($data['contact_email'] ?? ''),
                'address' => trim($data['address'] ?? ''),
            ]);

            $_SESSION['flash_success'] = 'เพิ่มหน่วยงานสำเร็จ';
            Router::redirect('/admin/organizations');
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error: ' . $e->getMessage();
            $_SESSION['form_data'] = $data;
            Router::redirect('/admin/organizations/create');
        }
    }

    public function show(int $id)
    {
        $user = $this->checkAdmin();
        $org = Organization::find($id);
        
        if (!$org) {
            if (!empty($_GET['ajax'])) {
                echo '<div class="p-4 text-red-500">ไม่พบข้อมูลหน่วยงาน</div>';
                return;
            }
            $_SESSION['flash_error'] = 'ไม่พบหน่วยงาน';
            Router::redirect('/admin/organizations');
            return;
        }

        $parent = $org['parent_id'] ? Organization::find($org['parent_id']) : null;

        // Detect AJAX request for Modal
        $isAjax = !empty($_GET['ajax']);
        $layout = $isAjax ? '' : 'main';

        View::render('admin/organizations/show', [
            'organization' => $org,
            'parent' => $parent,
            'auth' => $user,
        ], $layout);
    }

    public function edit(int $id)
    {
        $user = $this->checkAdmin();
        $org = Organization::find($id);
        
        if (!$org) {
            if (!empty($_GET['ajax'])) {
                echo '<div class="p-4 text-red-500">ไม่พบข้อมูลหน่วยงาน</div>';
                return;
            }
            $_SESSION['flash_error'] = 'ไม่พบหน่วยงาน';
            Router::redirect('/admin/organizations');
            return;
        }

        $parents = Organization::getForSelect();

        // Detect AJAX request for Modal
        $isAjax = !empty($_GET['ajax']);
        $layout = $isAjax ? '' : 'main';

        View::render('admin/organizations/form', [
            'title' => 'แก้ไขหน่วยงาน',
            'currentPage' => 'admin-organizations',
            'mode' => 'edit',
            'organization' => $org,
            'parents' => $parents,
            'auth' => $user,
        ], $layout);
    }

    public function update(int $id)
    {
        $this->checkAdmin();
        $data = $_POST;
        
        if (!empty($data['parent_id']) && $data['parent_id'] == $id) {
             $_SESSION['flash_error'] = 'หน่วยงานแม่ต้องไม่ใช่ตัวเอง';
             Router::redirect("/admin/organizations/{$id}/edit");
             return;
        }

        $errors = $this->validate($data, $id);

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode(', ', $errors);
            $_SESSION['form_data'] = $data;
            Router::redirect("/admin/organizations/{$id}/edit");
            return;
        }

        try {
            Organization::update($id, [
                'code' => trim($data['code']),
                'name_th' => trim($data['name_th']),
                'abbreviation' => trim($data['abbreviation'] ?? ''),
                'parent_id' => !empty($data['parent_id']) ? $data['parent_id'] : null,
                'level' => $this->calculateLevel($data['parent_id'] ?? null),
                'budget_allocated' => (float) str_replace(',', '', $data['budget_allocated'] ?? '0'),
                'sort_order' => (int) ($data['sort_order'] ?? 0),
                'is_active' => isset($data['is_active']) ? 1 : 0,
                // New fields
                'org_type' => $data['org_type'] ?? 'division',
                'region' => $data['region'] ?? 'central',
                'province_code' => !empty($data['province_code']) ? trim($data['province_code']) : null,
                'provincial_group' => trim($data['provincial_group'] ?? ''),
                'provincial_zone' => trim($data['provincial_zone'] ?? ''),
                'inspection_zone' => trim($data['inspection_zone'] ?? ''),
                'custom_zone' => trim($data['custom_zone'] ?? ''),
                'contact_phone' => trim($data['contact_phone'] ?? ''),
                'contact_email' => trim($data['contact_email'] ?? ''),
                'address' => trim($data['address'] ?? ''),
            ]);

            $_SESSION['flash_success'] = 'แก้ไขหน่วยงานสำเร็จ';
            Router::redirect('/admin/organizations');
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error: ' . $e->getMessage();
            Router::redirect("/admin/organizations/{$id}/edit");
        }
    }

    public function destroy(int $id)
    {
        $this->checkAdmin();
        
        // Safety check: Cannot delete if has children
        if (Organization::countChildren($id) > 0) {
            $_SESSION['flash_error'] = 'ไม่สามารถลบหน่วยงานนี้ได้เนื่องจากมีหน่วยงานย่อยภายใต้สังกัด';
            Router::redirect('/admin/organizations');
            return;
        }
        
        Organization::delete($id);
        $_SESSION['flash_success'] = 'ลบหน่วยงานสำเร็จ';
        Router::redirect('/admin/organizations');
    }

    private function validate(array $data, ?int $excludeId = null): array
    {
        $errors = [];
        if (empty($data['code'])) $errors[] = 'ระบุรหัสหน่วยงาน';
        if (empty($data['name_th'])) $errors[] = 'ระบุชื่อหน่วยงาน';
        
        $existing = Organization::findByCode($data['code']);
        if ($existing && $existing['id'] != $excludeId) {
            $errors[] = 'รหัสซ้ำกับที่มีอยู่แล้ว';
        }
        
        return $errors;
    }

    private function calculateLevel(?int $parentId): int
    {
        if (!$parentId) return 0;
        $parent = Organization::find($parentId);
        return $parent ? (int)$parent['level'] + 1 : 0;
    }
}
