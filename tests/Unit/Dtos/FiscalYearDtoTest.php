<?php

declare(strict_types=1);

namespace Tests\Unit\Dtos;

use PHPUnit\Framework\TestCase;
use App\Dtos\CreateFiscalYearDto;
use App\Dtos\UpdateFiscalYearDto;

class FiscalYearDtoTest extends TestCase
{
    /** @test */
    public function create_valid_year_passes(): void
    {
        $dto = new CreateFiscalYearDto(2569, '2025-10-01', '2026-09-30');
        $this->assertEmpty($dto->validate());
    }

    /** @test */
    public function create_year_too_low_fails(): void
    {
        $dto = new CreateFiscalYearDto(2000, '2025-10-01', '2026-09-30');
        $errors = $dto->validate();
        $this->assertArrayHasKey('year', $errors);
    }

    /** @test */
    public function create_year_too_high_fails(): void
    {
        $dto = new CreateFiscalYearDto(2800, '2025-10-01', '2026-09-30');
        $errors = $dto->validate();
        $this->assertArrayHasKey('year', $errors);
    }

    /** @test */
    public function create_empty_dates_fail(): void
    {
        $dto = new CreateFiscalYearDto(2569, '', '');
        $errors = $dto->validate();
        $this->assertArrayHasKey('start_date', $errors);
        $this->assertArrayHasKey('end_date', $errors);
    }

    /** @test */
    public function create_end_before_start_fails(): void
    {
        $dto = new CreateFiscalYearDto(2569, '2026-09-30', '2025-10-01');
        $errors = $dto->validate();
        $this->assertArrayHasKey('end_date', $errors);
    }

    /** @test */
    public function create_invalid_date_format_fails(): void
    {
        $dto = new CreateFiscalYearDto(2569, 'not-a-date', '2026-09-30');
        $errors = $dto->validate();
        $this->assertArrayHasKey('start_date', $errors);
    }

    /** @test */
    public function update_valid_partial_passes(): void
    {
        $dto = new UpdateFiscalYearDto(year: 2570);
        $this->assertEmpty($dto->validate());
    }

    /** @test */
    public function update_invalid_year_fails(): void
    {
        $dto = new UpdateFiscalYearDto(year: 1000);
        $errors = $dto->validate();
        $this->assertArrayHasKey('year', $errors);
    }
}
