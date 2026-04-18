<?php

declare(strict_types=1);

namespace App\Dtos;

final class BudgetRequestItemDto
{
    public function __construct(
        public readonly string $itemName,
        public readonly string $quantity,
        public readonly string $unitPrice,
        public readonly ?string $remark = null,
        public readonly ?int $categoryItemId = null,
    ) {}

    public function amount(): string
    {
        return bcmul($this->quantity, $this->unitPrice, 2);
    }

    /**
     * @return array<string,string>  field → Thai error message (empty = valid)
     */
    public function validate(): array
    {
        $errors = [];

        if ($this->itemName === '') {
            $errors['item_name'] = 'กรุณาระบุชื่อรายการ';
        } elseif (mb_strlen($this->itemName) > 255) {
            $errors['item_name'] = 'ชื่อรายการต้องไม่เกิน 255 ตัวอักษร';
        }

        if (!is_numeric($this->quantity) || bccomp($this->quantity, '0', 2) < 0) {
            $errors['quantity'] = 'จำนวนต้องเป็นตัวเลขที่ไม่ติดลบ';
        }

        if (!is_numeric($this->unitPrice) || bccomp($this->unitPrice, '0', 2) < 0) {
            $errors['unit_price'] = 'ราคาหน่วยต้องเป็นตัวเลขที่ไม่ติดลบ';
        }

        if ($this->remark !== null && mb_strlen($this->remark) > 1000) {
            $errors['remark'] = 'หมายเหตุต้องไม่เกิน 1,000 ตัวอักษร';
        }

        return $errors;
    }

    /**
     * Build from a raw associative array (one element of the JSON items array).
     */
    public static function fromArray(array $raw): self
    {
        return new self(
            itemName: trim((string) ($raw['item_name'] ?? '')),
            quantity: (string) ($raw['quantity'] ?? '0'),
            unitPrice: (string) ($raw['unit_price'] ?? '0'),
            remark: isset($raw['remark']) ? trim((string) $raw['remark']) : null,
            categoryItemId: isset($raw['category_item_id']) ? (int) $raw['category_item_id'] : null,
        );
    }

    public function toInsertArray(): array
    {
        return array_filter([
            'item_name' => $this->itemName,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'amount' => $this->amount(),
            'remark' => $this->remark,
            'category_item_id' => $this->categoryItemId,
        ], fn($v) => $v !== null);
    }
}
