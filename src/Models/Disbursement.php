<?php
namespace App\Models;

use App\Core\Database;

class Disbursement
{
    public static function create($data)
    {
        $sql = "INSERT INTO disbursements (
            fiscal_year, allocation_id, po_id, disbursement_date,
            reference_no, description, amount, payment_method, created_by
        ) VALUES (
            :fiscal_year, :allocation_id, :po_id, :disbursement_date,
            :reference_no, :description, :amount, :payment_method, :created_by
        )";

        Database::query($sql, [
            ':fiscal_year' => $data['fiscal_year'] ?? 2568,
            ':allocation_id' => $data['allocation_id'],
            ':po_id' => $data['po_id'] ?? null,
            ':disbursement_date' => $data['disbursement_date'] ?? date('Y-m-d'),
            ':reference_no' => $data['reference_no'] ?? null,
            ':description' => $data['description'] ?? null,
            ':amount' => $data['amount'],
            ':payment_method' => $data['payment_method'] ?? 'transfer',
            ':created_by' => $data['created_by'] ?? null
        ]);

        return Database::lastInsertId();
    }
}
