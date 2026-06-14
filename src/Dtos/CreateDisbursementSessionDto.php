<?php

declare(strict_types=1);

namespace App\Dtos;

/**
 * Create-or-fetch a disbursement session (organization + fiscal year + month).
 *
 * Idempotent at the service layer: an existing session for the same
 * (organization_id, fiscal_year, record_month) is reused.
 */
final class CreateDisbursementSessionDto
{
    public function __construct(
        public readonly int $organizationId,
        public readonly int $fiscalYear,
        public readonly int $recordMonth,
        public readonly string $recordDate,
    ) {}

    /** @return array<string,string> field → Thai error message (empty = valid) */
    public function validate(): array
    {
        $errors = [];

        if ($this->organizationId <= 0) {
            $errors['organization_id'] = 'กรุณาเลือกหน่วยงาน';
        }

        if ($this->fiscalYear < 2400 || $this->fiscalYear > 2700) {
            $errors['fiscal_year'] = 'ปีงบประมาณต้องอยู่ระหว่าง 2400-2700';
        }

        if ($this->recordMonth < 1 || $this->recordMonth > 12) {
            $errors['record_month'] = 'เดือนต้องอยู่ระหว่าง 1-12';
        }

        if (!$this->isValidDate($this->recordDate)) {
            $errors['record_date'] = 'รูปแบบวันที่ไม่ถูกต้อง (YYYY-MM-DD)';
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

        $recordDate = trim((string) ($raw['record_date'] ?? ''));
        if ($recordDate === '') {
            $recordDate = date('Y-m-d');
        }

        return new self(
            organizationId: (int) ($raw['organization_id'] ?? 0),
            fiscalYear: (int) ($raw['fiscal_year'] ?? 0),
            recordMonth: (int) ($raw['record_month'] ?? 0),
            recordDate: $recordDate,
        );
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
