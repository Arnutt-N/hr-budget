<?php

declare(strict_types=1);

namespace Tests\Unit\Dtos;

use PHPUnit\Framework\TestCase;
use App\Dtos\CreateTargetTypeDto;
use App\Dtos\UpdateTargetTypeDto;

class TargetTypeDtoTest extends TestCase
{
    /** @test */
    public function create_valid_passes(): void
    {
        $dto = new CreateTargetTypeDto('TT01', 'บุคลากร');
        $this->assertEmpty($dto->validate());
    }

    /** @test */
    public function create_missing_code_fails(): void
    {
        $dto = new CreateTargetTypeDto('', 'บุคลากร');
        $errors = $dto->validate();
        $this->assertArrayHasKey('code', $errors);
    }

    /** @test */
    public function create_code_too_long_fails(): void
    {
        $dto = new CreateTargetTypeDto(str_repeat('A', 51), 'บุคลากร');
        $errors = $dto->validate();
        $this->assertArrayHasKey('code', $errors);
    }

    /** @test */
    public function create_missing_name_th_fails(): void
    {
        $dto = new CreateTargetTypeDto('TT01', '');
        $errors = $dto->validate();
        $this->assertArrayHasKey('name_th', $errors);
    }

    /** @test */
    public function update_valid_partial_passes(): void
    {
        $dto = new UpdateTargetTypeDto(nameTh: 'หน่วยงาน');
        $this->assertEmpty($dto->validate());
    }
}
