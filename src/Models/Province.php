<?php
namespace App\Models;

use App\Core\Model;

class Province extends Model {
    protected $table = 'provinces';
    protected $fillable = [
        'code', 'name_th', 'name_en', 'description', 'region',
        'sort_order', 'is_active',
        'deleted_at', 'created_by', 'updated_by'
    ];
}
