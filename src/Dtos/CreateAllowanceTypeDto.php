<?php

declare(strict_types=1);

namespace App\Dtos;

use App\Dtos\Concerns\RequestHelpers;

final class CreateAllowanceTypeDto
{
    use RequestHelpers;

    public function __construct(
        public readonly string $code,
        public readonly string $nameTh,
        public readonly ?string $shortName,
        public readonly ?int $expenseItemId,
        public readonly string $scope,
        public readonly bool $vacantEligible,
        public readonly array $reportScope,
        public readonly string $basis,
        public readonly string $rateKind,
        public readonly string $budgetBasis,
        public readonly ?string $legalRef,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if ($this->code === '') {
            $errors['code'] = 'กรุณาระบุรหัส';
        }
        if ($this->nameTh === '') {
            $errors['name_th'] = 'กรุณาระบุชื่อเต็ม';
        }
        if (!in_array($this->scope, ['position', 'personal'], true)) {
            $errors['scope'] = 'ขอบเขตสิทธิ์ไม่ถูกต้อง';
        }
        if (!in_array($this->basis, ['flat', 'percent_of_salary', 'by_level', 'derived'], true)) {
            $errors['basis'] = 'รูปแบบการคำนวณไม่ถูกต้อง';
        }
        if (!in_array($this->rateKind, ['exact', 'ceiling'], true)) {
            $errors['rate_kind'] = 'rate_kind ไม่ถูกต้อง';
        }
        if (!in_array($this->budgetBasis, ['establishment', 'actuals', 'manual'], true)) {
            $errors['budget_basis'] = 'budget_basis ไม่ถูกต้อง';
        }
        foreach ($this->reportScope as $value) {
            if (!in_array($value, ['personnel', 'operating'], true)) {
                $errors['report_scope'] = 'report_scope รับได้เฉพาะ personnel/operating';
                break;
            }
        }
        if ($this->basis === 'derived' && $this->rateKind === 'ceiling') {
            $errors['basis'] = 'derived ไม่สามารถเป็นเพดาน (ceiling) ได้';
        }

        return $errors;
    }

    public static function fromRequest(): self
    {
        $raw = self::jsonBody();

        $reportScope = [];
        if (isset($raw['report_scope'])) {
            $value = $raw['report_scope'];
            if (is_array($value)) {
                $reportScope = array_values(array_filter($value, 'is_string'));
            } elseif (is_string($value)) {
                $reportScope = array_values(array_filter(array_map('trim', explode(',', $value))));
            }
        }

        return new self(
            code: trim((string) ($raw['code'] ?? '')),
            nameTh: trim((string) ($raw['name_th'] ?? '')),
            shortName: self::nullableString($raw['short_name'] ?? null),
            expenseItemId: isset($raw['expense_item_id']) ? (int) $raw['expense_item_id'] : null,
            scope: (string) ($raw['scope'] ?? 'position'),
            vacantEligible: (bool) ($raw['vacant_eligible'] ?? false),
            reportScope: $reportScope,
            basis: (string) ($raw['basis'] ?? 'flat'),
            rateKind: (string) ($raw['rate_kind'] ?? 'exact'),
            budgetBasis: (string) ($raw['budget_basis'] ?? 'establishment'),
            legalRef: self::nullableString($raw['legal_ref'] ?? null),
        );
    }
}
