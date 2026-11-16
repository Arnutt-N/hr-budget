<?php

declare(strict_types=1);

namespace Tests\Unit\Dtos;

use App\Dtos\CreateCategoryDto;
use App\Dtos\CreateCategoryItemDto;
use App\Dtos\CreatePlanDto;
use App\Dtos\UpdateCategoryDto;
use App\Dtos\UpdateCategoryItemDto;
use App\Dtos\UpdatePlanDto;
use PHPUnit\Framework\TestCase;

class FiscalCodeDtoTest extends TestCase
{
    private const VALID_FISCAL = '00000000000001000000';
    private const BAD_CATEGORY = '00000000000009900000';
    private const VALID_ACTIVITY = '00000000000000001';

    /** @test */
    public function create_category_rejects_19_digit_fiscal_code(): void
    {
        $dto = new CreateCategoryDto(
            code: 'C1',
            nameTh: 'หมวดทดสอบ',
            fiscalCode: substr(self::VALID_FISCAL, 0, 19),
        );
        $errors = $dto->validate();
        $this->assertArrayHasKey('fiscal_code', $errors);
        $this->assertSame('รหัสงบประมาณต้องมี 20 หลัก', $errors['fiscal_code']);
    }

    /** @test */
    public function create_category_rejects_unknown_expense_category(): void
    {
        $dto = new CreateCategoryDto(code: 'C1', nameTh: 'หมวดทดสอบ', fiscalCode: self::BAD_CATEGORY);
        $errors = $dto->validate();
        $this->assertArrayHasKey('fiscal_code', $errors);
        $this->assertSame('หมวดหมู่รหัสงบประมาณไม่ถูกต้อง', $errors['fiscal_code']);
    }

    /** @test */
    public function create_category_accepts_valid_fiscal_code(): void
    {
        $dto = new CreateCategoryDto(code: 'C1', nameTh: 'หมวดทดสอบ', fiscalCode: self::VALID_FISCAL);
        $this->assertEmpty($dto->validate());
    }

    /** @test */
    public function create_category_rejects_trailing_newline(): void
    {
        $dto = new CreateCategoryDto(code: 'C1', nameTh: 'หมวดทดสอบ', fiscalCode: self::VALID_FISCAL . "\n");
        $this->assertArrayHasKey('fiscal_code', $dto->validate());
    }

    /** @test */
    public function create_category_without_fiscal_code_still_passes(): void
    {
        $dto = new CreateCategoryDto(code: 'C1', nameTh: 'หมวดทดสอบ');
        $this->assertEmpty($dto->validate());
    }

    /** @test */
    public function update_category_rejects_bad_fiscal_code(): void
    {
        $dto = new UpdateCategoryDto(fiscalCode: '12345');
        $errors = $dto->validate();
        $this->assertArrayHasKey('fiscal_code', $errors);
        $this->assertSame('รหัสงบประมาณต้องมี 20 หลัก', $errors['fiscal_code']);
    }

    /** @test */
    public function update_category_accepts_valid_fiscal_code(): void
    {
        $dto = new UpdateCategoryDto(fiscalCode: self::VALID_FISCAL);
        $this->assertEmpty($dto->validate());
    }

    /** @test */
    public function create_category_item_rejects_bad_fiscal_code(): void
    {
        $dto = new CreateCategoryItemDto(name: 'รายการ', fiscalCode: '12345');
        $errors = $dto->validate();
        $this->assertArrayHasKey('fiscal_code', $errors);
        $this->assertSame('รหัสงบประมาณต้องมี 20 หลัก', $errors['fiscal_code']);
    }

    /** @test */
    public function create_category_item_accepts_valid_fiscal_code(): void
    {
        $dto = new CreateCategoryItemDto(name: 'รายการ', fiscalCode: self::VALID_FISCAL);
        $this->assertEmpty($dto->validate());
    }

    /** @test */
    public function update_category_item_rejects_bad_fiscal_code(): void
    {
        $dto = new UpdateCategoryItemDto(fiscalCode: '12345');
        $errors = $dto->validate();
        $this->assertArrayHasKey('fiscal_code', $errors);
        $this->assertSame('รหัสงบประมาณต้องมี 20 หลัก', $errors['fiscal_code']);
    }

    /** @test */
    public function update_category_item_accepts_valid_fiscal_code(): void
    {
        $dto = new UpdateCategoryItemDto(fiscalCode: self::VALID_FISCAL);
        $this->assertEmpty($dto->validate());
    }

    /** @test */
    public function create_plan_rejects_16_digit_activity_code(): void
    {
        $dto = new CreatePlanDto(
            code: 'PLN-1',
            nameTh: 'แผนทดสอบ',
            nameEn: null,
            description: null,
            fiscalYear: 2569,
            activityCode: substr(self::VALID_ACTIVITY, 0, 16),
        );
        $errors = $dto->validate();
        $this->assertArrayHasKey('activity_code', $errors);
        $this->assertSame('รหัสกิจกรรมหลักต้องมี 17 หลัก', $errors['activity_code']);
    }

    /** @test */
    public function create_plan_accepts_17_digit_activity_code(): void
    {
        $dto = new CreatePlanDto(
            code: 'PLN-1',
            nameTh: 'แผนทดสอบ',
            nameEn: null,
            description: null,
            fiscalYear: 2569,
            activityCode: self::VALID_ACTIVITY,
        );
        $this->assertEmpty($dto->validate());
    }

    /** @test */
    public function create_plan_rejects_trailing_newline(): void
    {
        $dto = new CreatePlanDto(
            code: 'PLN-1',
            nameTh: 'แผนทดสอบ',
            nameEn: null,
            description: null,
            fiscalYear: 2569,
            activityCode: self::VALID_ACTIVITY . "\n",
        );
        $this->assertArrayHasKey('activity_code', $dto->validate());
    }

    /** @test */
    public function update_plan_rejects_bad_activity_code(): void
    {
        $dto = new UpdatePlanDto(activityCode: '12345');
        $errors = $dto->validate();
        $this->assertArrayHasKey('activity_code', $errors);
        $this->assertSame('รหัสกิจกรรมหลักต้องมี 17 หลัก', $errors['activity_code']);
    }

    /** @test */
    public function update_plan_accepts_valid_activity_code(): void
    {
        $dto = new UpdatePlanDto(activityCode: self::VALID_ACTIVITY);
        $this->assertEmpty($dto->validate());
    }
}
