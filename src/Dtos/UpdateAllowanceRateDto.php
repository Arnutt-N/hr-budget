<?php

declare(strict_types=1);

namespace App\Dtos;

use App\Dtos\Concerns\RequestHelpers;

final class UpdateAllowanceRateDto
{
    use RequestHelpers;

    public function __construct(
        public readonly ?string $levelCode,
        public readonly ?string $lineCode,
        public readonly ?float $amount,
        public readonly ?float $percent,
        public readonly ?int $derivesFromTypeId,
        public readonly ?float $fallbackAmount,
        public readonly ?string $effectiveFrom,
        public readonly ?string $effectiveTo,
        public readonly ?string $docNo,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if ($this->amount !== null && $this->amount < 0) {
            $errors['amount'] = 'จำนวนเงินต้องไม่ติดลบ';
        }
        if ($this->percent !== null && ($this->percent < 0 || $this->percent > 100)) {
            $errors['percent'] = 'เปอร์เซ็นต์ต้องอยู่ระหว่าง 0-100';
        }
        if ($this->amount !== null && $this->percent !== null) {
            $errors['amount'] = 'ใส่ได้ทีละอย่าง: จำนวนเงิน หรือ เปอร์เซ็นต์';
        }
        if ($this->derivesFromTypeId !== null && $this->derivesFromTypeId <= 0) {
            $errors['derives_from_type_id'] = 'derives_from_type_id ไม่ถูกต้อง';
        }
        if ($this->fallbackAmount !== null && $this->fallbackAmount < 0) {
            $errors['fallback_amount'] = 'ยอดพื้นต้องไม่ติดลบ';
        }
        if ($this->effectiveFrom !== null && !self::isValidDate($this->effectiveFrom)) {
            $errors['effective_from'] = 'วันเริ่มมีผลไม่ถูกต้อง';
        }
        if ($this->effectiveTo !== null && !self::isValidDate($this->effectiveTo)) {
            $errors['effective_to'] = 'วันสิ้นสุดผลไม่ถูกต้อง';
        }

        return $errors;
    }

    public static function fromRequest(): self
    {
        $raw = self::jsonBody();

        $cleared = self::parseClearedFields(
            $raw,
            ['level_code', 'line_code', 'amount', 'percent', 'derives_from_type_id', 'fallback_amount', 'effective_to', 'doc_no'],
        );

        return (new self(
            levelCode: array_key_exists('level_code', $raw) ? self::nullableString($raw['level_code']) : null,
            lineCode: array_key_exists('line_code', $raw) ? self::nullableString($raw['line_code']) : null,
            amount: array_key_exists('amount', $raw)
                ? ($raw['amount'] === null ? null : (float) $raw['amount'])
                : null,
            percent: array_key_exists('percent', $raw)
                ? ($raw['percent'] === null ? null : (float) $raw['percent'])
                : null,
            derivesFromTypeId: array_key_exists('derives_from_type_id', $raw)
                ? ($raw['derives_from_type_id'] === null ? null : (int) $raw['derives_from_type_id'])
                : null,
            fallbackAmount: array_key_exists('fallback_amount', $raw)
                ? ($raw['fallback_amount'] === null ? null : (float) $raw['fallback_amount'])
                : null,
            effectiveFrom: array_key_exists('effective_from', $raw) ? (string) $raw['effective_from'] : null,
            effectiveTo: array_key_exists('effective_to', $raw) ? self::nullableString($raw['effective_to']) : null,
            docNo: array_key_exists('doc_no', $raw) ? self::nullableString($raw['doc_no']) : null,
        ))->withCleared($cleared);
    }

    /** แปลงเป็นชุดคอลัมน์ที่ repository อัปเดตได้ (null = ไม่แตะ · ผ่าน withCleared = ล้าง) */
    public function toUpdateData(): array
    {
        $map = [
            'level_code' => $this->levelCode,
            'line_code' => $this->lineCode,
            'amount' => $this->amount,
            'percent' => $this->percent,
            'derives_from_type_id' => $this->derivesFromTypeId,
            'fallback_amount' => $this->fallbackAmount,
            'effective_from' => $this->effectiveFrom,
            'effective_to' => $this->effectiveTo,
            'doc_no' => $this->docNo,
        ];

        return $this->applyCleared(array_filter($map, fn ($value) => $value !== null));
    }
}
