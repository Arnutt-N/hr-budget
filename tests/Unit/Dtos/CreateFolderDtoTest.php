<?php

declare(strict_types=1);

namespace Tests\Unit\Dtos;

use App\Dtos\CreateFolderDto;
use PHPUnit\Framework\TestCase;

final class CreateFolderDtoTest extends TestCase
{
    public function testValidFolderHasNoErrors(): void
    {
        $dto = new CreateFolderDto(name: 'งบบุคลากร', fiscalYear: 2569);

        $this->assertSame([], $dto->validate());
    }

    public function testMissingNameFails(): void
    {
        $dto = new CreateFolderDto(name: '   ', fiscalYear: 2569);
        $errors = $dto->validate();

        $this->assertArrayHasKey('name', $errors);
    }

    public function testNameTooLongFails(): void
    {
        $dto = new CreateFolderDto(name: str_repeat('ก', 256), fiscalYear: 2569);
        $errors = $dto->validate();

        $this->assertArrayHasKey('name', $errors);
    }

    public function testMissingYearAndParentFails(): void
    {
        $dto = new CreateFolderDto(name: 'x');
        $errors = $dto->validate();

        $this->assertArrayHasKey('fiscal_year', $errors);
    }

    public function testParentOnlyIsValid(): void
    {
        // A child folder supplies parentId instead of fiscalYear.
        $dto = new CreateFolderDto(name: 'ลูก', parentId: 3);

        $this->assertSame([], $dto->validate());
    }

    public function testNameWithPathSeparatorFails(): void
    {
        $dto = new CreateFolderDto(name: '../evil', fiscalYear: 2569);

        $this->assertArrayHasKey('name', $dto->validate());
    }
}
