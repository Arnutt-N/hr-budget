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
        
        // Handle filters
        $type = $_GET['type'] ?? '';
        $region = $_GET['region'] ?? '';
        
        if (!empty($type)) {
            $organizations = Organization::getByType($type, false);
            // No flattening needed if filtered, just list them
        } elseif (!empty($region)) {
            $organizations = Organization::getByRegion($region, false);
        } else {
            // Default tree view
            $tree = Organization::getTree(false);
            $organizations = $this->flattenTree($tree);
        }

        View::render('admin/organizations/index', [
            'title' => 'จัดการโครงสร้างหน่วยงาน',
            'currentPage' => 'admin-organizations',
            'organizations' => $organizations,
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
        $parents = Organization::getForSelect();

        View::render('admin/organizations/form', [
            'title' => 'เพิ่มหน่วยงานใหม่',
            'currentPage' => 'admin-organizations',
            'mode' => 'create',
            'organization' => null,
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

    public function edit(int $id)
    {
        $user = $this->checkAdmin();
        $org = Organization::find($id);
        
        if (!$org) {
            $_SESSION['flash_error'] = 'ไม่พบหน่วยงาน';
            Router::redirect('/admin/organizations');
            return;
        }

        $parents = Organization::getForSelect();

        View::render('admin/organizations/form', [
            'title' => 'แก้ไขหน่วยงาน',
            'currentPage' => 'admin-organizations',
            'mode' => 'edit',
            'organization' => $org,
            'parents' => $parents,
            'auth' => $user,
        ], 'main');
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
