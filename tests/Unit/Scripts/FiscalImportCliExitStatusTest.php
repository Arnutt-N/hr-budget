<?php

declare(strict_types=1);

namespace Tests\Unit\Scripts;

use PHPUnit\Framework\TestCase;

final class FiscalImportCliExitStatusTest extends TestCase
{
    public function test_importer_refuses_non_test_database_before_reading_sources(): void
    {
        $script = dirname(__DIR__, 3) . '/scripts/import_fiscal_codes.php';
        $process = proc_open(
            [PHP_BINARY, $script, '--database=__invalid__'],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes
        );
        $this->assertIsResource($process);

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $status = proc_close($process);

        $this->assertSame(1, $status);
        $this->assertSame('', $stdout);
        $this->assertStringContainsString('[ERROR] Refusing to run', $stderr);
    }
}
