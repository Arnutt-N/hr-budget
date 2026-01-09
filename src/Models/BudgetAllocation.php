<?php
namespace App\Models;

use App\Core\Database;

class BudgetAllocation
{
    public static function find($id)
    {
        $result = Database::query("SELECT * FROM budget_allocations WHERE id = ?", [$id]);
        return $result[0] ?? null;
    }

    public static function findByParams($fiscal_year, $plan_id, $item_id)
    {
        $sql = "SELECT * FROM budget_allocations 
                WHERE fiscal_year = ? AND plan_id = ? AND item_id = ?";
        $result = Database::query($sql, [$fiscal_year, $plan_id, $item_id]);
        return $result[0] ?? null;
    }

    public static function create($data)
    {
        $sql = "INSERT INTO budget_allocations (
            fiscal_year, plan_id, fund_source_id, category_id, item_id, division_id,
            allocated_pba, allocated_received, net_budget, remaining,
            created_by
        ) VALUES (
            :fiscal_year, :plan_id, :fund_source_id, :category_id, :item_id, :division_id,
            :allocated_pba, :allocated_received, :net_budget, :remaining,
            :created_by
        )";

        // Init net_budget and remaining same as allocated_received if not provided
        $received = $data['allocated_received'] ?? 0;
        $net = $data['net_budget'] ?? $received;
        $remaining = $data['remaining'] ?? $net;
        
        Database::query($sql, [
            ':fiscal_year' => $data['fiscal_year'] ?? 2568,
            ':plan_id' => $data['plan_id'],
            ':fund_source_id' => $data['fund_source_id'] ?? null,
            ':category_id' => $data['category_id'] ?? null,
            ':item_id' => $data['item_id'],
            ':division_id' => $data['division_id'] ?? null,
            ':allocated_pba' => $data['allocated_pba'] ?? 0,
            ':allocated_received' => $received,
            ':net_budget' => $net,
            ':remaining' => $remaining,
            ':created_by' => $data['created_by'] ?? null
        ]);

        return Database::lastInsertId();
    }

    public static function updateBalances($id)
    {
        // Recalculate balances based on transfers, po, disbursement
        // This is a placeholder for complex logic. 
        // For now, we assume values are updated directly or via specific methods.
    }
}
