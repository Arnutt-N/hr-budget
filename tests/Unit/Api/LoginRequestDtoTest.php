<?php

namespace Tests\Unit\Api;

use PHPUnit\Framework\TestCase;
use App\Dtos\LoginRequestDto;

class LoginRequestDtoTest extends TestCase
{
    /** @test */
    public function valid_credentials_produce_no_errors(): void
    {
        $dto = new LoginRequestDto('user@example.com', 'pass1234');
        $this->assertSame([], $dto->validate());
    }

    /** @test */
    public function empty_email_returns_email_error(): void
    {
        $errors = (new LoginRequestDto('', 'pw12'))->validate();
        $this->assertArrayHasKey('email', $errors);
    }

    /** @test */
    public function malformed_email_returns_email_error(): void
    {
        $errors = (new LoginRequestDto('notanemail', 'pw12'))->validate();
        $this->assertArrayHasKey('email', $errors);
        $this->assertStringContainsString('รูปแบบ', $errors['email']);
    }

    /** @test */
    public function empty_password_returns_password_error(): void
    {
        $errors = (new LoginRequestDto('u@x.co', ''))->validate();
        $this->assertArrayHasKey('password', $errors);
    }

    /** @test */
    public function short_password_returns_password_error(): void
    {
        $errors = (new LoginRequestDto('u@x.co', 'ab'))->validate();
        $this->assertArrayHasKey('password', $errors);
    }
}
