<?php
declare(strict_types=1);

namespace App\Dtos;

final class UpdateRoleDto
{
    /**
     * @param array<int,string>|null $permissions null = leave unchanged
     */
    public function __construct(
        public readonly ?string $nameTh = null,
        public readonly ?string $nameEn = null,
        public readonly ?string $description = null,
        public readonly ?bool $isActive = null,
        public readonly ?int $sortOrder = null,
        public readonly ?array $permissions = null,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];
        if ($this->nameTh !== null && $this->nameTh === '') {
            $errors['name_th'] = 'ชื่อบทบาทไม่ควรเป็นค่าว่าง';
        }
        if ($this->nameTh !== null && mb_strlen($this->nameTh) > 255) {
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
            nameTh: array_key_exists('name_th', $raw) ? trim((string) $raw['name_th']) : null,
            nameEn: array_key_exists('name_en', $raw) ? trim((string) $raw['name_en']) : null,
            description: array_key_exists('description', $raw) ? trim((string) $raw['description']) : null,
            isActive: array_key_exists('is_active', $raw) ? (bool) $raw['is_active'] : null,
            sortOrder: array_key_exists('sort_order', $raw) ? (int) $raw['sort_order'] : null,
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
