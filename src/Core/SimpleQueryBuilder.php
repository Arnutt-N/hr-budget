<?php
namespace App\Core;

/**
 * Simple Query Builder to support chained where/orderBy/get calls
 */
class SimpleQueryBuilder
{
    protected string $table;
    protected array $conditions = [];
    protected array $params = [];
    protected string $orderBy = '';
    protected array $select = ['*'];

    public function __construct(string $table) 
    {
        $this->table = $table;
    }
    
    public function select(array $columns): self
    {
        $this->select = $columns;
        return $this;
    }

    public function where(string $column, $value): self
    {
        $this->conditions[] = "$column = ?";
        $this->params[] = $value;
        return $this;
    }

    public function whereNull(string $column): self
    {
        $this->conditions[] = "$column IS NULL";
        return $this;
    }

    public function whereNotNull(string $column): self
    {
        $this->conditions[] = "$column IS NOT NULL";
        return $this;
    }
    
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBy = " ORDER BY $column $direction";
        return $this;
    }
    
    public function get(): array
    {
        $cols = implode(', ', $this->select);
        $sql = "SELECT $cols FROM {$this->table}";
        
        if (!empty($this->conditions)) {
            $sql .= " WHERE " . implode(' AND ', $this->conditions);
        }
        
        $sql .= $this->orderBy;
        
        return Database::query($sql, $this->params);
    }
    
    public function first(): ?array
    {
        $result = $this->get();
        return $result[0] ?? null;
    }
}
