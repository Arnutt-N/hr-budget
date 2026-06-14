<?php

declare(strict_types=1);

namespace App\Dtos;

final class CreateTargetTypeDto
{
    public function __construct(
        public readonly string $code,
        public readonly string $nameTh,
        public readonly ?string $description = null,
        public readonly bool $isActive = true,
        public readonly int $sortOrder = 0,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if ($this->code === '') {
            $errors['code'] = 'กรุณาระบุรหัสประเภทเป้าหมาย';
        } elseif (mb_strlen($this->code) > 50) {
            $errors['code'] = 'รหัสต้องไม่เกิน 50 ตัวอักษร';
        }

        if ($this->nameTh === '') {
            $errors['name_th'] = 'กรุณาระบุชื่อประเภทเป้าหมาย';
        } elseif (mb_strlen($this->nameTh) > 255) {
            $errors['name_th'] = 'ชื่อต้องไม่เกิน 255 ตัวอักษร';
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
            code: trim((string) ($raw['code'] ?? '')),
            nameTh: trim((string) ($raw['name_th'] ?? '')),
            description: array_key_exists('description', $raw) && $raw['description'] !== null
                ? trim((string) $raw['description'])
                : null,
            isActive: (bool) ($raw['is_active'] ?? true),
            sortOrder: (int) ($raw['sort_order'] ?? 0),
        );
    }
}
