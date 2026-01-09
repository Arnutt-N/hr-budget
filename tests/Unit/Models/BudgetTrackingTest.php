<?php
/**
 * BudgetTracking Model Tests
 */

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\BudgetTracking;

class BudgetTrackingTest extends TestCase
{
    /** @test */
    public function it_can_get_tracking_data_by_fiscal_year()
    {
        // Insert test tracking data
        BudgetTracking::upsert(2568, 1, [
            'allocated' => 100000,
            'transfer' => 5000,
            'disbursed' => 30000,
            'pending' => 10000,
            'po' => 5000
        ]);

        $data = BudgetTracking::getByFiscalYear(2568);

        $this->assertNotEmpty($data);
        $this->assertIsArray($data);
    }

    /** @test */
    public function it_can_upsert_tracking_data()
    {
        $itemId = 1;
        $fiscalYear = 2568;

        // First insert
        $result = BudgetTracking::upsert($fiscalYear, $itemId, [
            'allocated' => 50000,
            'transfer' => 0,
            'disbursed' => 10000,
            'pending' => 5000,
            'po' => 2000
        ]);

        $this->assertTrue($result);

        // Update same record
        $result = BudgetTracking::upsert($fiscalYear, $itemId, [
            'allocated' => 50000,
            'transfer' => 5000,
            'disbursed' => 15000,
            'pending' => 3000,
            'po' => 2000
        ]);

        $this->assertTrue($result);

        // Verify data was updated
        $record = BudgetTracking::find($fiscalYear, $itemId);
        $this->assertEquals(5000, $record['transfer']);
        $this->assertEquals(15000, $record['disbursed']);
    }

    /** @test */
    public function it_can_get_summary_statistics()
    {
        $fiscalYear = 2568;

        // Insert multiple tracking records
        BudgetTracking::upsert($fiscalYear, 1, [
            'allocated' => 100000,
            'transfer' => 10000,
            'disbursed' => 30000,
            'pending' => 10000,
            'po' => 5000
        ]);

        BudgetTracking::upsert($fiscalYear, 2, [
            'allocated' => 200000,
            'transfer' => -5000,
            'disbursed' => 50000,
            'pending' => 20000,
            'po' => 10000
        ]);

        $summary = BudgetTracking::getSummary($fiscalYear);

        $this->assertIsArray($summary);
        $this->assertArrayHasKey('total_allocated', $summary);
        $this->assertArrayHasKey('total_disbursed', $summary);
        $this->assertArrayHasKey('total_remaining', $summary);

        // Verify calculations
        $this->assertEquals(300000, $summary['total_allocated']);
        $this->assertEquals(5000, $summary['total_transfer']);
        $this->assertEquals(80000, $summary['total_disbursed']);
    }

    /** @test */
    public function it_can_get_keyed_data()
    {
        $fiscalYear = 2568;

        BudgetTracking::upsert($fiscalYear, 1, ['allocated' => 100000]);
        BudgetTracking::upsert($fiscalYear, 2, ['allocated' => 200000]);

        $keyed = BudgetTracking::getByFiscalYearKeyed($fiscalYear);

        $this->assertIsArray($keyed);
        $this->assertArrayHasKey(1, $keyed);
        $this->assertArrayHasKey(2, $keyed);
        $this->assertEquals(100000, $keyed[1]['allocated']);
    }

    /** @test */
    public function it_can_delete_tracking_record()
    {
        $fiscalYear = 2568;
        $itemId = 99;

        BudgetTracking::upsert($fiscalYear, $itemId, ['allocated' => 50000]);

        $deleted = BudgetTracking::delete($fiscalYear, $itemId);

        $this->assertGreaterThan(0, $deleted);

        $record = BudgetTracking::find($fiscalYear, $itemId);
        $this->assertNull($record);
    }

    /** @test */
    public function it_can_bulk_upsert()
    {
        $fiscalYear = 2568;

        $items = [
            10 => ['allocated' => 10000, 'disbursed' => 1000],
            11 => ['allocated' => 20000, 'disbursed' => 2000],
            12 => ['allocated' => 30000, 'disbursed' => 3000],
        ];

        $count = BudgetTracking::bulkUpsert($fiscalYear, $items);

        $this->assertEquals(3, $count);

        $data = BudgetTracking::getByFiscalYearKeyed($fiscalYear);
        $this->assertArrayHasKey(10, $data);
        $this->assertArrayHasKey(11, $data);
        $this->assertArrayHasKey(12, $data);
    }
}
