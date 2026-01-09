<?php
namespace App\Models;

use App\Core\Database;

class BudgetTransfer
{
    public static function create($data)
    {
        $sql = "INSERT INTO budget_transfers (
            fiscal_year, transfer_date, reference_no,
            source_allocation_id, source_description,
            destination_allocation_id, destination_description,
            amount, transfer_type, reason, reason_category,
            status, created_by
        ) VALUES (
            :fiscal_year, :transfer_date, :reference_no,
            :source_allocation_id, :source_description,
            :destination_allocation_id, :destination_description,
            :amount, :transfer_type, :reason, :reason_category,
            :status, :created_by
        )";

        Database::query($sql, [
            ':fiscal_year' => $data['fiscal_year'] ?? 2568,
            ':transfer_date' => $data['transfer_date'] ?? date('Y-m-d'),
            ':reference_no' => $data['reference_no'] ?? null,
            ':source_allocation_id' => $data['source_allocation_id'] ?? null,
            ':source_description' => $data['source_description'] ?? null,
            ':destination_allocation_id' => $data['destination_allocation_id'] ?? null,
            ':destination_description' => $data['destination_description'] ?? null,
            ':amount' => $data['amount'],
            ':transfer_type' => $data['transfer_type'] ?? 'reallocation',
            ':reason' => $data['reason'] ?? null,
            ':reason_category' => $data['reason_category'] ?? 'other',
            ':status' => $data['status'] ?? 'draft',
            ':created_by' => $data['created_by'] ?? null
        ]);

        return Database::lastInsertId();
    }

    public static function approve($id, $approver_id)
    {
        $sql = "UPDATE budget_transfers SET status = 'approved', approved_by = ?, approved_at = NOW() WHERE id = ?";
        Database::query($sql, [$approver_id, $id]);
        
        // TODO: Trigger BudgetAllocation balance update
    }
}
