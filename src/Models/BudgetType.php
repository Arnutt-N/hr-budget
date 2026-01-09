<?php
namespace App\Models;

use App\Core\Model;

class BudgetType extends Model {
    protected $table = 'budget_types';
    protected $fillable = [
        'code', 'name_th', 'name_en', 'description', 
        'sort_order', 'is_active', 
        'deleted_at', 'created_by', 'updated_by'
    ];

    public function plans() {
        // Return relationship definition needed? 
        // Using raw SQL usually in this project, but defining structure helps.
    }
}
