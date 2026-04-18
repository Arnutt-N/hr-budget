<?php

declare(strict_types=1);

namespace App\Dtos;

final class CreateBudgetRequestDto
{
    /**
     * @param BudgetRequestItemDto[] $items
     */
    public function __construct(
        public readonly string $requestTitle,
        public readonly int $fiscalYear,
        public readonly ?int $orgId,
        public readonly array $items,
    ) {}

    /**
     * @return array<string,string>
     */
    public function validate(): array
    {
        $errors = [];

        if ($this->requestTitle === '') {
            $errors['request_title'] = 'กรุณาระบุชื่อคำขอ';
        }

        if ($this->fiscalYear < 2400 || $this->fiscalYear > 2700) {
            $errors['fiscal_year'] = 'ปีงบประมาณไม่ถูกต้อง';
        }

        if (count($this->items) === 0) {
            $errors['items'] = 'ต้องมีรายการอย่างน้อย 1 รายการ';
        }

        foreach ($this->items as $i => $item) {
            $itemErrors = $item->validate();
            foreach ($itemErrors as $field => $msg) {
                $errors["items[{$i}][{$field}]"] = $msg;
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

        $items = [];
        foreach ($raw['items'] ?? [] as $itemRaw) {
            if (is_array($itemRaw)) {
                $items[] = BudgetRequestItemDto::fromArray($itemRaw);
            }
        }

        $orgId = $raw['org_id'] ?? null;

        return new self(
            requestTitle: trim((string) ($raw['request_title'] ?? '')),
            fiscalYear: (int) ($raw['fiscal_year'] ?? 0),
            orgId: $orgId !== null ? (int) $orgId : null,
            items: $items,
        );
    }
}
