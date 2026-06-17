<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

/**
 * Read access for the fixed catalogue of permissions (seeded via migration).
 */
class PermissionRepository
{
    /** @return array<int,array> */
    public function findAll(): array
    {
        return Database::query("SELECT * FROM permissions ORDER BY resource, code");
    }

    /**
     * Resolve a list of permission codes to their ids (skips unknown codes).
     * @param array<int,string> $codes
     * @return array<int,int>
     */
    public function idsForCodes(array $codes): array
    {
        $codes = array_values(array_unique(array_filter($codes, 'is_string')));
        if ($codes === []) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($codes), '?'));
        $rows = Database::query(
            "SELECT id FROM permissions WHERE code IN ($placeholders)",
            $codes
        );
        return array_map(static fn ($r) => (int) $r['id'], $rows);
    }
}
