<?php
/**
 * File Controller (Document Archive)
 * 
 * Handles document management organized by fiscal year and budget categories
 */

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Core\Router;
use App\Models\File;
use App\Models\Folder;
use App\Models\FiscalYear;

class FileController
{
    /**
     * Document Archive main page
     */
    public static function index()
    {
        Auth::require();
        
        $fiscalYear = (int)($_GET['year'] ?? FiscalYear::currentYear());
        $folderId = isset($_GET['folder']) ? (int)$_GET['folder'] : null;
        
        // Get user info for org filtering
        $user = Auth::user();
        $userOrgId = $user['organization_id'] ?? null;
        $isAdmin = Auth::hasRole('admin');
        
        // Get available years
        $availableYears = Folder::getAvailableYears();
        if (empty($availableYears)) {
            // Initialize folders for current year
            Folder::initializeForYear($fiscalYear, Auth::id());
            $availableYears = [['fiscal_year' => $fiscalYear]];
        }
        
        // Current folder or root
        $currentFolder = $folderId ? Folder::find($folderId) : null;
        $breadcrumb = $folderId ? Folder::getBreadcrumb($folderId) : [];
        
        // Access control: Only validate when a folder is selected
        if ($currentFolder) {
            [$hasAccess, $accessError] = \App\Helpers\FileValidator::canAccessFolder($currentFolder, $userOrgId, $isAdmin);
            if (!$hasAccess) {
                $_SESSION['error'] = $accessError;
                http_response_code(403);
                Router::redirect('/files?year=' . $fiscalYear);
                return;
            }
        }
        
        // Get folders and files
        if ($folderId) {
            $folders = Folder::getSubfolders($folderId);
            $files = File::getByFolder($folderId);
        } else {
            // Apply org filtering for root folders
            $folders = Folder::getRootFolders($fiscalYear, $userOrgId, $isAdmin);
            $files = [];
        }

        // Get organizations for Admin dropdown (Cascading: Department -> Division)
        $departments = [];
        $divisions = [];
        
        if ($isAdmin) {
            $allOrgs = \App\Models\Organization::all();
            foreach ($allOrgs as $org) {
                if ($org['level'] == 1) {
                    $departments[] = $org;
                } else {
                    $divisions[] = $org;
                }
            }
        }
        
        // Folder tree for sidebar (filtered by org)
        $folderTree = Folder::getTree($fiscalYear, $userOrgId, $isAdmin);
        
        // Determine if user can upload/create
        $canUpload = false;
        if ($isAdmin) {
            // Admin can always create/upload
            $canUpload = true;
        } elseif ($currentFolder) {
            // User can upload to their own org or Central folder (if allowed)
            $folderOrgId = $currentFolder['organization_id'];
            $canUpload = ($folderOrgId !== null && $folderOrgId == $userOrgId);
        }
        
        View::render('files/index', [
            'fiscalYear' => $fiscalYear,
            'availableYears' => $availableYears,
            'folders' => $folders,
            'folderTree' => $folderTree,
            'currentFolder' => $currentFolder,
            'breadcrumb' => $breadcrumb,
            'files' => $files,
            'canUpload' => $canUpload,
            'isAdmin' => $isAdmin,
            'userOrgId' => $userOrgId,
            'currentPage' => 'files',
            'departments' => $departments,
            'divisions' => $divisions,
            'title' => 'คลังเอกสาร'
        ], 'main');
    }

    /**
     * Handle file upload
     */
    public static function upload()
    {
        Auth::require();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['file'])) {
            Router::redirect('/files');
            return;
        }

        $folderId = (int)$_POST['folder_id'];
        if (!$folderId) {
            $_SESSION['error'] = 'กรุณาเลือกโฟลเดอร์ก่อนอัปโหลด';
            Router::redirect('/files');
            return;
        }
        
        $description = $_POST['description'] ?? null;
        
        $result = File::upload($_FILES['file'], $folderId, Auth::id(), $description);
        
        if (is_string($result)) {
            $_SESSION['error'] = $result;
        } else {
            $_SESSION['success'] = 'อัปโหลดไฟล์สำเร็จ';
        }
        
        Router::redirect('/files?folder=' . $folderId);
    }

    /**
     * Download file
     */
    public static function download(int $id)
    {
        Auth::require();
        
        $file = File::find($id);
        if (!$file) {
            Router::redirect('/files');
            return;
        }
        
        $fullPath = BASE_PATH . '/public/' . $file['file_path'];
        if (!file_exists($fullPath)) {
            $_SESSION['error'] = 'ไม่พบไฟล์';
            Router::redirect('/files?folder=' . $file['folder_id']);
            return;
        }
        
        header('Content-Type: ' . ($file['mime_type'] ?? 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . $file['original_name'] . '"');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }

    /**
     * Delete file
     */
    public static function deleteFile(int $id)
    {
        Auth::require();
        
        $file = File::find($id);
        $folderId = $file ? $file['folder_id'] : null;
        
        if (File::delete($id)) {
            $_SESSION['success'] = 'ลบไฟล์สำเร็จ';
        } else {
            $_SESSION['error'] = 'ไม่สามารถลบไฟล์ได้';
        }
        
        Router::redirect('/files' . ($folderId ? "?folder=$folderId" : ''));
    }

    /**
     * Create folder
     */
    public static function createFolder()
    {
        Auth::require();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['name'])) {
            Router::redirect('/files');
            return;
        }
        
        $parentId = isset($_POST['parent_id']) && $_POST['parent_id'] !== '' ? (int)$_POST['parent_id'] : null;
        $fiscalYear = (int)($_POST['fiscal_year'] ?? FiscalYear::currentYear());
        $organizationId = null;
        
        // If parent folder, inherit fiscal year and organization_id from it
        if ($parentId) {
            $parent = Folder::find($parentId);
            if ($parent) {
                $fiscalYear = $parent['fiscal_year'];
                $organizationId = $parent['organization_id'];
            }
        } else {
            // Root folder creation
            if (Auth::hasRole('admin')) {
                // Admin can specify organization (from cascading dropdown)
                $organizationId = !empty($_POST['organization_id']) ? (int)$_POST['organization_id'] : null;
            } else {
                // Regular users create in their own org
                $organizationId = Auth::user()['organization_id'];
            }
        }
        
        Folder::create([
            'name' => $_POST['name'],
            'parent_id' => $parentId,
            'fiscal_year' => $fiscalYear,
            'organization_id' => $organizationId,
            'description' => $_POST['description'] ?? null,
            'is_system' => 0,
            'created_by' => Auth::id()
        ]);
        
        $_SESSION['success'] = 'สร้างโฟลเดอร์สำเร็จ';
        Router::redirect('/files' . ($parentId ? "?folder=$parentId" : "?year=$fiscalYear"));
    }

    /**
     * Delete folder
     */
    public static function deleteFolder(int $id)
    {
        Auth::require();
        
        $folder = Folder::find($id);
        
        if ($folder && $folder['is_system']) {
            $_SESSION['error'] = 'ไม่สามารถลบโฟลเดอร์ระบบได้';
        } elseif (Folder::delete($id)) {
            $_SESSION['success'] = 'ลบโฟลเดอร์สำเร็จ';
        } else {
            $_SESSION['error'] = 'ไม่สามารถลบโฟลเดอร์ได้';
        }
        
        $parentId = $folder ? $folder['parent_id'] : null;
        $year = $folder ? $folder['fiscal_year'] : FiscalYear::currentYear();
        
        Router::redirect('/files' . ($parentId ? "?folder=$parentId" : "?year=$year"));
    }

    /**
     * Initialize folders for a fiscal year
     */
    public static function initializeYear()
    {
        Auth::require();
        
        $year = (int)($_POST['year'] ?? FiscalYear::currentYear());
        $created = Folder::initializeForYear($year, Auth::id());
        
        $_SESSION['success'] = "สร้างโฟลเดอร์สำหรับปี $year เรียบร้อย ($created โฟลเดอร์)";
        Router::redirect('/files?year=' . $year);
    }
}
