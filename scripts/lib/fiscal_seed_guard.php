<?php

declare(strict_types=1);

/**
 * Fiscal-code seed database guard, shared by the CLI script and its unit test.
 *
 * Production writes must be impossible by construction: the seed script exits
 * unless the resolved database name ends in _test.
 */

/**
 * Return an error message when $database may not be written, else null.
 */
function fiscalSeedDatabaseError(string $database): ?string
{
    if ($database === '_test' || !str_ends_with($database, '_test')) {
        return "Refusing to write to '{$database}': only *_test databases are allowed. "
            . "ห้ามเขียนฐานข้อมูล '{$database}' ต้องใช้ชื่อที่ลงท้ายด้วย _test เท่านั้น "
            . "(use --database=NAME with a name ending in _test).";
    }

    return null;
}
