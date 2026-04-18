<?php

declare(strict_types=1);

namespace Tests\Unit\Dtos;

use PHPUnit\Framework\TestCase;
use App\Dtos\CreateUserDto;
use App\Dtos\UpdateUserDto;

class UserDtoTest extends TestCase
{
    /** @test */
    public function create_valid_passes(): void
    {
        $dto = new CreateUserDto('admin@example.com', 'password123', 'Admin');
        $this->assertEmpty($dto->validate());
    }

    /** @test */
    public function create_empty_email_fails(): void
    {
        $dto = new CreateUserDto('', 'password123', 'Admin');
        $errors = $dto->validate();
        $this->assertArrayHasKey('email', $errors);
    }

    /** @test */
    public function create_invalid_email_fails(): void
    {
        $dto = new CreateUserDto('not-an-email', 'password123', 'Admin');
        $errors = $dto->validate();
        $this->assertArrayHasKey('email', $errors);
    }

    /** @test */
    public function create_short_password_fails(): void
    {
        $dto = new CreateUserDto('admin@example.com', '123', 'Admin');
        $errors = $dto->validate();
        $this->assertArrayHasKey('password', $errors);
    }

    /** @test */
    public function create_empty_password_fails(): void
    {
        $dto = new CreateUserDto('admin@example.com', '', 'Admin');
        $errors = $dto->validate();
        $this->assertArrayHasKey('password', $errors);
    }

    /** @test */
    public function create_empty_name_fails(): void
    {
        $dto = new CreateUserDto('admin@example.com', 'password123', '');
        $errors = $dto->validate();
        $this->assertArrayHasKey('name', $errors);
    }

    /** @test */
    public function create_invalid_role_fails(): void
    {
        $dto = new CreateUserDto('admin@example.com', 'password123', 'Admin', role: 'superadmin');
        $errors = $dto->validate();
        $this->assertArrayHasKey('role', $errors);
    }

    /** @test */
    public function create_valid_roles_pass(): void
    {
        foreach (['admin', 'editor', 'viewer'] as $role) {
            $dto = new CreateUserDto('admin@example.com', 'password123', 'Admin', role: $role);
            $this->assertEmpty($dto->validate(), "Role '{$role}' should be valid");
        }
    }

    /** @test */
    public function update_invalid_email_fails(): void
    {
        $dto = new UpdateUserDto(email: 'bad-email');
        $errors = $dto->validate();
        $this->assertArrayHasKey('email', $errors);
    }

    /** @test */
    public function update_short_password_fails(): void
    {
        $dto = new UpdateUserDto(password: '12');
        $errors = $dto->validate();
        $this->assertArrayHasKey('password', $errors);
    }

    /** @test */
    public function update_invalid_role_fails(): void
    {
        $dto = new UpdateUserDto(role: 'hacker');
        $errors = $dto->validate();
        $this->assertArrayHasKey('role', $errors);
    }
}
