<?php
namespace App\Models;

use App\Core\Database;

class POCommitment
{
    public static function create($data)
    {
        $sql = "INSERT INTO po_commitments (
            fiscal_year, allocation_id, po_number, po_date,
            vendor_name, description, amount, status
        ) VALUES (
            :fiscal_year, :allocation_id, :po_number, :po_date,
            :vendor_name, :description, :amount, :status
        )";

        Database::query($sql, [
            ':fiscal_year' => $data['fiscal_year'] ?? 2568,
            ':allocation_id' => $data['allocation_id'],
            ':po_number' => $data['po_number'],
            ':po_date' => $data['po_date'] ?? date('Y-m-d'),
            ':vendor_name' => $data['vendor_name'] ?? null,
            ':description' => $data['description'] ?? null,
            ':amount' => $data['amount'],
            ':status' => $data['status'] ?? 'active'
        ]);

        return Database::lastInsertId();
    }
}
