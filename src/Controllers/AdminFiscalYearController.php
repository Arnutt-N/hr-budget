<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Core\Router;
use App\Models\FiscalYear;

class AdminFiscalYearController
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
        $years = FiscalYear::all();

        View::render('admin/fiscal-years/index', [
            'title' => 'จัดการปีงบประมาณ',
            'currentPage' => 'admin-fiscal-years',
            'years' => $years,
            'auth' => $user,
        ], 'main');
    }

    public function create()
    {
        $user = $this->checkAdmin();

        View::render('admin/fiscal-years/form', [
            'title' => 'เพิ่มปีงบประมาณ',
            'currentPage' => 'admin-fiscal-years',
            'mode' => 'create',
            'year' => null,
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
            Router::redirect('/admin/fiscal-years/create');
            return;
        }

        try {
            // Calculate start and end dates (Thai fiscal year: Oct 1 - Sep 30)
            $yearBE = (int) $data['year'];
            $yearCE = $yearBE - 543;
            
            FiscalYear::create([
                'year' => $yearBE,
                'start_date' => ($yearCE - 1) . '-10-01',
                'end_date' => $yearCE . '-09-30',
                'is_current' => isset($data['is_current']) ? 1 : 0,
                'is_closed' => isset($data['is_closed']) ? 1 : 0,
            ]);

            // If set as current, update others
            if (isset($data['is_current'])) {
                $this->ensureOnlyOneCurrent($yearBE);
            }

            $_SESSION['flash_success'] = 'เพิ่มปีงบประมาณสำเร็จ';
            Router::redirect('/admin/fiscal-years');
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error: ' . $e->getMessage();
            $_SESSION['form_data'] = $data;
            Router::redirect('/admin/fiscal-years/create');
        }
    }

    public function edit(int $id)
    {
        $user = $this->checkAdmin();
        $year = FiscalYear::findById($id);
        
        if (!$year) {
            $_SESSION['flash_error'] = 'ไม่พบปีงบประมาณ';
            Router::redirect('/admin/fiscal-years');
            return;
        }

        View::render('admin/fiscal-years/form', [
            'title' => 'แก้ไขปีงบประมาณ',
            'currentPage' => 'admin-fiscal-years',
            'mode' => 'edit',
            'year' => $year,
            'auth' => $user,
        ], 'main');
    }

    public function update(int $id)
    {
        $this->checkAdmin();
        $data = $_POST;
        $errors = $this->validate($data, $id);

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode(', ', $errors);
            Router::redirect("/admin/fiscal-years/{$id}/edit");
            return;
        }

        try {
            // Calculate start and end dates
            $yearBE = (int) $data['year'];
            $yearCE = $yearBE - 543;
            
            FiscalYear::update($id, [
                'year' => $yearBE,
                'start_date' => ($yearCE - 1) . '-10-01',
                'end_date' => $yearCE . '-09-30',
                'is_current' => isset($data['is_current']) ? 1 : 0,
                'is_closed' => isset($data['is_closed']) ? 1 : 0,
            ]);

            // If set as current, update others
            if (isset($data['is_current'])) {
                $this->ensureOnlyOneCurrent($yearBE);
            }

            $_SESSION['flash_success'] = 'แก้ไขปีงบประมาณสำเร็จ';
            Router::redirect('/admin/fiscal-years');
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error: ' . $e->getMessage();
            Router::redirect("/admin/fiscal-years/{$id}/edit");
        }
    }

    public function destroy(int $id)
    {
        $this->checkAdmin();
        FiscalYear::delete($id);
        $_SESSION['flash_success'] = 'ลบปีงบประมาณสำเร็จ';
        Router::redirect('/admin/fiscal-years');
    }

    public function setCurrent(int $id)
    {
        $this->checkAdmin();
        $year = FiscalYear::findById($id);
        
        if (!$year) {
            $_SESSION['flash_error'] = 'ไม่พบปีงบประมาณ';
            Router::redirect('/admin/fiscal-years');
            return;
        }

        // Set this year as current
        FiscalYear::update($id, ['is_current' => 1]);
        
        // Ensure only one current
        $this->ensureOnlyOneCurrent($year['year']);

        $_SESSION['flash_success'] = 'ตั้งปีงบประมาณปัจจุบันสำเร็จ';
        Router::redirect('/admin/fiscal-years');
    }

    public function toggleClosed(int $id)
    {
        $this->checkAdmin();
        FiscalYear::toggleClosed($id);
        $_SESSION['flash_success'] = 'เปลี่ยนสถานะปีงบประมาณสำเร็จ';
        Router::redirect('/admin/fiscal-years');
    }

    private function validate(array $data, ?int $excludeId = null): array
    {
        $errors = [];
        
        if (empty($data['year'])) {
            $errors[] = 'กรุณาระบุปีงบประมาณ';
        } else {
            $year = (int) $data['year'];
            
            // Validate year range
            if ($year < 2500 || $year > 2600) {
                $errors[] = 'ปีงบประมาณต้องอยู่ในช่วง พ.ศ. 2500-2600';
            }
            
            // Check duplicate
            if (FiscalYear::yearExists($year, $excludeId)) {
                $errors[] = 'ปีงบประมาณนี้มีอยู่แล้ว';
            }
        }
        
        return $errors;
    }

    private function ensureOnlyOneCurrent(int $currentYear): void
    {
        // Reset all others to not current
        $allYears = FiscalYear::all();
        foreach ($allYears as $y) {
            if ($y['year'] != $currentYear && $y['is_current']) {
                FiscalYear::update($y['id'], ['is_current' => 0]);
            }
        }
    }
}
