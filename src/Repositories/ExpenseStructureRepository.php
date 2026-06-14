<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

/**
 * Read-only reference tree of expense types → groups → items, plus an
 * item → (group_id, type_id) resolver used when persisting trackings.
 */
class ExpenseStructureRepository
{
    /**
     * Active expense_types, each with active groups, each with active leaf items.
     *
     * Assembled from exactly 3 flat queries (types, groups, items) joined in PHP
     * via hash maps keyed by parent id — avoids the previous 1 + T + T×G query
     * fan-out. Output shape and ordering are preserved exactly.
     *
     * @return array<int,array<string,mixed>>
     */
    public function tree(): array
    {
        $types = Database::query(
            "SELECT id, code, name_th, sort_order
             FROM expense_types
             WHERE is_active = 1
             ORDER BY sort_order, id"
        );

        $groups = Database::query(
            "SELECT id, expense_type_id, code, name_th, sort_order
             FROM expense_groups
             WHERE is_active = 1 AND deleted_at IS NULL
             ORDER BY sort_order, id"
        );

        $items = Database::query(
            "SELECT id, expense_group_id, expense_type_id, parent_id,
                    code, name_th, level, is_header
             FROM expense_items
             WHERE is_active = 1 AND deleted_at IS NULL
             ORDER BY sort_order, id"
        );

        // Bucket items by group_id, groups by type_id (insertion order already
        // reflects the ORDER BY sort_order, id from each query).
        $itemsByGroup = [];
        foreach ($items as $item) {
            $gid = $item['expense_group_id'] !== null ? (int) $item['expense_group_id'] : 0;
            $itemsByGroup[$gid][] = $item;
        }

        $groupsByType = [];
        foreach ($groups as $group) {
            $group['items'] = $itemsByGroup[(int) $group['id']] ?? [];
            $tid = (int) $group['expense_type_id'];
            $groupsByType[$tid][] = $group;
        }

        foreach ($types as &$type) {
            $type['groups'] = $groupsByType[(int) $type['id']] ?? [];
        }
        unset($type);

        return $types;
    }

    /**
     * Resolve an expense item to its group/type ids.
     * expense_type_id on the item may be NULL → fall back via the group.
     *
     * @return array{expense_item_id:int, expense_group_id:?int, expense_type_id:?int}|null
     */
    public function resolveItem(int $expenseItemId): ?array
    {
        $item = Database::queryOne(
            "SELECT id, expense_group_id, expense_type_id
             FROM expense_items
             WHERE id = ?",
            [$expenseItemId]
        );

        if ($item === null) {
            return null;
        }

        $groupId = $item['expense_group_id'] !== null ? (int) $item['expense_group_id'] : null;
        $typeId = $item['expense_type_id'] !== null ? (int) $item['expense_type_id'] : null;

        if ($typeId === null && $groupId !== null) {
            $group = Database::queryOne(
                "SELECT expense_type_id FROM expense_groups WHERE id = ?",
                [$groupId]
            );
            if ($group !== null && $group['expense_type_id'] !== null) {
                $typeId = (int) $group['expense_type_id'];
            }
        }

        return [
            'expense_item_id' => (int) $item['id'],
            'expense_group_id' => $groupId,
            'expense_type_id' => $typeId,
        ];
    }

    /**
     * Batch variant of resolveItem(): resolve many items in a single
     * `WHERE id IN (...)` query (plus one batched query for the group
     * fallback), keyed by expense_item_id. Items that do not exist are
     * simply absent from the result — callers treat a missing key the
     * same as resolveItem() returning null.
     *
     * @param array<int,int> $expenseItemIds
     * @return array<int,array{expense_item_id:int, expense_group_id:?int, expense_type_id:?int}>
     */
    public function resolveItems(array $expenseItemIds): array
    {
        $ids = array_values(array_unique(array_map('intval', $expenseItemIds)));
        if ($ids === []) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($ids), '?'));
        $items = Database::query(
            "SELECT id, expense_group_id, expense_type_id
             FROM expense_items
             WHERE id IN ({$placeholders})",
            $ids
        );

        // Collect groups needing a type-id fallback so we can resolve them
        // in one extra batched query instead of one per item.
        $resolved = [];
        $fallbackGroupIds = [];
        foreach ($items as $item) {
            $itemId = (int) $item['id'];
            $groupId = $item['expense_group_id'] !== null ? (int) $item['expense_group_id'] : null;
            $typeId = $item['expense_type_id'] !== null ? (int) $item['expense_type_id'] : null;

            if ($typeId === null && $groupId !== null) {
                $fallbackGroupIds[$groupId] = true;
            }

            $resolved[$itemId] = [
                'expense_item_id' => $itemId,
                'expense_group_id' => $groupId,
                'expense_type_id' => $typeId,
            ];
        }

        if ($fallbackGroupIds !== []) {
            $groupIds = array_keys($fallbackGroupIds);
            $groupPlaceholders = implode(', ', array_fill(0, count($groupIds), '?'));
            $groups = Database::query(
                "SELECT id, expense_type_id
                 FROM expense_groups
                 WHERE id IN ({$groupPlaceholders})",
                $groupIds
            );

            $typeByGroup = [];
            foreach ($groups as $group) {
                if ($group['expense_type_id'] !== null) {
                    $typeByGroup[(int) $group['id']] = (int) $group['expense_type_id'];
                }
            }

            foreach ($resolved as $itemId => $row) {
                if ($row['expense_type_id'] === null
                    && $row['expense_group_id'] !== null
                    && isset($typeByGroup[$row['expense_group_id']])
                ) {
                    $resolved[$itemId]['expense_type_id'] = $typeByGroup[$row['expense_group_id']];
                }
            }
        }

        return $resolved;
    }
}
