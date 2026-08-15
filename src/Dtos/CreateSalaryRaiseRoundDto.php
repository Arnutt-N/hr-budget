<?php

declare(strict_types=1);

namespace App\Dtos;

final class CreateSalaryRaiseRoundDto
{
    public function __construct(
        public readonly string $roundMonth,
        public readonly int $roundYearBe,
        public readonly string $effectiveDate,
        public readonly int $fiscalYearId,
        public readonly bool $includeInBudget,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if (!in_array($this->roundMonth, ['apr', 'oct'], true)) {
            $errors['round_month'] = 'รอบเลื่อนต้องเป็น เม.ย. (apr) หรือ ต.ค. (oct)';
        }

        if ($this->roundYearBe < 2400 || $this->roundYearBe > 2700) {
            $errors['round_year_be'] = 'ปี พ.ศ. ของรอบไม่ถูกต้อง';
        }

        if (!$this->isValidDate($this->effectiveDate)) {
            $errors['effective_date'] = 'วันที่มีผลไม่ถูกต้อง';
        }

        if ($this->fiscalYearId <= 0) {
            $errors['fiscal_year_id'] = 'กรุณาระบุปีงบประมาณ';
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
            roundMonth: (string) ($raw['round_month'] ?? ''),
            roundYearBe: (int) ($raw['round_year_be'] ?? 0),
            effectiveDate: trim((string) ($raw['effective_date'] ?? '')),
            fiscalYearId: (int) ($raw['fiscal_year_id'] ?? 0),
            includeInBudget: (bool) ($raw['include_in_budget'] ?? false),
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
