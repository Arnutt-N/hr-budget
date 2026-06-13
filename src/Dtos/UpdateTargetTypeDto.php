<?php

declare(strict_types=1);

namespace App\Dtos;

final class UpdateTargetTypeDto
{
    public function __construct(
        public readonly ?string $code = null,
        public readonly ?string $nameTh = null,
        public readonly ?string $description = null,
        public readonly ?bool $isActive = null,
        public readonly ?int $sortOrder = null,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if ($this->code !== null) {
            if ($this->code === '') {
                $errors['code'] = 'กรุณาระบุรหัสประเภทเป้าหมาย';
            } elseif (mb_strlen($this->code) > 50) {
                $errors['code'] = 'รหัสต้องไม่เกิน 50 ตัวอักษร';
            }
        }

        if ($this->nameTh !== null) {
            if ($this->nameTh === '') {
                $errors['name_th'] = 'กรุณาระบุชื่อประเภทเป้าหมาย';
            } elseif (mb_strlen($this->nameTh) > 255) {
                $errors['name_th'] = 'ชื่อต้องไม่เกิน 255 ตัวอักษร';
            }
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
            code: array_key_exists('code', $raw) ? trim((string) $raw['code']) : null,
            nameTh: array_key_exists('name_th', $raw) ? trim((string) $raw['name_th']) : null,
            description: array_key_exists('description', $raw)
                ? ($raw['description'] !== null ? trim((string) $raw['description']) : null)
                : null,
            isActive: array_key_exists('is_active', $raw) ? (bool) $raw['is_active'] : null,
            sortOrder: array_key_exists('sort_order', $raw) ? (int) $raw['sort_order'] : null,
        );
    }
}
