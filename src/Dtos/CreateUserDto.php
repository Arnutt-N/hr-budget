<?php

declare(strict_types=1);

namespace App\Dtos;

final class CreateUserDto
{
    private const ROLES = ['admin', 'editor', 'viewer'];

    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly string $name,
        public readonly string $role = 'viewer',
        public readonly bool $isActive = true,
        public readonly ?string $department = null,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if ($this->email === '') {
            $errors['email'] = 'กรุณาระบุอีเมล';
        } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'รูปแบบอีเมลไม่ถูกต้อง';
        }

        if ($this->password === '') {
            $errors['password'] = 'กรุณาระบุรหัสผ่าน';
        } elseif (mb_strlen($this->password) < 6) {
            $errors['password'] = 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร';
        }

        if ($this->name === '') {
            $errors['name'] = 'กรุณาระบุชื่อ';
        }

        if (!in_array($this->role, self::ROLES, true)) {
            $errors['role'] = 'บทบาทไม่ถูกต้อง';
        }

        return $errors;
    }

    public static function fromRequest(): self
    {
        $raw = [];
        $body = file_get_contents('php://input');
        if ($body !== false && $body !== '') {
            $decoded = json_decode($body, true);
            if (is_array($decoded)) {
                $raw = $decoded;
            }
        }

        return new self(
            email: trim((string) ($raw['email'] ?? '')),
            password: $raw['password'] ?? '',
            name: trim((string) ($raw['name'] ?? '')),
            role: $raw['role'] ?? 'viewer',
            isActive: (bool) ($raw['is_active'] ?? true),
            department: $raw['department'] ?? null,
        );
    }
}
