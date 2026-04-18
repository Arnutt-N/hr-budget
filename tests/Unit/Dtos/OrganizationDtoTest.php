<?php

declare(strict_types=1);

namespace Tests\Unit\Dtos;

use PHPUnit\Framework\TestCase;
use App\Dtos\CreateOrganizationDto;
use App\Dtos\UpdateOrganizationDto;

class OrganizationDtoTest extends TestCase
{
    /** @test */
    public function create_valid_passes(): void
    {
        $dto = new CreateOrganizationDto('DEPT01', 'กองบัญชีการคลัง');
        $this->assertEmpty($dto->validate());
    }

    /** @test */
    public function create_empty_code_fails(): void
    {
        $dto = new CreateOrganizationDto('', 'กองบัญชี');
        $errors = $dto->validate();
        $this->assertArrayHasKey('code', $errors);
    }

    /** @test */
    public function create_empty_name_fails(): void
    {
        $dto = new CreateOrganizationDto('DEPT01', '');
        $errors = $dto->validate();
        $this->assertArrayHasKey('name_th', $errors);
    }

    /** @test */
    public function create_invalid_org_type_fails(): void
    {
        $dto = new CreateOrganizationDto('DEPT01', 'กองบัญชี', orgType: 'invalid');
        $errors = $dto->validate();
        $this->assertArrayHasKey('org_type', $errors);
    }

    /** @test */
    public function create_valid_org_type_passes(): void
    {
        $dto = new CreateOrganizationDto('DEPT01', 'กองบัญชี', orgType: 'department');
        $this->assertEmpty($dto->validate());
    }

    /** @test */
    public function create_code_too_long_fails(): void
    {
        $dto = new CreateOrganizationDto(str_repeat('A', 51), 'กองบัญชี');
        $errors = $dto->validate();
        $this->assertArrayHasKey('code', $errors);
    }

    /** @test */
    public function update_invalid_org_type_fails(): void
    {
        $dto = new UpdateOrganizationDto(orgType: 'notvalid');
        $errors = $dto->validate();
        $this->assertArrayHasKey('org_type', $errors);
    }

    /** @test */
    public function update_empty_name_fails(): void
    {
        $dto = new UpdateOrganizationDto(nameTh: '');
        $errors = $dto->validate();
        $this->assertArrayHasKey('name_th', $errors);
    }
}
