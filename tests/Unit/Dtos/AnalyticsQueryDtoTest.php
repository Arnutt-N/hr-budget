<?php

declare(strict_types=1);

namespace Tests\Unit\Dtos;

use App\Dtos\AnalyticsQueryDto;
use PHPUnit\Framework\TestCase;

/**
 * Input-DTO validation for the analytics endpoints. Mirrors the
 * BudgetRequestListQueryDto pattern: readonly props, fromQueryString() reads
 * $_GET, validate() returns a map of field => Thai error.
 */
class AnalyticsQueryDtoTest extends TestCase
{
    protected function tearDown(): void
    {
        // Each test mutates $_GET; reset it so tests stay isolated.
        $_GET = [];
    }

    /** @test */
    public function from_query_string_falls_back_to_config_fiscal_year_when_absent(): void
    {
        $_GET = [];

        $dto = AnalyticsQueryDto::fromQueryString();

        $config = require __DIR__ . '/../../../config/app.php';
        $this->assertSame((int) $config['fiscal_year']['current'], $dto->fiscalYear);
        $this->assertSame('year', $dto->dimension);
    }

    /** @test */
    public function from_query_string_reads_fiscal_year_and_dimension(): void
    {
        $_GET = ['fiscal_year' => '2570', 'dimension' => 'quarter'];

        $dto = AnalyticsQueryDto::fromQueryString();

        $this->assertSame(2570, $dto->fiscalYear);
        $this->assertSame('quarter', $dto->dimension);
    }

    /** @test */
    public function valid_input_produces_no_errors(): void
    {
        $_GET = ['fiscal_year' => '2569', 'dimension' => 'month'];

        $errors = AnalyticsQueryDto::fromQueryString()->validate();

        $this->assertSame([], $errors);
    }

    /** @test */
    public function fiscal_year_below_range_is_rejected(): void
    {
        $_GET = ['fiscal_year' => '2499', 'dimension' => 'year'];

        $errors = AnalyticsQueryDto::fromQueryString()->validate();

        $this->assertArrayHasKey('fiscal_year', $errors);
    }

    /** @test */
    public function fiscal_year_above_range_is_rejected(): void
    {
        $_GET = ['fiscal_year' => '2601', 'dimension' => 'year'];

        $errors = AnalyticsQueryDto::fromQueryString()->validate();

        $this->assertArrayHasKey('fiscal_year', $errors);
    }

    /** @test */
    public function unknown_dimension_is_rejected_as_injection_guard(): void
    {
        $_GET = ['fiscal_year' => '2569', 'dimension' => 'year; DROP TABLE budget_trackings'];

        $errors = AnalyticsQueryDto::fromQueryString()->validate();

        $this->assertArrayHasKey('dimension', $errors);
    }

    /** @test */
    public function all_whitelisted_dimensions_are_accepted(): void
    {
        foreach (['year', 'quarter', 'month'] as $dimension) {
            $_GET = ['fiscal_year' => '2569', 'dimension' => $dimension];
            $errors = AnalyticsQueryDto::fromQueryString()->validate();
            $this->assertArrayNotHasKey('dimension', $errors, "dimension '$dimension' should be valid");
        }
    }
}
