<?php

declare(strict_types=1);

namespace App\Dtos;

final class UpdatePositionDto
{
    public function __construct(
        public readonly ?string $payNo,
        public readonly ?string $employeeCategory,
        public readonly ?string $createdDocNo,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if ($this->payNo !== null && trim($this->payNo) === '') {
            $errors['pay_no'] = 'เลขถือจ่ายห้ามเป็นค่าว่าง';
        }

        if ($this->employeeCategory !== null
            && !in_array($this->employeeCategory, CreatePositionDto::CATEGORIES, true)) {
            $errors['employee_category'] = 'ประเภทบุคลากรไม่ถูกต้อง';
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
            payNo: array_key_exists('pay_no', $raw) ? (string) $raw['pay_no'] : null,
            employeeCategory: array_key_exists('employee_category', $raw) ? (string) $raw['employee_category'] : null,
            createdDocNo: array_key_exists('created_doc_no', $raw)
                ? (trim((string) $raw['created_doc_no']) !== '' ? trim((string) $raw['created_doc_no']) : null)
                : null,
        );
    }
}
