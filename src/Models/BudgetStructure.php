<?php

namespace App\Models;

use App\Core\Database;

class BudgetStructure
{
    /**
     * Get distinct budget plans for a specific fiscal year
     */
    public static function getDistinctPlans(int $fiscalYear): array
    {
        // Get distinct plan names from plans that have allocations
        $sql = "SELECT DISTINCT p.name_th as plan_name, p.id, p.code
                FROM plans p
                INNER JOIN budget_allocations ba ON p.id = ba.plan_id
                WHERE ba.fiscal_year = ? AND ba.deleted_at IS NULL
                ORDER BY p.name_th";
        
        return Database::query($sql, [$fiscalYear]);
    }
}
