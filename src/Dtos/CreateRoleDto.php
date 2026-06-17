<?php
declare(strict_types=1);

namespace App\Dtos;

final class CreateRoleDto
{
    /**
     * @param array<int,string>|null $permissions permission codes to assign
     */
    public function __construct(
        public readonly string $code,
        public readonly string $nameTh,
        public readonly ?string $nameEn = null,
        public readonly ?string $description = null,
        public readonly ?array $permissions = null,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];
        if ($this->code === '') {
            $errors['code'] = 'กรุณาระบุรหัสบทบาท';
        } elseif (!preg_match('/^[a-z][a-z0-9_]{1,49}$/', $this->code)) {
            $errors['code'] = 'รหัสบทบาทต้องเป็น a-z, 0-9, _ และขึ้นต้นด้วยตัวอักษร (≤50)';
        }
        if ($this->nameTh === '') {
            $errors['name_th'] = 'กรุณาระบุชื่อบทบาท';
        } elseif (mb_strlen($this->nameTh) > 255) {
            $errors['name_th'] = 'ชื่อบทบาทต้องไม่เกิน 255 ตัวอักษร';
        }
        if ($this->permissions !== null) {
            foreach ($this->permissions as $p) {
                if (!is_string($p)) {
                    $errors['permissions'] = 'รายการสิทธิ์ไม่ถูกต้อง';
                    break;
                }
            }
        }
        return $errors;
    }

    public static function fromRequest(): self
    {
        $raw = self::body();
        $perms = $raw['permissions'] ?? null;
        return new self(
            code: trim((string) ($raw['code'] ?? '')),
            nameTh: trim((string) ($raw['name_th'] ?? '')),
            nameEn: isset($raw['name_en']) ? trim((string) $raw['name_en']) : null,
            description: isset($raw['description']) ? trim((string) $raw['description']) : null,
            permissions: is_array($perms) ? array_values($perms) : null,
        );
    }

    /** @return array<string,mixed> */
    private static function body(): array
    {
        $body = file_get_contents('php://input');
        if ($body !== false && $body !== '') {
            $decoded = json_decode($body, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        return [];
    }
}
