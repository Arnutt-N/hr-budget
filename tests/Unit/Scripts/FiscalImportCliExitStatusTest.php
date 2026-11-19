<?php

declare(strict_types=1);

namespace Tests\Unit\Scripts;

use PHPUnit\Framework\TestCase;

final class FiscalImportCliExitStatusTest extends TestCase
{
    public function test_importer_returns_failure_status_when_row_errors_are_counted(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/scripts/import_fiscal_codes.php');

        $this->assertIsString($source);
        $this->assertMatchesRegularExpression(
            '/exit\(\$stats\[\'errors\'\] > 0 \? 1 : 0\);\s*\R?$/',
            $source
        );
    }
}
