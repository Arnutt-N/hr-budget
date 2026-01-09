<?php
namespace App\Models;

use App\Core\Database;

class BudgetPlan
{
    public static function all($fiscal_year = 2568)
    {
        return Database::query("SELECT * FROM plans WHERE fiscal_year = ? ORDER BY sort_order ASC, id ASC", [$fiscal_year]);
    }

    public static function find($id)
    {
        $result = Database::query("SELECT * FROM plans WHERE id = ?", [$id]);
        return $result[0] ?? null;
    }

    public static function getHierarchy($fiscal_year = 2568)
    {
        // Get all items
        $items = self::all($fiscal_year);
        
        // Build tree
        $tree = [];
        $refs = [];
        
        foreach ($items as $item) {
            $thisRef = &$refs[$item['id']];
            $thisRef['data'] = $item;
            $thisRef['children'] = [];

            if ($item['parent_id'] == null) {
                $tree[$item['id']] = &$thisRef;
            } else {
                $refs[$item['parent_id']]['children'][] = &$thisRef;
            }
        }

        return $tree;
    }

    public static function where(string $column, $value)
    {
        return Database::query("SELECT * FROM plans WHERE $column = ? ORDER BY sort_order ASC, id ASC", [$value]);
    }

    public static function create($data)
    {
        $sql = "INSERT INTO plans (fiscal_year, code, name_th, name_en, description, plan_type, parent_id, division_id, level, sort_order) 
                VALUES (:fiscal_year, :code, :name_th, :name_en, :description, :plan_type, :parent_id, :division_id, :level, :sort_order)";
        
        Database::query($sql, [
            ':fiscal_year' => $data['fiscal_year'] ?? 2568,
            ':code' => $data['code'],
            ':name_th' => $data['name_th'],
            ':name_en' => $data['name_en'] ?? null,
            ':description' => $data['description'] ?? null,
            ':plan_type' => $data['plan_type'],
            ':parent_id' => $data['parent_id'] ?? null,
            ':division_id' => $data['division_id'] ?? null,
            ':level' => $data['level'] ?? 1,
            ':sort_order' => $data['sort_order'] ?? 0
        ]);

        return Database::lastInsertId();
    }
}
