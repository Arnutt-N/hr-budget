<?php

declare(strict_types=1);

namespace App\Dtos;

final class CreateFiscalYearDto
{
    public function __construct(
        public readonly int $year,
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly bool $isCurrent = false,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if ($this->year < 2400 || $this->year > 2700) {
            $errors['year'] = 'ปีงบประมาณต้องอยู่ระหว่าง 2400-2700';
        }

        if ($this->startDate === '') {
            $errors['start_date'] = 'กรุณาระบุวันเริ่มต้น';
        } elseif (!$this->isValidDate($this->startDate)) {
            $errors['start_date'] = 'รูปแบบวันที่ไม่ถูกต้อง';
        }

        if ($this->endDate === '') {
            $errors['end_date'] = 'กรุณาระบุวันสิ้นสุด';
        } elseif (!$this->isValidDate($this->endDate)) {
            $errors['end_date'] = 'รูปแบบวันที่ไม่ถูกต้อง';
        }

        if (empty($errors['start_date']) && empty($errors['end_date']) && $this->startDate >= $this->endDate) {
            $errors['end_date'] = 'วันสิ้นสุดต้องมาหลังวันเริ่มต้น';
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
            year: (int) ($raw['year'] ?? 0),
            startDate: trim((string) ($raw['start_date'] ?? '')),
            endDate: trim((string) ($raw['end_date'] ?? '')),
            isCurrent: (bool) ($raw['is_current'] ?? false),
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
