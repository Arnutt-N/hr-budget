<?php

declare(strict_types=1);

namespace App\Dtos;

final class UpdateBudgetRequestDto
{
    /**
     * @param BudgetRequestItemDto[]|null $items  null = keep existing; array = replace all
     */
    public function __construct(
        public readonly ?string $requestTitle = null,
        public readonly ?int $fiscalYear = null,
        public readonly ?int $orgId = null,
        public readonly ?array $items = null,
    ) {}

    /**
     * @return array<string,string>
     */
    public function validate(): array
    {
        $errors = [];

        if ($this->fiscalYear !== null && ($this->fiscalYear < 2400 || $this->fiscalYear > 2700)) {
            $errors['fiscal_year'] = 'ปีงบประมาณไม่ถูกต้อง';
        }

        if ($this->items !== null && count($this->items) === 0) {
            $errors['items'] = 'ต้องมีรายการอย่างน้อย 1 รายการ';
        }

        if ($this->items !== null) {
            foreach ($this->items as $i => $item) {
                $itemErrors = $item->validate();
                foreach ($itemErrors as $field => $msg) {
                    $errors["items[{$i}][{$field}]"] = $msg;
                }
            }
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

        $items = null;
        if (array_key_exists('items', $raw)) {
            $items = [];
            foreach ($raw['items'] as $itemRaw) {
                if (is_array($itemRaw)) {
                    $items[] = BudgetRequestItemDto::fromArray($itemRaw);
                }
            }
        }

        $orgId = array_key_exists('org_id', $raw) ? $raw['org_id'] : null;

        return new self(
            requestTitle: array_key_exists('request_title', $raw) ? trim((string) $raw['request_title']) : null,
            fiscalYear: array_key_exists('fiscal_year', $raw) ? (int) $raw['fiscal_year'] : null,
            orgId: $orgId !== null ? (int) $orgId : null,
            items: $items,
        );
    }

    public function hasUpdates(): bool
    {
        return $this->requestTitle !== null
            || $this->fiscalYear !== null
            || $this->orgId !== null
            || $this->items !== null;
    }
}
