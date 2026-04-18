<?php

declare(strict_types=1);

namespace Tests\Unit\Dtos;

use PHPUnit\Framework\TestCase;
use App\Dtos\BudgetRequestItemDto;
use App\Dtos\CreateBudgetRequestDto;
use App\Dtos\UpdateBudgetRequestDto;
use App\Dtos\ApprovalActionDto;
use App\Dtos\BudgetRequestListQueryDto;

class BudgetRequestDtoTest extends TestCase
{
    // --- BudgetRequestItemDto ---

    /** @test */
    public function item_amount_is_quantity_times_unit_price(): void
    {
        $item = new BudgetRequestItemDto('Paper', '10', '25.50');
        $this->assertSame('255.00', $item->amount());
    }

    /** @test */
    public function item_validate_empty_name_fails(): void
    {
        $item = new BudgetRequestItemDto('', '1', '100');
        $errors = $item->validate();
        $this->assertArrayHasKey('item_name', $errors);
    }

    /** @test */
    public function item_validate_negative_quantity_fails(): void
    {
        $item = new BudgetRequestItemDto('Test', '-1', '100');
        $errors = $item->validate();
        $this->assertArrayHasKey('quantity', $errors);
    }

    /** @test */
    public function item_validate_valid_passes(): void
    {
        $item = new BudgetRequestItemDto('Test item', '5', '100.00');
        $this->assertEmpty($item->validate());
    }

    /** @test */
    public function item_validate_name_exceeds_255_fails(): void
    {
        $item = new BudgetRequestItemDto(str_repeat('x', 256), '1', '100');
        $errors = $item->validate();
        $this->assertArrayHasKey('item_name', $errors);
    }

    /** @test */
    public function item_validate_remark_exceeds_1000_fails(): void
    {
        $item = new BudgetRequestItemDto('Test', '1', '100', str_repeat('x', 1001));
        $errors = $item->validate();
        $this->assertArrayHasKey('remark', $errors);
    }

    /** @test */
    public function item_from_array_parses_correctly(): void
    {
        $item = BudgetRequestItemDto::fromArray([
            'item_name' => '  Pens  ',
            'quantity' => '3',
            'unit_price' => '50',
            'remark' => 'Blue ink',
        ]);
        $this->assertSame('Pens', $item->itemName);
        $this->assertSame('3', $item->quantity);
        $this->assertSame('50', $item->unitPrice);
        $this->assertSame('Blue ink', $item->remark);
    }

    // --- ApprovalActionDto ---

    /** @test */
    public function approve_with_no_note_is_valid(): void
    {
        $dto = new ApprovalActionDto(null);
        $this->assertEmpty($dto->validate('approve'));
    }

    /** @test */
    public function reject_without_note_is_invalid(): void
    {
        $dto = new ApprovalActionDto(null);
        $errors = $dto->validate('reject');
        $this->assertArrayHasKey('note', $errors);
    }

    /** @test */
    public function reject_with_note_is_valid(): void
    {
        $dto = new ApprovalActionDto('Missing documentation');
        $this->assertEmpty($dto->validate('reject'));
    }

    /** @test */
    public function approve_note_exceeds_2000_fails(): void
    {
        $dto = new ApprovalActionDto(str_repeat('x', 2001));
        $errors = $dto->validate('approve');
        $this->assertArrayHasKey('note', $errors);
    }

    // --- BudgetRequestListQueryDto ---

    /** @test */
    public function list_query_offset_calculation(): void
    {
        $dto = new BudgetRequestListQueryDto(page: 3, perPage: 20);
        $this->assertSame(40, $dto->offset());
    }

    /** @test */
    public function list_query_invalid_status_fails(): void
    {
        $dto = new BudgetRequestListQueryDto(status: 'unknown');
        $errors = $dto->validate();
        $this->assertArrayHasKey('status', $errors);
    }

    /** @test */
    public function list_query_valid_status_passes(): void
    {
        $dto = new BudgetRequestListQueryDto(status: 'pending');
        $this->assertEmpty($dto->validate());
    }

    /** @test */
    public function list_query_to_filters_excludes_nulls(): void
    {
        $dto = new BudgetRequestListQueryDto(status: 'draft');
        $filters = $dto->toFilters();
        $this->assertSame(['status' => 'draft'], $filters);
    }

    // --- CreateBudgetRequestDto ---

    /** @test */
    public function create_dto_empty_items_fails(): void
    {
        $dto = new CreateBudgetRequestDto('Test', 2569, null, []);
        $errors = $dto->validate();
        $this->assertArrayHasKey('items', $errors);
    }

    /** @test */
    public function create_dto_valid_passes(): void
    {
        $items = [new BudgetRequestItemDto('Item A', '2', '100')];
        $dto = new CreateBudgetRequestDto('Test Request', 2569, null, $items);
        $this->assertEmpty($dto->validate());
    }

    /** @test */
    public function create_dto_title_exceeds_255_fails(): void
    {
        $items = [new BudgetRequestItemDto('Item A', '2', '100')];
        $dto = new CreateBudgetRequestDto(str_repeat('x', 256), 2569, null, $items);
        $errors = $dto->validate();
        $this->assertArrayHasKey('request_title', $errors);
    }

    // --- UpdateBudgetRequestDto ---

    /** @test */
    public function update_dto_all_null_is_valid_no_op(): void
    {
        $dto = new UpdateBudgetRequestDto();
        $this->assertEmpty($dto->validate());
        $this->assertFalse($dto->hasUpdates());
    }

    /** @test */
    public function update_dto_with_title_has_updates(): void
    {
        $dto = new UpdateBudgetRequestDto(requestTitle: 'New title');
        $this->assertTrue($dto->hasUpdates());
    }
}
