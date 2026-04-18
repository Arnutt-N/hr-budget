<?php

declare(strict_types=1);

namespace App\Dtos;

final class UpdateFiscalYearDto
{
    public function __construct(
        public readonly ?int $year = null,
        public readonly ?string $startDate = null,
        public readonly ?string $endDate = null,
        public readonly ?bool $isCurrent = null,
        public readonly ?bool $isClosed = null,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if ($this->year !== null && ($this->year < 2400 || $this->year > 2700)) {
            $errors['year'] = 'ปีงบประมาณต้องอยู่ระหว่าง 2400-2700';
        }

        if ($this->startDate !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->startDate)) {
            $errors['start_date'] = 'รูปแบบวันที่ไม่ถูกต้อง';
        }

        if ($this->endDate !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->endDate)) {
            $errors['end_date'] = 'รูปแบบวันที่ไม่ถูกต้อง';
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
            year: array_key_exists('year', $raw) ? (int) $raw['year'] : null,
            startDate: array_key_exists('start_date', $raw) ? trim((string) $raw['start_date']) : null,
            endDate: array_key_exists('end_date', $raw) ? trim((string) $raw['end_date']) : null,
            isCurrent: array_key_exists('is_current', $raw) ? (bool) $raw['is_current'] : null,
            isClosed: array_key_exists('is_closed', $raw) ? (bool) $raw['is_closed'] : null,
        );
    }
}
