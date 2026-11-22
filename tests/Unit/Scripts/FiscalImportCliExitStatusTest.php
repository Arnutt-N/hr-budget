<?php

declare(strict_types=1);

namespace Tests\Unit\Scripts;

use PHPUnit\Framework\TestCase;

final class FiscalImportCliExitStatusTest extends TestCase
{
    public function test_importer_returns_failure_status_when_row_errors_are_counted(): void
    {
        $this->markTestSkipped('CLI exit status requires a real importer run with source spreadsheets.');
    }

}
