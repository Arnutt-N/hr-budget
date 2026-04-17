<?php
namespace App\Models;

use App\Core\Database;

class Organization
{
    // Type Constants
    public const TYPE_MINISTRY = 'ministry';
    public const TYPE_DEPARTMENT = 'department';
    public const TYPE_DIVISION = 'division';
    public const TYPE_SECTION = 'section';
    public const TYPE_PROVINCE = 'province';
    public const TYPE_OFFICE = 'office';

    // Region Constants
    public const REGION_CENTRAL = 'central';
    public const REGION_REGIONAL = 'regional';
    public const REGION_PROVINCIAL = 'provincial';
    public const REGION_CENTRAL_IN_REGION = 'central_in_region';

    public static function all(bool $activeOnly = true): array
    {
        $sql = "SELECT * FROM organizations";
        $params = [];
        
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        
        $sql .= " ORDER BY level ASC, sort_order ASC, code ASC";
        
        return Database::query($sql, $params);
    }

    public static function getTree(bool $activeOnly = true): array
    {
        $orgs = self::all($activeOnly);
        return self::buildTree($orgs);
    }

    private static function buildTree(array $orgs, ?int $parentId = null): array
    {
        $tree = [];
        foreach ($orgs as $org) {
            if ($org['parent_id'] == $parentId) {
                $children = self::buildTree($orgs, $org['id']);
                if ($children) {
                    $org['children'] = $children;
                }
                $tree[] = $org;
            }
        }
        return $tree;
    }

    public static function find(int $id): ?array
    {
        $result = Database::query("SELECT * FROM organizations WHERE id = ?", [$id]);
        return $result[0] ?? null;
    }

    public static function findByCode(string $code): ?array
    {
        $result = Database::query("SELECT * FROM organizations WHERE code = ?", [$code]);
        return $result[0] ?? null;
    }

    /**
     * Get organizations by type
     */
    public static function getByType(string $type, bool $activeOnly = true): array
    {
        $sql = "SELECT * FROM organizations WHERE org_type = ?";
        if ($activeOnly) {
            $sql .= " AND is_active = 1";
        }
        $sql .= " ORDER BY sort_order ASC, name_th ASC";
        
        return Database::query($sql, [$type]);
    }

    /**
     * Get organizations by region
     */
    public static function getByRegion(string $region, bool $activeOnly = true): array
    {
        $sql = "SELECT * FROM organizations WHERE region = ?";
        if ($activeOnly) {
            $sql .= " AND is_active = 1";
        }
        $sql .= " ORDER BY level ASC, sort_order ASC, name_th ASC";
        
        return Database::query($sql, [$region]);
    }

    public static function create(array $data): int
    {
        // Add defaults
        if (!isset($data['budget_allocated'])) $data['budget_allocated'] = 0;
        if (!isset($data['sort_order'])) $data['sort_order'] = 0;
        if (!isset($data['is_active'])) $data['is_active'] = 1;
        
        // Ensure new fields have defaults or valid values
        if (!isset($data['org_type'])) $data['org_type'] = self::TYPE_DIVISION;
        if (!isset($data['region'])) $data['region'] = self::REGION_CENTRAL;
        
        return Database::insert('organizations', $data);
    }

    public static function update(int $id, array $data): bool
    {
        // Allowed columns list extended
        $allowed = [
            'parent_id', 'code', 'name_th', 'abbreviation', 
            'budget_allocated', 'level', 'sort_order', 'is_active',
            'org_type', 'province_code', 'region', 
            'provincial_group', 'provincial_zone', 'inspection_zone', 'custom_zone',
            'contact_phone', 'contact_email', 'address'
        ];
        
        $updateData = array_intersect_key($data, array_flip($allowed));
        
        if (empty($updateData)) return false;
        
        return Database::update('organizations', $updateData, 'id = ?', [$id]) > 0;
    }

    public static function delete(int $id): bool
    {
        return Database::delete('organizations', 'id = ?', [$id]) > 0;
    }

    public static function countChildren(int $id): int
    {
        $result = Database::query("SELECT COUNT(*) as count FROM organizations WHERE parent_id = ?", [$id]);
        return (int) ($result[0]['count'] ?? 0);
    }
    
    public static function getForSelect(): array
    {
        $orgs = self::all();
        $options = [];
        foreach ($orgs as $org) {
             $prefix = str_repeat('— ', (int) $org['level']);
             // Add type label if available (requires enhancement to all() or using view, 
             // but for now keeping it simple to avoid breaking existing calls)
             $options[] = [
                 'id' => $org['id'],
                 'name' => $prefix . $org['name_th'] . ($org['abbreviation'] ? " ({$org['abbreviation']})" : ""),
                 'level' => $org['level'],
                 'org_type' => $org['org_type'] ?? 'division'
             ];
        }
        return $options;
    }

    public static function getTypeLabels(): array
    {
        return [
            self::TYPE_MINISTRY => 'กระทรวง',
            self::TYPE_DEPARTMENT => 'กรม',
            self::TYPE_DIVISION => 'กอง/สำนัก',
            self::TYPE_SECTION => 'กลุ่มงาน',
            self::TYPE_PROVINCE => 'จังหวัด',
            self::TYPE_OFFICE => 'ส่วนราชการ'
        ];
    }

    public static function getRegionLabels(): array
    {
        return [
            self::REGION_CENTRAL => 'ส่วนกลาง',
            self::REGION_REGIONAL => 'ภูมิภาค',
            self::REGION_PROVINCIAL => 'จังหวัด',
            self::REGION_CENTRAL_IN_REGION => 'ส่วนกลางที่ตั้งอยู่ในภูมิภาค'
        ];
    }

    /**
     * Get all ministries (for dropdown)
     */
    public static function getMinistries(bool $activeOnly = true): array
    {
        return self::getByType(self::TYPE_MINISTRY, $activeOnly);
    }

    /**
     * Get all departments (for dropdown)
     */
    public static function getDepartments(bool $activeOnly = true): array
    {
        return self::getByType(self::TYPE_DEPARTMENT, $activeOnly);
    }

    /**
     * Search organizations with multiple filters
     */
    public static function search(array $filters = []): array
    {
        $sql = "SELECT * FROM organizations";
        $where = [];
        $params = [];

        // Filter by region
        if (!empty($filters['region'])) {
            $where[] = "region = ?";
            $params[] = $filters['region'];
        }

        // Search by text (name or code)
        if (!empty($filters['search'])) {
            $where[] = "(name_th LIKE ? OR code LIKE ? OR abbreviation LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Filter by ministry (parent hierarchy search - simplified)
        if (!empty($filters['ministry_id'])) {
            // Find all descendants of this ministry
            $where[] = "(id = ? OR parent_id = ? OR id IN (
                SELECT o2.id FROM organizations o2
                LEFT JOIN organizations p1 ON o2.parent_id = p1.id
                LEFT JOIN organizations p2 ON p1.parent_id = p2.id
                WHERE p1.id = ? OR p2.id = ?
            ))";
            $params[] = $filters['ministry_id'];
            $params[] = $filters['ministry_id'];
            $params[] = $filters['ministry_id'];
            $params[] = $filters['ministry_id'];
        }

        // Filter by department (parent hierarchy search - simplified)
        if (!empty($filters['department_id'])) {
            $where[] = "(id = ? OR parent_id = ? OR id IN (
                SELECT o2.id FROM organizations o2
                WHERE o2.parent_id = ?
            ))";
            $params[] = $filters['department_id'];
            $params[] = $filters['department_id'];
            $params[] = $filters['department_id'];
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY level ASC, sort_order ASC, code ASC";

        return Database::query($sql, $params);
    }
}
