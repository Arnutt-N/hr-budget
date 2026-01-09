<?php
namespace App\Models;

use App\Core\Database;

class FundSource
{
    public static function all()
    {
        return Database::query("SELECT * FROM fund_sources ORDER BY sort_order ASC, id ASC");
    }

    public static function find($id)
    {
        $result = Database::query("SELECT * FROM fund_sources WHERE id = ?", [$id]);
        return $result[0] ?? null;
    }

    public static function findByCode($code)
    {
        $result = Database::query("SELECT * FROM fund_sources WHERE code = ?", [$code]);
        return $result[0] ?? null;
    }

    public static function create($data)
    {
        $sql = "INSERT INTO fund_sources (code, name_th, name_en, parent_id, level, sort_order) 
                VALUES (:code, :name_th, :name_en, :parent_id, :level, :sort_order)";
        
        Database::query($sql, [
            ':code' => $data['code'],
            ':name_th' => $data['name_th'],
            ':name_en' => $data['name_en'] ?? null,
            ':parent_id' => $data['parent_id'] ?? null,
            ':level' => $data['level'] ?? 0,
            ':sort_order' => $data['sort_order'] ?? 0
        ]);

        return Database::lastInsertId();
    }
}
