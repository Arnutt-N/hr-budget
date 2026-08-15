<?php

declare(strict_types=1);

namespace App\Dtos;

final class UpdateAllowanceTypeDto
{
    public const SCOPES = ['position', 'personal'];
    public const BASES = ['flat', 'percent_of_salary', 'by_level', 'derived'];
    public const RATE_KINDS = ['exact', 'ceiling'];
    public const BUDGET_BASES = ['establishment', 'actuals', 'manual'];
    public const REPORT_SCOPE_VALUES = ['personnel', 'operating'];

    public function __construct(
        public readonly ?string $nameTh,
        public readonly ?string $shortName,
        public readonly ?int $expenseItemId,
        public readonly ?string $scope,
        public readonly ?bool $vacantEligible,
        public readonly ?array $reportScope,
        public readonly ?string $basis,
        public readonly ?string $rateKind,
        public readonly ?string $budgetBasis,
        public readonly ?string $legalRef,
        public readonly ?bool $isActive,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if ($this->scope !== null && !in_array($this->scope, self::SCOPES, true)) {
            $errors['scope'] = 'ขอบเขตสิทธิ์ไม่ถูกต้อง (position/personal)';
        }
        if ($this->basis !== null && !in_array($this->basis, self::BASES, true)) {
            $errors['basis'] = 'รูปแบบการคำนวณไม่ถูกต้อง';
        }
        if ($this->rateKind !== null && !in_array($this->rateKind, self::RATE_KINDS, true)) {
            $errors['rate_kind'] = 'rate_kind ไม่ถูกต้อง (exact/ceiling)';
        }
        if ($this->budgetBasis !== null && !in_array($this->budgetBasis, self::BUDGET_BASES, true)) {
            $errors['budget_basis'] = 'budget_basis ไม่ถูกต้อง';
        }
        if ($this->reportScope !== null) {
            foreach ($this->reportScope as $value) {
                if (!in_array($value, self::REPORT_SCOPE_VALUES, true)) {
                    $errors['report_scope'] = 'report_scope รับได้เฉพาะ personnel/operating';
                    break;
                }
            }
        }
        if ($this->basis === 'derived' && $this->rateKind === 'ceiling') {
            $errors['basis'] = 'derived ไม่สามารถเป็นเพดาน (ceiling) ได้';
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

        $reportScope = null;
        if (array_key_exists('report_scope', $raw)) {
            $value = $raw['report_scope'];
            if (is_array($value)) {
                $reportScope = array_values(array_filter($value, 'is_string'));
            } elseif (is_string($value)) {
                $reportScope = array_values(array_filter(array_map('trim', explode(',', $value))));
            }
        }

        return new self(
            nameTh: array_key_exists('name_th', $raw) ? trim((string) $raw['name_th']) : null,
            shortName: array_key_exists('short_name', $raw) ? trim((string) $raw['short_name']) : null,
            expenseItemId: array_key_exists('expense_item_id', $raw)
                ? ($raw['expense_item_id'] === null ? null : (int) $raw['expense_item_id'])
                : null,
            scope: array_key_exists('scope', $raw) ? (string) $raw['scope'] : null,
            vacantEligible: array_key_exists('vacant_eligible', $raw) ? (bool) $raw['vacant_eligible'] : null,
            reportScope: $reportScope,
            basis: array_key_exists('basis', $raw) ? (string) $raw['basis'] : null,
            rateKind: array_key_exists('rate_kind', $raw) ? (string) $raw['rate_kind'] : null,
            budgetBasis: array_key_exists('budget_basis', $raw) ? (string) $raw['budget_basis'] : null,
            legalRef: array_key_exists('legal_ref', $raw) ? trim((string) $raw['legal_ref']) : null,
            isActive: array_key_exists('is_active', $raw) ? (bool) $raw['is_active'] : null,
        );
    }
}
