<?php
/**
 * Folder Model (Document Archive)
 * 
 * Manages folder structure organized by fiscal year and budget categories
 */

namespace App\Models;

use App\Core\Database;

class Folder
{
    /**
     * Get root folders for a fiscal year
     * @param int $fiscalYear Fiscal year to filter
     * @param int|null $userOrgId User's organization ID (null = admin sees all)
     * @param bool $isAdmin Whether user is admin
     */
    public static function getRootFolders(int $fiscalYear, ?int $userOrgId = null, bool $isAdmin = false): array
    {
        $sql = "SELECT f.*, u.name as created_by_name, o.name_th as organization_name,
                    (SELECT COUNT(*) FROM folders WHERE parent_id = f.id) as subfolder_count,
                    (SELECT COUNT(*) FROM files WHERE folder_id = f.id) as file_count
             FROM folders f 
             LEFT JOIN users u ON f.created_by = u.id 
             LEFT JOIN organizations o ON f.organization_id = o.id
             WHERE f.fiscal_year = ? AND f.parent_id IS NULL";
        
        $params = [$fiscalYear];
        
        // Non-admin users see only: Central folder (org_id IS NULL) + their own org
        if (!$isAdmin && $userOrgId !== null) {
            $sql .= " AND (f.organization_id IS NULL OR f.organization_id = ?)";
            $params[] = $userOrgId;
        }
        
        $sql .= " ORDER BY f.organization_id IS NULL DESC, f.is_system DESC, f.name ASC";
        
        return Database::query($sql, $params);
    }

    /**
     * Get subfolders of a folder
     */
    public static function getSubfolders(int $parentId): array
    {
        return Database::query(
            "SELECT f.*, u.name as created_by_name,
                    (SELECT COUNT(*) FROM folders WHERE parent_id = f.id) as subfolder_count,
                    (SELECT COUNT(*) FROM files WHERE folder_id = f.id) as file_count
             FROM folders f 
             LEFT JOIN users u ON f.created_by = u.id 
             WHERE f.parent_id = ?
             ORDER BY f.is_system DESC, f.name ASC",
            [$parentId]
        );
    }

    /**
     * Get folder tree as nested array
     * @param int $fiscalYear Fiscal year to filter
     * @param int|null $userOrgId User's organization ID (null = admin sees all)
     * @param bool $isAdmin Whether user is admin
     */
    public static function getTree(int $fiscalYear, ?int $userOrgId = null, bool $isAdmin = false): array
    {
        $sql = "SELECT * FROM folders WHERE fiscal_year = ?";
        $params = [$fiscalYear];
        
        // Non-admin users see only: Central folder (org_id IS NULL) + their own org
        if (!$isAdmin && $userOrgId !== null) {
            $sql .= " AND (organization_id IS NULL OR organization_id = ?)";
            $params[] = $userOrgId;
        }
        
        $sql .= " ORDER BY organization_id IS NULL DESC, is_system DESC, name ASC";
        
        $all = Database::query($sql, $params);
        return self::buildTree($all);
    }

    /**
     * Build tree from flat array
     */
    private static function buildTree(array $folders, ?int $parentId = null): array
    {
        $tree = [];
        foreach ($folders as $folder) {
            if ($folder['parent_id'] == $parentId) {
                $folder['children'] = self::buildTree($folders, $folder['id']);
                $tree[] = $folder;
            }
        }
        return $tree;
    }

    /**
     * Find folder by ID
     */
    public static function find(int $id): ?array
    {
        return Database::queryOne(
            "SELECT f.*, u.name as created_by_name 
             FROM folders f 
             LEFT JOIN users u ON f.created_by = u.id 
             WHERE f.id = ?",
            [$id]
        );
    }

    /**
     * Create new folder
     */
    public static function create(array $data): int
    {
        // Build folder path
        $path = $data['name'];
        if (!empty($data['parent_id'])) {
            $parent = self::find($data['parent_id']);
            if ($parent) {
                $path = $parent['folder_path'] . '/' . $data['name'];
            }
        } elseif (!empty($data['fiscal_year'])) {
            $path = $data['fiscal_year'] . '/' . $data['name'];
        }

        return Database::insert('folders', [
            'name' => $data['name'],
            'fiscal_year' => $data['fiscal_year'] ?? null,
            'organization_id' => $data['organization_id'] ?? null,
            'budget_category_id' => $data['budget_category_id'] ?? null,
            'parent_id' => $data['parent_id'] ?? null,
            'folder_path' => $path,
            'description' => $data['description'] ?? null,
            'is_system' => $data['is_system'] ?? 0,
            'created_by' => $data['created_by']
        ]);
    }

    /**
     * Update folder
     */
    public static function update(int $id, array $data): bool
    {
        $allowed = ['name', 'description'];
        $updateData = array_intersect_key($data, array_flip($allowed));
        
        if (empty($updateData)) return false;
        
        return Database::update('folders', $updateData, 'id = ?', [$id]) > 0;
    }

    /**
     * Delete folder (cascades to subfolders and files)
     */
    public static function delete(int $id): bool
    {
        $folder = self::find($id);
        if (!$folder || $folder['is_system']) {
            return false; // Cannot delete system folders
        }
        return Database::delete('folders', 'id = ?', [$id]) > 0;
    }

    /**
     * Get breadcrumb path for a folder (Optimized with CTE)
     */
    public static function getBreadcrumb(int $id): array
    {
        // Recursive Common Table Expression (CTE) to fetch hierarchy in one query
        $sql = "
            WITH RECURSIVE folder_path (id, name, parent_id, fiscal_year, depth) AS (
                SELECT id, name, parent_id, fiscal_year, 0
                FROM folders 
                WHERE id = ?
                
                UNION ALL
                
                SELECT f.id, f.name, f.parent_id, f.fiscal_year, fp.depth + 1
                FROM folders f
                INNER JOIN folder_path fp ON f.id = fp.parent_id
            )
            SELECT * FROM folder_path ORDER BY depth DESC
        ";

        try {
            return Database::query($sql, [$id]);
        } catch (\Exception $e) {
            // Fallback for MySQL versions that might not support CTE or connection issues
            $path = [];
            $current = self::find($id);
            
            while ($current) {
                array_unshift($path, $current);
                if ($current['parent_id']) {
                    $current = self::find($current['parent_id']);
                } else {
                    break;
                }
            }
            return $path;
        }
    }

    /**
     * Get available fiscal years that have folders
     */
    public static function getAvailableYears(): array
    {
        return Database::query(
            "SELECT DISTINCT fiscal_year FROM folders 
             WHERE fiscal_year IS NOT NULL 
             ORDER BY fiscal_year DESC"
        );
    }

    /**
     * Initialize folder structure for a fiscal year based on Organizations
     * Creates: 1 Central folder + 1 folder per organization
     */
    public static function initializeForYear(int $fiscalYear, int $createdBy): int
    {
        $created = 0;
        
        // 1. Create Central folder (organization_id = NULL)
        $existingCentral = Database::queryOne(
            "SELECT id FROM folders WHERE fiscal_year = ? AND organization_id IS NULL AND parent_id IS NULL AND name = ?",
            [$fiscalYear, 'ส่วนกลาง']
        );
        
        if (!$existingCentral) {
            self::create([
                'name' => 'ส่วนกลาง',
                'fiscal_year' => $fiscalYear,
                'organization_id' => null,
                'is_system' => 1,
                'description' => 'โฟลเดอร์ส่วนกลาง สำหรับเอกสารที่ทุกหน่วยงานเข้าถึงได้',
                'created_by' => $createdBy
            ]);
            $created++;
        }
        
        // 2. Create folder for each organization
        $organizations = \App\Models\Organization::all();
        
        foreach ($organizations as $org) {
            // Check if already exists
            $existing = Database::queryOne(
                "SELECT id FROM folders WHERE fiscal_year = ? AND organization_id = ? AND parent_id IS NULL",
                [$fiscalYear, $org['id']]
            );
            
            if (!$existing) {
                self::create([
                    'name' => $org['name'],
                    'fiscal_year' => $fiscalYear,
                    'organization_id' => $org['id'],
                    'is_system' => 1,
                    'created_by' => $createdBy
                ]);
                $created++;
            }
        }
        
        return $created;
    }
    
    /**
     * Get folders by organization
     */
    public static function getByOrganization(int $orgId, int $fiscalYear): array
    {
        return Database::query(
            "SELECT f.*, u.name as created_by_name,
                    (SELECT COUNT(*) FROM folders WHERE parent_id = f.id) as subfolder_count,
                    (SELECT COUNT(*) FROM files WHERE folder_id = f.id) as file_count
             FROM folders f 
             LEFT JOIN users u ON f.created_by = u.id 
             WHERE f.organization_id = ? AND f.fiscal_year = ?
             ORDER BY f.parent_id IS NULL DESC, f.name ASC",
            [$orgId, $fiscalYear]
        );
    }
}
