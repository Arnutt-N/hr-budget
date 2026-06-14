<?php

declare(strict_types=1);

namespace Tests\Unit\Dtos;

use PHPUnit\Framework\TestCase;
use App\Dtos\CreateDisbursementRecordDto;
use App\Dtos\CreateDisbursementSessionDto;
use App\Dtos\DisbursementSessionListQueryDto;
use App\Dtos\SaveTrackingItemsDto;
use App\Dtos\TrackingItemDto;

class DisbursementDtoTest extends TestCase
{
    // ---- CreateDisbursementSessionDto ----

    /** @test */
    public function session_valid_passes(): void
    {
        $dto = new CreateDisbursementSessionDto(3, 2569, 10, '2026-01-01');
        $this->assertEmpty($dto->validate());
    }

    /** @test */
    public function session_zero_org_fails(): void
    {
        $dto = new CreateDisbursementSessionDto(0, 2569, 10, '2026-01-01');
        $this->assertArrayHasKey('organization_id', $dto->validate());
    }

    /** @test */
    public function session_year_out_of_range_fails(): void
    {
        $dto = new CreateDisbursementSessionDto(3, 1999, 10, '2026-01-01');
        $this->assertArrayHasKey('fiscal_year', $dto->validate());
    }

    /** @test */
    public function session_month_out_of_range_fails(): void
    {
        $dto = new CreateDisbursementSessionDto(3, 2569, 13, '2026-01-01');
        $this->assertArrayHasKey('record_month', $dto->validate());
    }

    /** @test */
    public function session_month_zero_fails(): void
    {
        $dto = new CreateDisbursementSessionDto(3, 2569, 0, '2026-01-01');
        $this->assertArrayHasKey('record_month', $dto->validate());
    }

    /** @test */
    public function session_invalid_date_fails(): void
    {
        $dto = new CreateDisbursementSessionDto(3, 2569, 10, '2026-13-40');
        $this->assertArrayHasKey('record_date', $dto->validate());
    }

    // ---- CreateDisbursementRecordDto ----

    /** @test */
    public function record_valid_passes(): void
    {
        $dto = new CreateDisbursementRecordDto(14, 31);
        $this->assertEmpty($dto->validate());
    }

    /** @test */
    public function record_zero_session_fails(): void
    {
        $dto = new CreateDisbursementRecordDto(0, 31);
        $this->assertArrayHasKey('session_id', $dto->validate());
    }

    /** @test */
    public function record_zero_activity_fails(): void
    {
        $dto = new CreateDisbursementRecordDto(14, 0);
        $this->assertArrayHasKey('activity_id', $dto->validate());
    }

    // ---- DisbursementSessionListQueryDto ----

    /** @test */
    public function list_query_defaults_pass(): void
    {
        $dto = new DisbursementSessionListQueryDto();
        $this->assertEmpty($dto->validate());
        $this->assertSame(0, $dto->offset());
        $this->assertSame([], $dto->toFilters());
    }

    /** @test */
    public function list_query_offset_computed(): void
    {
        $dto = new DisbursementSessionListQueryDto(page: 3, perPage: 20);
        $this->assertSame(40, $dto->offset());
    }

    /** @test */
    public function list_query_filters_built(): void
    {
        $dto = new DisbursementSessionListQueryDto(fiscalYear: 2569, organizationId: 3, recordMonth: 10);
        $filters = $dto->toFilters();
        $this->assertSame(2569, $filters['fiscal_year']);
        $this->assertSame(3, $filters['organization_id']);
        $this->assertSame(10, $filters['record_month']);
    }

    /** @test */
    public function list_query_bad_month_fails(): void
    {
        $dto = new DisbursementSessionListQueryDto(recordMonth: 99);
        $this->assertArrayHasKey('record_month', $dto->validate());
    }

    /** @test */
    public function list_query_bad_page_fails(): void
    {
        $dto = new DisbursementSessionListQueryDto(page: 0);
        $this->assertArrayHasKey('page', $dto->validate());
    }

    /** @test */
    public function list_query_fiscal_year_out_of_range_fails(): void
    {
        $dto = new DisbursementSessionListQueryDto(fiscalYear: 1999);
        $this->assertArrayHasKey('fiscal_year', $dto->validate());

        $dtoHigh = new DisbursementSessionListQueryDto(fiscalYear: 9999);
        $this->assertArrayHasKey('fiscal_year', $dtoHigh->validate());
    }

    /** @test */
    public function list_query_fiscal_year_in_range_passes(): void
    {
        $dto = new DisbursementSessionListQueryDto(fiscalYear: 2569);
        $this->assertEmpty($dto->validate());
    }

    /** @test */
    public function list_query_null_fiscal_year_skips_range_check(): void
    {
        // Omitted fiscal_year must not trigger the range error.
        $dto = new DisbursementSessionListQueryDto();
        $this->assertArrayNotHasKey('fiscal_year', $dto->validate());
    }

    // ---- TrackingItemDto / SaveTrackingItemsDto ----

    /** @test */
    public function tracking_item_valid_passes(): void
    {
        $dto = new TrackingItemDto(15, '100', '0', '80', '0', '0');
        $this->assertEmpty($dto->validate());
    }

    /** @test */
    public function tracking_item_negative_amount_fails(): void
    {
        $dto = new TrackingItemDto(15, '100', '0', '-5', '0', '0');
        $errors = $dto->validate();
        $this->assertArrayHasKey('disbursed', $errors);
    }

    /** @test */
    public function tracking_item_non_numeric_fails(): void
    {
        $dto = new TrackingItemDto(15, 'abc', '0', '0', '0', '0');
        $this->assertArrayHasKey('allocated', $dto->validate());
    }

    /** @test */
    public function tracking_item_zero_item_id_fails(): void
    {
        $dto = new TrackingItemDto(0, '0', '0', '0', '0', '0');
        $this->assertArrayHasKey('expense_item_id', $dto->validate());
    }

    /** @test */
    public function tracking_item_from_array_coerces_empty_to_zero(): void
    {
        $dto = TrackingItemDto::fromArray(['expense_item_id' => 15]);
        $this->assertEmpty($dto->validate());
        $amounts = $dto->amounts();
        $this->assertSame('0.00', $amounts['allocated']);
        $this->assertSame('0.00', $amounts['po']);
    }

    /** @test */
    public function tracking_item_amounts_normalized_to_scale_2(): void
    {
        $dto = TrackingItemDto::fromArray([
            'expense_item_id' => 15,
            'allocated' => '100',
            'disbursed' => '80.5',
        ]);
        $amounts = $dto->amounts();
        $this->assertSame('100.00', $amounts['allocated']);
        $this->assertSame('80.50', $amounts['disbursed']);
    }

    /** @test */
    public function save_items_empty_fails(): void
    {
        $dto = new SaveTrackingItemsDto([]);
        $this->assertArrayHasKey('items', $dto->validate());
    }

    /** @test */
    public function save_items_propagates_nested_errors(): void
    {
        $dto = new SaveTrackingItemsDto([
            new TrackingItemDto(15, '100', '0', '0', '0', '0'),
            new TrackingItemDto(16, '0', '0', '-1', '0', '0'),
        ]);
        $errors = $dto->validate();
        $this->assertArrayHasKey('items.1.disbursed', $errors);
        $this->assertArrayNotHasKey('items.0.disbursed', $errors);
    }

    /** @test */
    public function save_items_valid_passes(): void
    {
        $dto = new SaveTrackingItemsDto([
            new TrackingItemDto(15, '100', '0', '80', '0', '0'),
        ]);
        $this->assertEmpty($dto->validate());
    }
}
