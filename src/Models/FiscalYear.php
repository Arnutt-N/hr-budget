<?php
/**
 * FiscalYear Model
 * 
 * Handles fiscal year operations
 */

namespace App\Models;

use App\Core\Database;

class FiscalYear
{
    /**
     * Get all fiscal years
     */
    public static function all(): array
    {
        return Database::query("SELECT * FROM fiscal_years ORDER BY year DESC");
    }

    /**
     * Get current fiscal year
     */
    public static function current(): ?array
    {
        $result = Database::query("SELECT * FROM fiscal_years WHERE is_current = 1 LIMIT 1");
        return $result[0] ?? null;
    }

    /**
     * Get current fiscal year number
     */
    public static function currentYear(): int
    {
        $current = self::current();
        return $current ? (int) $current['year'] : 2568;
    }

    /**
     * Find fiscal year by year number
     */
    public static function find(int $year): ?array
    {
        $result = Database::query("SELECT * FROM fiscal_years WHERE year = ?", [$year]);
        return $result[0] ?? null;
    }

    /**
     * Set current fiscal year
     */
    public static function setCurrent(int $year): bool
    {
        // Reset all
        Database::update('fiscal_years', ['is_current' => false], '1 = 1', []);
        
        // Set new current
        return Database::update('fiscal_years', ['is_current' => true], 'year = ?', [$year]) > 0;
    }

    /**
     * Get for dropdown/select
     */
    public static function getForSelect(): array
    {
        $years = self::all();
        $options = [];
        
        foreach ($years as $y) {
            $label = 'พ.ศ. ' . $y['year'];
            if ($y['is_current']) {
                $label .= ' (ปัจจุบัน)';
            }
            if ($y['is_closed']) {
                $label .= ' [ปิด]';
            }
            
            $options[] = [
                'value' => $y['year'],
                'label' => $label,
                'is_current' => (bool) $y['is_current'],
                'is_closed' => (bool) $y['is_closed'],
            ];
        }
        
        return $options;
    }

    /**
     * Get for dropdown/select - Simple format (year number only)
     */
    public static function getForSelectSimple(): array
    {
        $years = self::all();
        $options = [];
        
        foreach ($years as $y) {
            $options[] = [
                'value' => $y['year'],
                'label' => (string) $y['year'],  // Only the year number
                'is_current' => (bool) $y['is_current'],
                'is_closed' => (bool) $y['is_closed'],
            ];
        }
        
        return $options;
    }

    /**
     * Find fiscal year by ID
     */
    public static function findById(int $id): ?array
    {
        $result = Database::query("SELECT * FROM fiscal_years WHERE id = ?", [$id]);
        return $result[0] ?? null;
    }

    /**
     * Create new fiscal year
     */
    public static function create(array $data): int
    {
        return Database::insert('fiscal_years', $data);
    }

    /**
     * Update fiscal year
     */
    public static function update(int $id, array $data): bool
    {
        return Database::update('fiscal_years', $data, 'id = ?', [$id]) > 0;
    }

    /**
     * Delete fiscal year
     */
    public static function delete(int $id): bool
    {
        return Database::delete('fiscal_years', 'id = ?', [$id]) > 0;
    }

    /**
     * Toggle closed status
     */
    public static function toggleClosed(int $id): bool
    {
        $year = self::findById($id);
        if (!$year) return false;
        
        $newStatus = !$year['is_closed'];
        return self::update($id, ['is_closed' => $newStatus]);
    }

    /**
     * Check if year exists
     */
    public static function yearExists(int $year, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM fiscal_years WHERE year = ?";
        $params = [$year];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = Database::query($sql, $params);
        return isset($result[0]) && (int)$result[0]['count'] > 0;
    }
}
