<?php

declare(strict_types=1);

namespace App\Dtos;

final class CreateAllowanceRateDto
{
    public function __construct(
        public readonly int $allowanceTypeId,
        public readonly ?string $levelCode,
        public readonly ?string $lineCode,
        public readonly ?float $amount,
        public readonly ?float $percent,
        public readonly ?int $derivesFromTypeId,
        public readonly ?float $fallbackAmount,
        public readonly string $effectiveFrom,
        public readonly ?string $effectiveTo,
        public readonly ?string $docNo,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if ($this->allowanceTypeId <= 0) {
            $errors['allowance_type_id'] = 'กรุณาระบุชนิดเงินเพิ่ม';
        }

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
        if ($this->derivesFromTypeId !== null && ($this->amount !== null || $this->percent !== null)) {
            $errors['derives_from_type_id'] = 'derived อ้างอิงตัวอื่นแล้ว — ห้ามใส่ amount/percent';
        }
        if ($this->derivesFromTypeId === null && $this->fallbackAmount !== null) {
            $errors['fallback_amount'] = 'fallback ใช้ได้เฉพาะอัตราแบบ derived';
        }
        if ($this->fallbackAmount !== null && $this->fallbackAmount < 0) {
            $errors['fallback_amount'] = 'ยอดพื้นต้องไม่ติดลบ';
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

        return self::fromArray($raw);
    }

    /** @param array<string,mixed> $raw */
    public static function fromArray(array $raw): self
    {
        return new self(
            allowanceTypeId: (int) ($raw['allowance_type_id'] ?? 0),
            levelCode: self::nullableString($raw['level_code'] ?? null),
            lineCode: self::nullableString($raw['line_code'] ?? null),
            amount: isset($raw['amount']) ? (float) $raw['amount'] : null,
            percent: isset($raw['percent']) ? (float) $raw['percent'] : null,
            derivesFromTypeId: isset($raw['derives_from_type_id']) ? (int) $raw['derives_from_type_id'] : null,
            fallbackAmount: isset($raw['fallback_amount']) ? (float) $raw['fallback_amount'] : null,
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
