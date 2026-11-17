<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

final class FiscalCodeReportService
{
    /** @return list<array<string,mixed>> */
    public function byExpenseCategory(int $fiscalYear): array
    {
        return Database::query(
            "SELECT e.fiscal_code AS code, e.name_th AS name_th,
                    COUNT(bc.id) AS count,
                    COALESCE(MAX(a.allocated_pba), 0) AS allocated_pba
               FROM expense_types e
               JOIN budget_categories bc
                 ON bc.expense_cat_code = e.fiscal_code
                AND LENGTH(bc.fiscal_code) = 20
               LEFT JOIN (
                    SELECT expense_type_id, SUM(COALESCE(allocated_pba, 0)) AS allocated_pba
                      FROM budget_line_items
                     WHERE fiscal_year = ?
                       AND deleted_at IS NULL
                     GROUP BY expense_type_id
                ) a ON a.expense_type_id = e.id
              GROUP BY e.fiscal_code, e.name_th
              ORDER BY e.fiscal_code",
            [$fiscalYear]
        );
    }

    /** @return list<array<string,mixed>> */
    public function byActivity(int $fiscalYear): array
    {
        return Database::query(
            "SELECT p.activity_code AS activity_code,
                    MAX(p.name_th) AS name_th,
                    COUNT(*) AS count
               FROM plans p
              WHERE p.fiscal_year = ?
                AND LENGTH(p.activity_code) = 17
                AND p.deleted_at IS NULL
              GROUP BY p.activity_code
              ORDER BY p.activity_code",
            [$fiscalYear]
        );
    }

    /** @return list<array<string,mixed>> */
    public function byParentRollup(): array
    {
        return Database::query(
            "SELECT child.fiscal_code_parent AS parent_code,
                    parent.name_th AS name_th,
                    COUNT(*) AS count
               FROM budget_categories child
               LEFT JOIN budget_categories parent
                 ON parent.fiscal_code = child.fiscal_code_parent
              WHERE LENGTH(child.fiscal_code) = 20
                AND child.fiscal_code_parent <> ''
              GROUP BY child.fiscal_code_parent, parent.name_th
              ORDER BY parent_code"
        );
    }

    /** @return list<array<string,mixed>> */
    public function byAgency(): array
    {
        return Database::query(
            "SELECT SUBSTR(child.fiscal_code, 1, 5) AS agency_code,
                    d.name_th AS name_th,
                    COUNT(*) AS count
               FROM budget_categories child
               LEFT JOIN divisions d
                 ON d.code = SUBSTR(child.fiscal_code, 1, 5)
              WHERE LENGTH(child.fiscal_code) = 20
                AND child.expense_cat_code IS NOT NULL
              GROUP BY SUBSTR(child.fiscal_code, 1, 5), d.name_th
              ORDER BY agency_code"
        );
    }
}
