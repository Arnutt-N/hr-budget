<?php

declare(strict_types=1);

namespace App\Dtos;

final class CreateFolderDto
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?int $parentId = null,
        public readonly ?int $fiscalYear = null,
        public readonly ?string $description = null,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        $name = trim((string) $this->name);
        if ($name === '') {
            $errors['name'] = 'กรุณาระบุชื่อโฟลเดอร์';
        } elseif (mb_strlen($name) > 255) {
            $errors['name'] = 'ชื่อโฟลเดอร์ต้องไม่เกิน 255 ตัวอักษร';
        } elseif (preg_match('#[/\\\\]#', $name) === 1) {
            // Defense-in-depth: a folder name must not contain path separators.
            $errors['name'] = 'ชื่อโฟลเดอร์ต้องไม่มีเครื่องหมาย / หรือ \\';
        }

        if ($this->parentId === null && $this->fiscalYear === null) {
            $errors['fiscal_year'] = 'กรุณาระบุปีงบประมาณหรือโฟลเดอร์แม่';
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

        $parentId = isset($raw['parent_id']) && $raw['parent_id'] !== ''
            ? (int) $raw['parent_id'] : null;
        $fiscalYear = isset($raw['fiscal_year']) && $raw['fiscal_year'] !== ''
            ? (int) $raw['fiscal_year'] : null;
        $description = isset($raw['description']) && trim((string) $raw['description']) !== ''
            ? trim((string) $raw['description']) : null;

        return new self(
            name: isset($raw['name']) ? trim((string) $raw['name']) : null,
            parentId: $parentId,
            fiscalYear: $fiscalYear,
            description: $description,
        );
    }
}
