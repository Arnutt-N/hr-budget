<?php

declare(strict_types=1);

namespace Tests\Unit\Dtos;

use PHPUnit\Framework\TestCase;
use App\Dtos\CreateBudgetTargetDto;
use App\Dtos\UpdateBudgetTargetDto;

class BudgetTargetDtoTest extends TestCase
{
    /** @test */
    public function create_valid_target_passes(): void
    {
        $dto = new CreateBudgetTargetDto(targetTypeId: 1, fiscalYear: 2569);
        $this->assertEmpty($dto->validate());
    }

    /** @test */
    public function create_target_type_id_zero_fails(): void
    {
        $dto = new CreateBudgetTargetDto(targetTypeId: 0, fiscalYear: 2569);
        $errors = $dto->validate();
        $this->assertArrayHasKey('target_type_id', $errors);
    }

    /** @test */
    public function create_fiscal_year_zero_fails(): void
    {
        $dto = new CreateBudgetTargetDto(targetTypeId: 1, fiscalYear: 0);
        $errors = $dto->validate();
        $this->assertArrayHasKey('fiscal_year', $errors);
    }

    /** @test */
    public function create_invalid_quarter_fails(): void
    {
        $dto = new CreateBudgetTargetDto(targetTypeId: 1, fiscalYear: 2569, quarter: 5);
        $errors = $dto->validate();
        $this->assertArrayHasKey('quarter', $errors);
    }

    /** @test */
    public function create_invalid_target_percent_fails(): void
    {
        $dto = new CreateBudgetTargetDto(targetTypeId: 1, fiscalYear: 2569, targetPercent: 150.0);
        $errors = $dto->validate();
        $this->assertArrayHasKey('target_percent', $errors);
    }

    /** @test */
    public function update_valid_partial_passes(): void
    {
        $dto = new UpdateBudgetTargetDto(targetPercent: 50.0);
        $this->assertEmpty($dto->validate());
    }
}
