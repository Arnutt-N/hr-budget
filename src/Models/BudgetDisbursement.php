<?php
namespace App\Models;

use PDO;
use App\Database;

class BudgetDisbursement {
    protected $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function allByRequestItemId(int $itemId): array {
        $stmt = $this->pdo->prepare('SELECT * FROM budget_disbursements WHERE budget_request_item_id = :itemId ORDER BY disbursement_date DESC');
        $stmt->execute(['itemId' => $itemId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(int $itemId, float $amount, string $date, ?string $notes = null): int {
        $stmt = $this->pdo->prepare('INSERT INTO budget_disbursements (budget_request_item_id, amount, disbursement_date, notes) VALUES (:itemId, :amount, :date, :notes)');
        $stmt->execute([
            'itemId' => $itemId,
            'amount' => $amount,
            'date'   => $date,
            'notes'  => $notes,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare('DELETE FROM budget_disbursements WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
?>
