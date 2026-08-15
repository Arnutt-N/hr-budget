<?php

declare(strict_types=1);

namespace App\Dtos;

final class CreateSalaryScaleDto
{
    public function __construct(
        public readonly string $employeeCategory,
        public readonly string $levelCode,
        public readonly float $minAmount,
        public readonly float $maxAmount,
        public readonly string $effectiveFrom,
        public readonly ?string $effectiveTo,
        public readonly ?string $docNo,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if (!in_array($this->employeeCategory, CreatePositionDto::CATEGORIES, true)) {
            $errors['employee_category'] = 'ประเภทบุคลากรไม่ถูกต้อง';
        }

        if ($this->levelCode === '') {
            $errors['level_code'] = 'กรุณาระบุระดับตำแหน่ง';
        }

        if ($this->minAmount < 0 || $this->maxAmount < 0) {
            $errors['min_amount'] = 'อัตราเงินเดือนต้องไม่ติดลบ';
        } elseif ($this->maxAmount < $this->minAmount) {
            $errors['max_amount'] = 'อัตราขั้นสูงต้องไม่ต่ำกว่าขั้นต่ำ';
        }

        if (!$this->isValidDate($this->effectiveFrom)) {
            $errors['effective_from'] = 'วันเริ่มมีผลไม่ถูกต้อง';
        }
        if ($this->effectiveTo !== null && !$this->isValidDate($this->effectiveTo)) {
            $errors['effective_to'] = 'วันสิ้นสุดผลไม่ถูกต้อง';
        } elseif ($this->effectiveTo !== null && $this->effectiveTo < $this->effectiveFrom) {
            $errors['effective_to'] = 'วันสิ้นสุดผลต้องไม่ก่อนวันเริ่มมีผล';
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
            employeeCategory: (string) ($raw['employee_category'] ?? ''),
            levelCode: trim((string) ($raw['level_code'] ?? '')),
            minAmount: (float) ($raw['min_amount'] ?? 0),
            maxAmount: (float) ($raw['max_amount'] ?? 0),
            effectiveFrom: trim((string) ($raw['effective_from'] ?? '')),
            effectiveTo: self::nullableString($raw['effective_to'] ?? null),
            docNo: self::nullableString($raw['doc_no'] ?? null),
        );
    }

    private static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $trimmed = trim((string) $value);
        return $trimmed === '' ? null : $trimmed;
    }

    private function isValidDate(string $date): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }
        $parts = explode('-', $date);
        return checkdate((int) $parts[1], (int) $parts[2], (int) $parts[0]);
    }
}
