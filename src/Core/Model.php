<?php
namespace App\Core;

class Model
{
    protected $table;
    protected $fillable = [];

    /**
     * Get table name from static property or instance property
     */
    public static function getTableName(): string
    {
        // Check for static property first (Models created by us)
        if (property_exists(static::class, 'table')) {
            // Need reflection or just checking if it is accessible? 
            // If it is protected static, we can access it inside the class if we are in the hierarchy?
            // Actually, property_exists returns true even if protected.
            // But we can't access `static::$table` if it's protected and we are in parent?
            // Child overrides parent. Parent accesses static::$table -> late static binding.
            // But if child declares it as non-static (ExpenseType), static::$table fails.
            
            // Safe way: Instantiate
            $instance = new static();
            if (isset($instance->table)) {
                return $instance->table;
            }
        }
        
        // Fallback: use class name pluralized (simple convention)
        $className = basename(str_replace('\\', '/', static::class));
        return strtolower($className) . 's';
    }

    public static function all(array $columns = ['*']): array
    {
        $table = static::getTableName();
        $cols = implode(', ', $columns);
        return Database::query("SELECT $cols FROM $table");
    }

    public static function find($id)
    {
        $table = static::getTableName();
        $result = Database::queryOne("SELECT * FROM $table WHERE id = ?", [$id]);
        return $result;
    }

    public static function where(string $column, $value): SimpleQueryBuilder
    {
        $table = static::getTableName();
        // Check if SimpleQueryBuilder exists
        if (!class_exists('App\Core\SimpleQueryBuilder')) {
            throw new \Exception("SimpleQueryBuilder class not found.");
        }
        
        $builder = new SimpleQueryBuilder($table);
        return $builder->where($column, $value);
    }
    
    public static function create(array $data)
    {
        $table = static::getTableName();
        $validData = []; // Should filter by fillable
        
        // Very basic fillable check if available
        $instance = new static();
        if (!empty($instance->fillable)) {
             foreach ($data as $key => $val) {
                 if (in_array($key, $instance->fillable)) {
                     $validData[$key] = $val;
                 }
             }
        } else {
            $validData = $data;
        }

        if (empty($validData)) {
            return false;
        }

        return Database::insert($table, $validData);
    }
    
    public static function update($id, array $data)
    {
        $table = static::getTableName();
        return Database::update($table, $data, 'id = ?', [$id]);
    }
    
    public static function destroy($id)
    {
        $table = static::getTableName();
        return Database::delete($table, 'id = ?', [$id]);
    }
}
