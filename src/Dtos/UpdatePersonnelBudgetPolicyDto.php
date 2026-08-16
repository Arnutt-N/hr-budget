<?php

declare(strict_types=1);

namespace App\Dtos;

use App\Dtos\Concerns\RequestHelpers;

final class UpdatePersonnelBudgetPolicyDto
{
    use RequestHelpers;

    public function __construct(
        public readonly ?string $vacancyRule,
        public readonly ?string $calcMode,
        public readonly ?float $bufferPercent,
        public readonly ?string $referenceDate,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if ($this->vacancyRule !== null
            && !in_array($this->vacancyRule, ['transfer_request', 'eligibility_list', 'ready_to_fill'], true)) {
            $errors['vacancy_rule'] = 'เกณฑ์อัตราว่างไม่ถูกต้อง';
        }
        if ($this->calcMode !== null && !in_array($this->calcMode, ['snapshot', 'prorate'], true)) {
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

        $cleared = self::parseClearedFields($raw, ['vacancy_rule', 'buffer_percent', 'reference_date']);

        return (new self(
            vacancyRule: array_key_exists('vacancy_rule', $raw) ? self::nullableString($raw['vacancy_rule']) : null,
            calcMode: array_key_exists('calc_mode', $raw) ? (string) $raw['calc_mode'] : null,
            bufferPercent: array_key_exists('buffer_percent', $raw)
                ? ($raw['buffer_percent'] === null ? null : (float) $raw['buffer_percent'])
                : null,
            referenceDate: array_key_exists('reference_date', $raw) ? self::nullableString($raw['reference_date']) : null,
        ))->withCleared($cleared);
    }

    /** แปลงเป็นชุดคอลัมน์ที่ repository อัปเดตได้ (null = ไม่แตะ · ผ่าน withCleared = ล้าง) */
    public function toUpdateData(): array
    {
        $map = [
            'vacancy_rule' => $this->vacancyRule,
            'calc_mode' => $this->calcMode,
            'buffer_percent' => $this->bufferPercent,
            'reference_date' => $this->referenceDate,
        ];
        return $this->applyCleared(array_filter($map, fn ($value) => $value !== null));
    }
}
