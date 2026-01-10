<?php
namespace App\Models;

use App\Core\Database;

class BudgetRequestItem
{
    /**
     * Get items for a request
     */
    public static function getByRequestId(int $requestId): array
    {
        return Database::query("SELECT * FROM budget_request_items WHERE budget_request_id = ?", [$requestId]);
    }

    /**
     * Get items for a request as a tree (if needed)
     */
    public static function getTree(int $requestId): array
    {
        $items = self::getByRequestId($requestId);
        // This would require parent_id in budget_request_items if we want internal hierarchy
        // But usually, we link to budget_category_items which has the hierarchy.
        // For now, return flat and let view/controller handle it if needed.
        return $items;
    }

    /**
     * Create new item
     */
    public static function create(array $data): int
    {
        return Database::insert('budget_request_items', $data);
    }

    /**
     * Delete item
     */
    public static function delete(int $id): bool
    {
        return Database::delete('budget_request_items', 'id = ?', [$id]) > 0;
    }

    /**
     * Upsert item (Create or Update based on request_id and category_item_id)
     */
    public static function upsert(int $requestId, int $categoryItemId, array $data): int
    {
        $existing = Database::query(
            "SELECT id FROM budget_request_items WHERE budget_request_id = ? AND category_item_id = ?",
            [$requestId, $categoryItemId]
        );

        if (!empty($existing)) {
            $id = $existing[0]['id'];
            Database::update('budget_request_items', $data, 'id = ?', [$id]);
            return $id;
        } else {
            $data['budget_request_id'] = $requestId;
            $data['category_item_id'] = $categoryItemId;
            return Database::insert('budget_request_items', $data);
        }
    }
}
