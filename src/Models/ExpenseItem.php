<?php
namespace App\Models;

use App\Core\Model;

class ExpenseItem extends Model {
    protected $table = 'expense_items';
    protected $fillable = [
        'expense_group_id', 'parent_id', 'code', 'name_th', 'name_en', 'description',
        'level', 'is_header', 'requires_quantity', 'default_unit',
        'sort_order', 'is_active',
        'deleted_at', 'created_by', 'updated_by'
    ];

    /**
     * Get children items
     */
    public function getChildren($parentId) {
        return $this->where('parent_id', $parentId)
                    ->orderBy('sort_order', 'ASC')
                    ->get();
    }

    /**
     * Get full hierarchy tree
     */
    public function getTree($groupId = null) {
        $query = "SELECT * FROM expense_items WHERE deleted_at IS NULL";
        $params = [];
        
        if ($groupId) {
            $query .= " AND expense_group_id = ?";
            $params[] = $groupId;
        }
        
        $query .= " ORDER BY level ASC, sort_order ASC";
        
        // This logic should be in Model Core fetchAll but simplifying here
        // Assuming implementation handles basic queries
        return $this->raw($query, $params);
    }
}
