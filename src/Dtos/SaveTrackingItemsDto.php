<?php

declare(strict_types=1);

namespace App\Dtos;

/**
 * Upsert the monthly tracking amounts for a disbursement record.
 *
 * Each item carries an expense_item_id and five decimal amounts kept as
 * numeric strings (avoids float drift; mirrors BudgetRequestItemDto).
 */
final class SaveTrackingItemsDto
{
    /** @param TrackingItemDto[] $items */
    public function __construct(
        public readonly array $items,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if ($this->items === []) {
            $errors['items'] = 'กรุณาระบุรายการอย่างน้อย 1 รายการ';
            return $errors;
        }

        foreach ($this->items as $index => $item) {
            foreach ($item->validate() as $field => $message) {
                $errors["items.{$index}.{$field}"] = $message;
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

        $rawItems = $raw['items'] ?? [];
        $items = [];
        if (is_array($rawItems)) {
            foreach ($rawItems as $rawItem) {
                if (is_array($rawItem)) {
                    $items[] = TrackingItemDto::fromArray($rawItem);
                }
            }
        }

        return new self(items: $items);
    }
}
