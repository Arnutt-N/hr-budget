<?php
namespace App\Models;

use App\Core\Database;

class Plan
{
    protected static $table = 'plans';
    
    public static function all(bool $activeOnly = true): array
    {
        $sql = "SELECT * FROM plans";
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY sort_order ASC, name_th ASC";
        return Database::query($sql);
    }
    
    public static function find(int $id): ?array
    {
        $result = Database::query("SELECT * FROM plans WHERE id = ?", [$id]);
        return $result[0] ?? null;
    }
    
    public static function where(string $column, $value): \App\Core\SimpleQueryBuilder
    {
        $builder = new \App\Core\SimpleQueryBuilder(self::$table);
        return $builder->where($column, $value);
    }
    
    public static function getByFiscalYear(int $fiscalYear, bool $activeOnly = true): array
    {
        $sql = "SELECT * FROM plans WHERE fiscal_year = ?";
        if ($activeOnly) {
            $sql .= " AND is_active = 1";
        }
        $sql .= " ORDER BY sort_order ASC, name_th ASC";
        return Database::query($sql, [$fiscalYear]);
    }
}

