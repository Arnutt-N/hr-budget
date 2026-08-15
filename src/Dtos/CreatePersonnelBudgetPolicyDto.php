<?php

declare(strict_types=1);

namespace App\Dtos;

use App\Dtos\Concerns\RequestHelpers;

final class CreatePersonnelBudgetPolicyDto
{
    use RequestHelpers;

    public function __construct(
        public readonly int $fiscalYearId,
        public readonly ?string $vacancyRule,
        public readonly string $calcMode,
        public readonly ?float $bufferPercent,
        public readonly ?string $referenceDate,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if ($this->fiscalYearId <= 0) {
            $errors['fiscal_year_id'] = 'กรุณาระบุปีงบประมาณ';
        }
        if ($this->vacancyRule !== null
            && !in_array($this->vacancyRule, ['transfer_request', 'eligibility_list', 'ready_to_fill'], true)) {
            $errors['vacancy_rule'] = 'เกณฑ์อัตราว่างไม่ถูกต้อง';
        }
        if (!in_array($this->calcMode, ['snapshot', 'prorate'], true)) {
            $errors['calc_mode'] = 'calc_mode ต้องเป็น snapshot หรือ prorate';
        }
        if ($this->bufferPercent !== null && ($this->bufferPercent < 0 || $this->bufferPercent > 100)) {
            $errors['buffer_percent'] = 'ช่องปรับต้องอยู่ระหว่าง 0-100';
        }
        if ($this->referenceDate !== null && !self::isValidDate($this->referenceDate)) {
            $errors['reference_date'] = 'วันอ้างอิงไม่ถูกต้อง';
        }

        return $errors;
    }

    public static function fromRequest(): self
    {
        $raw = self::jsonBody();

        return new self(
            fiscalYearId: (int) ($raw['fiscal_year_id'] ?? 0),
            vacancyRule: self::nullableString($raw['vacancy_rule'] ?? null),
            calcMode: (string) ($raw['calc_mode'] ?? 'prorate'),
            bufferPercent: isset($raw['buffer_percent']) ? (float) $raw['buffer_percent'] : null,
            referenceDate: self::nullableString($raw['reference_date'] ?? null),
        );
    }
}
