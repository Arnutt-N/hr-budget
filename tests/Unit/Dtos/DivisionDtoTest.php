<?php

declare(strict_types=1);

namespace Tests\Unit\Dtos;

use PHPUnit\Framework\TestCase;
use App\Dtos\CreateDivisionDto;
use App\Dtos\UpdateDivisionDto;

class DivisionDtoTest extends TestCase
{
    /** @test */
    public function create_valid_division_passes(): void
    {
        // Arrange
        $dto = new CreateDivisionDto(code: 'DIV01', nameTh: 'กองยุทธศาสตร์');

        // Act
        $errors = $dto->validate();

        // Assert
        $this->assertEmpty($errors);
    }

    /** @test */
    public function create_missing_code_fails(): void
    {
        // Arrange
        $dto = new CreateDivisionDto(code: '', nameTh: 'กองยุทธศาสตร์');

        // Act
        $errors = $dto->validate();

        // Assert
        $this->assertArrayHasKey('code', $errors);
    }

    /** @test */
    public function create_code_too_long_fails(): void
    {
        // Arrange
        $dto = new CreateDivisionDto(code: str_repeat('A', 21), nameTh: 'กองยุทธศาสตร์');

        // Act
        $errors = $dto->validate();

        // Assert
        $this->assertArrayHasKey('code', $errors);
    }

    /** @test */
    public function create_missing_name_th_fails(): void
    {
        // Arrange
        $dto = new CreateDivisionDto(code: 'DIV01', nameTh: '');

        // Act
        $errors = $dto->validate();

        // Assert
        $this->assertArrayHasKey('name_th', $errors);
    }

    /** @test */
    public function create_invalid_type_fails(): void
    {
        // Arrange
        $dto = new CreateDivisionDto(code: 'DIV01', nameTh: 'กองยุทธศาสตร์', type: 'bogus');

        // Act
        $errors = $dto->validate();

        // Assert
        $this->assertArrayHasKey('type', $errors);
    }

    /** @test */
    public function update_valid_partial_passes(): void
    {
        // Arrange
        $dto = new UpdateDivisionDto(nameTh: 'กองใหม่');

        // Act
        $errors = $dto->validate();

        // Assert
        $this->assertEmpty($errors);
    }

    /** @test */
    public function update_invalid_type_fails(): void
    {
        // Arrange
        $dto = new UpdateDivisionDto(type: 'bogus');

        // Act
        $errors = $dto->validate();

        // Assert
        $this->assertArrayHasKey('type', $errors);
    }
}
