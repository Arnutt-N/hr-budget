<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use App\Core\SimpleQueryBuilder;

class Activity extends Model {
    protected $table = 'activities';
    protected $fillable = [
        'project_id', 'plan_id', 'code', 'name_th', 'name_en', 'description',
        'fiscal_year', 'sort_order', 'is_active',
        'deleted_at', 'created_by', 'updated_by'
    ];

    public static function getAllActive(bool $activeOnly = true): array
    {
        $sql = "SELECT * FROM activities";
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY sort_order ASC, name_th ASC";
        return Database::query($sql);
    }

    public static function find($id)
    {
        $result = Database::query("SELECT * FROM activities WHERE id = ?", [$id]);
        return $result[0] ?? null;
    }

    public static function where(string $column, $value): SimpleQueryBuilder
    {
        $builder = new SimpleQueryBuilder('activities');
        return $builder->where($column, $value);
    }
}
