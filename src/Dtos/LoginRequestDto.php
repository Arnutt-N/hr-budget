<?php
/**
 * Login request payload — typed + self-validating.
 *
 * Usage:
 *     $dto = LoginRequestDto::fromRequest();
 *     $errors = $dto->validate();
 */

namespace App\Dtos;

final class LoginRequestDto
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
    ) {}

    /**
     * @return array<string,string>  map of field → error message (empty = valid)
     */
    public function validate(): array
    {
        $errors = [];

        if ($this->email === '') {
            $errors['email'] = 'กรุณากรอกอีเมล';
        } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'รูปแบบอีเมลไม่ถูกต้อง';
        }

        if ($this->password === '') {
            $errors['password'] = 'กรุณากรอกรหัสผ่าน';
        } elseif (strlen($this->password) < 4) {
            $errors['password'] = 'รหัสผ่านต้องมีอย่างน้อย 4 ตัวอักษร';
        }

        return $errors;
    }

    /**
     * Parse JSON body into a DTO. Missing/non-string fields become empty string.
     */
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
            password: (string) ($raw['password'] ?? ''),
        );
    }
}
