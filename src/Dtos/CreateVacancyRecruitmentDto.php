<?php

declare(strict_types=1);

namespace App\Dtos;

use App\Dtos\Concerns\RequestHelpers;

final class CreateVacancyRecruitmentDto
{
    use RequestHelpers;

    public function __construct(
        public readonly int $positionId,
        public readonly int $fiscalYearId,
        public readonly string $type,
        public readonly ?string $docNo,
        public readonly ?string $docDate,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if ($this->positionId <= 0) {
            $errors['position_id'] = 'กรุณาระบุอัตรากำลัง';
        }
        if ($this->fiscalYearId <= 0) {
            $errors['fiscal_year_id'] = 'กรุณาระบุปีงบประมาณ';
        }
        if (!in_array($this->type, ['transfer_request', 'eligibility_list', 'ready_to_fill'], true)) {
            $errors['type'] = 'ประเภทหลักฐานไม่ถูกต้อง';
        }
        if ($this->docDate !== null && !self::isValidDate($this->docDate)) {
            $errors['doc_date'] = 'วันที่เอกสารไม่ถูกต้อง';
        }

        return $errors;
    }

    public static function fromRequest(): self
    {
        $raw = self::jsonBody();

        return new self(
            positionId: (int) ($raw['position_id'] ?? 0),
            fiscalYearId: (int) ($raw['fiscal_year_id'] ?? 0),
            type: (string) ($raw['type'] ?? ''),
            docNo: self::nullableString($raw['doc_no'] ?? null),
            docDate: self::nullableString($raw['doc_date'] ?? null),
        );
    }
}
