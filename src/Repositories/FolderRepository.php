<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

/**
 * Document-vault folder data access. Ports the legacy App\Models\Folder
 * queries into the layered repository style used by the /api/v1 layer.
 */
final class FolderRepository
{
    public function findById(int $id): ?array
    {
        return Database::queryOne(
            "SELECT f.*, u.name as created_by_name
             FROM folders f
             LEFT JOIN users u ON f.created_by = u.id
             WHERE f.id = ?",
            [$id]
        );
    }

    public function findRoots(int $fiscalYear): array
    {
        return Database::query(
            "SELECT f.*, u.name as created_by_name,
                    (SELECT COUNT(*) FROM folders WHERE parent_id = f.id) as subfolder_count,
                    (SELECT COUNT(*) FROM files WHERE folder_id = f.id) as file_count
             FROM folders f
             LEFT JOIN users u ON f.created_by = u.id
             WHERE f.fiscal_year = ? AND f.parent_id IS NULL
             ORDER BY f.is_system DESC, f.name ASC",
            [$fiscalYear]
        );
    }

    public function findChildren(int $parentId): array
    {
        return Database::query(
            "SELECT f.*, u.name as created_by_name,
                    (SELECT COUNT(*) FROM folders WHERE parent_id = f.id) as subfolder_count,
                    (SELECT COUNT(*) FROM files WHERE folder_id = f.id) as file_count
             FROM folders f
             LEFT JOIN users u ON f.created_by = u.id
             WHERE f.parent_id = ?
             ORDER BY f.is_system DESC, f.name ASC",
            [$parentId]
        );
    }

    public function findTree(int $fiscalYear): array
    {
        $all = Database::query(
            "SELECT * FROM folders WHERE fiscal_year = ? ORDER BY is_system DESC, name ASC",
            [$fiscalYear]
        );

        return $this->buildTree($all, null);
    }

    /** Recursive nest of a flat folder list (mirrors legacy Folder::buildTree). */
    private function buildTree(array $folders, ?int $parentId): array
    {
        $tree = [];
        foreach ($folders as $folder) {
            $fParent = $folder['parent_id'] !== null ? (int) $folder['parent_id'] : null;
            if ($fParent === $parentId) {
                $folder['children'] = $this->buildTree($folders, (int) $folder['id']);
                $tree[] = $folder;
            }
        }

        return $tree;
    }

    /**
     * Breadcrumb root→current via recursive CTE, with an iterative fallback
     * for engines/connections where the CTE is unavailable.
     */
    public function breadcrumb(int $id): array
    {
        $sql = "
            WITH RECURSIVE folder_path (id, name, parent_id, fiscal_year, depth) AS (
                SELECT id, name, parent_id, fiscal_year, 0
                FROM folders
                WHERE id = ?
                UNION ALL
                SELECT f.id, f.name, f.parent_id, f.fiscal_year, fp.depth + 1
                FROM folders f
                INNER JOIN folder_path fp ON f.id = fp.parent_id
            )
            SELECT * FROM folder_path ORDER BY depth DESC
        ";

        try {
            return Database::query($sql, [$id]);
        } catch (\Throwable $e) {
            $path = [];
            $current = $this->findById($id);
            while ($current) {
                array_unshift($path, $current);
                $current = !empty($current['parent_id'])
                    ? $this->findById((int) $current['parent_id'])
                    : null;
            }

            return $path;
        }
    }

    public function availableYears(): array
    {
        return Database::query(
            "SELECT DISTINCT fiscal_year FROM folders
             WHERE fiscal_year IS NOT NULL
             ORDER BY fiscal_year DESC"
        );
    }

    public function create(array $data): int
    {
        // Build a human-readable folder_path (parent path / name, or year / name).
        $path = $data['name'];
        if (!empty($data['parent_id'])) {
            $parent = $this->findById((int) $data['parent_id']);
            if ($parent !== null && !empty($parent['folder_path'])) {
                $path = $parent['folder_path'] . '/' . $data['name'];
            }
        } elseif (!empty($data['fiscal_year'])) {
            $path = $data['fiscal_year'] . '/' . $data['name'];
        }

        return Database::insert('folders', [
            'name' => $data['name'],
            'fiscal_year' => $data['fiscal_year'] ?? null,
            'budget_category_id' => $data['budget_category_id'] ?? null,
            'parent_id' => $data['parent_id'] ?? null,
            'folder_path' => $path,
            'description' => $data['description'] ?? null,
            'is_system' => $data['is_system'] ?? 0,
            'created_by' => $data['created_by'],
        ]);
    }

    public function delete(int $id): int
    {
        return Database::delete('folders', 'id = ?', [$id]);
    }
}
