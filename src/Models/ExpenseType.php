<?php
namespace App\Models;

use App\Core\Model;

class ExpenseType extends Model {
    protected $table = 'expense_types';
    protected $fillable = [
        'code', 'name_th', 'sort_order', 'is_active'
    ];
}
