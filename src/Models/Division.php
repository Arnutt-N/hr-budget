<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Division
{
    public static function all()
    {
        return Database::query("SELECT * FROM divisions ORDER BY sort_order ASC, id ASC");
    }

    public static function find($id)
    {
        $result = Database::query("SELECT * FROM divisions WHERE id = ?", [$id]);
        return $result[0] ?? null;
    }

    public static function findByCode($code)
    {
        $result = Database::query("SELECT * FROM divisions WHERE code = ?", [$code]);
        return $result[0] ?? null;
    }

    public static function create($data)
    {
        $sql = "INSERT INTO divisions (code, name_th, name_en, short_name, parent_id, type, sort_order) 
                VALUES (:code, :name_th, :name_en, :short_name, :parent_id, :type, :sort_order)";
        
        Database::query($sql, [
            ':code' => $data['code'],
            ':name_th' => $data['name_th'],
            ':name_en' => $data['name_en'] ?? null,
            ':short_name' => $data['short_name'] ?? null,
            ':parent_id' => $data['parent_id'] ?? null,
            ':type' => $data['type'] ?? 'central',
            ':sort_order' => $data['sort_order'] ?? 0
        ]);

        return Database::lastInsertId();
    }
}
