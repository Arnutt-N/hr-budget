<?php

declare(strict_types=1);

namespace Tests\Unit\Dtos;

use PHPUnit\Framework\TestCase;
use App\Dtos\CreatePlanDto;
use App\Dtos\UpdatePlanDto;

class PlanDtoTest extends TestCase
{
    /** @test */
    public function create_valid_plan_passes(): void
    {
        $dto = new CreatePlanDto(
            code: 'PLN-001',
            nameTh: 'แผนงานพัฒนาบุคลากร',
            nameEn: 'HR Development Plan',
            description: null,
            fiscalYear: 2569,
        );
        $this->assertEmpty($dto->validate());
    }

    /** @test */
    public function create_missing_name_th_fails(): void
    {
        $dto = new CreatePlanDto(
            code: 'PLN-001',
            nameTh: '',
            nameEn: null,
            description: null,
            fiscalYear: 2569,
        );
        $errors = $dto->validate();
        $this->assertArrayHasKey('name_th', $errors);
    }

    /** @test */
    public function create_name_th_too_long_fails(): void
    {
        $dto = new CreatePlanDto(
            code: null,
            nameTh: str_repeat('ก', 501),
            nameEn: null,
            description: null,
            fiscalYear: 2569,
        );
        $errors = $dto->validate();
        $this->assertArrayHasKey('name_th', $errors);
    }

    /** @test */
    public function create_fiscal_year_zero_fails(): void
    {
        $dto = new CreatePlanDto(
            code: null,
            nameTh: 'แผนงานทดสอบ',
            nameEn: null,
            description: null,
            fiscalYear: 0,
        );
        $errors = $dto->validate();
        $this->assertArrayHasKey('fiscal_year', $errors);
    }

    /** @test */
    public function create_code_too_long_fails(): void
    {
        $dto = new CreatePlanDto(
            code: str_repeat('A', 51),
            nameTh: 'แผนงานทดสอบ',
            nameEn: null,
            description: null,
            fiscalYear: 2569,
        );
        $errors = $dto->validate();
        $this->assertArrayHasKey('code', $errors);
    }

    /** @test */
    public function update_valid_partial_passes(): void
    {
        $dto = new UpdatePlanDto(nameTh: 'แผนงานแก้ไข');
        $this->assertEmpty($dto->validate());
    }
}
