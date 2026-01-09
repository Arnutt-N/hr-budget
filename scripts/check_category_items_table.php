<?php
/**
 * Check existing data in budget_category_items table
 * 
 * This script will:
 * 1. Check if table exists
 * 2. Show table structure
 * 3. Count total rows
 * 4. Show sample data
 * 5. Check for existing parent_id/level columns
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

echo "=================================================\n";
echo "Budget Category Items - Database Inspection\n";
echo "=================================================\n\n";

try {
    // Check if table exists
    $tableCheck = Database::query("SHOW TABLES LIKE 'budget_category_items'");
    
    if (empty($tableCheck)) {
        echo "❌ Table 'budget_category_items' does NOT exist!\n";
        exit(1);
    }
    
    echo "✅ Table 'budget_category_items' exists\n\n";
    
    // Show structure
    echo "📋 TABLE STRUCTURE\n";
    echo str_repeat("-", 80) . "\n";
    $columns = Database::query("DESCRIBE budget_category_items");
    
    $hasParentId = false;
    $hasLevel = false;
    
    printf("%-20s %-15s %-10s %-10s\n", "Column", "Type", "Null", "Key");
    echo str_repeat("-", 80) . "\n";
    
    foreach ($columns as $col) {
        printf("%-20s %-15s %-10s %-10s\n", 
            $col['Field'], 
            $col['Type'], 
            $col['Null'], 
            $col['Key']
        );
        
        if ($col['Field'] === 'parent_id') $hasParentId = true;
        if ($col['Field'] === 'level') $hasLevel = true;
    }
    
    echo "\n";
    
    // Check for hierarchy columns
    if ($hasParentId || $hasLevel) {
        echo "⚠️  HIERARCHY COLUMNS ALREADY EXIST:\n";
        if ($hasParentId) echo "   - parent_id found\n";
        if ($hasLevel) echo "   - level found\n";
        echo "   Migration may need to be modified or skipped!\n\n";
    } else {
        echo "✅ No hierarchy columns found - safe to migrate\n\n";
    }
    
    // Count rows
    $countResult = Database::query("SELECT COUNT(*) as total FROM budget_category_items");
    $totalRows = $countResult[0]['total'] ?? 0;
    
    echo "📊 DATA VOLUME\n";
    echo str_repeat("-", 80) . "\n";
    echo "Total rows: $totalRows\n\n";
    
    if ($totalRows > 0) {
        // Show sample data
        echo "📝 SAMPLE DATA (First 10 rows)\n";
        echo str_repeat("-", 80) . "\n";
        $samples = Database::query("SELECT * FROM budget_category_items LIMIT 10");
        
        foreach ($samples as $idx => $row) {
            echo "\nRow " . ($idx + 1) . ":\n";
            foreach ($row as $key => $value) {
                echo "  $key: " . (is_null($value) ? 'NULL' : $value) . "\n";
            }
        }
        
        echo "\n";
        
        // Check for category distribution
        echo "🏷️  CATEGORY DISTRIBUTION\n";
        echo str_repeat("-", 80) . "\n";
        $categoryDist = Database::query("
            SELECT 
                category_id,
                COUNT(*) as item_count
            FROM budget_category_items
            GROUP BY category_id
            ORDER BY item_count DESC
        ");
        
        printf("%-15s %s\n", "Category ID", "Item Count");
        echo str_repeat("-", 80) . "\n";
        foreach ($categoryDist as $dist) {
            printf("%-15s %s\n", $dist['category_id'] ?? 'NULL', $dist['item_count']);
        }
        
        echo "\n";
        
        // Active vs Inactive
        $activeCheck = Database::query("
            SELECT 
                is_active,
                COUNT(*) as count
            FROM budget_category_items
            GROUP BY is_active
        ");
        
        echo "📌 ACTIVE STATUS\n";
        echo str_repeat("-", 80) . "\n";
        foreach ($activeCheck as $status) {
            $label = $status['is_active'] == 1 ? 'Active' : 'Inactive';
            echo "$label: {$status['count']} rows\n";
        }
        
    } else {
        echo "ℹ️  Table is EMPTY - no existing data to migrate\n";
    }
    
    echo "\n";
    echo "=================================================\n";
    echo "Inspection Complete!\n";
    echo "=================================================\n\n";
    
    // Recommendations
    echo "💡 RECOMMENDATIONS\n";
    echo str_repeat("-", 80) . "\n";
    
    if ($totalRows === 0) {
        echo "✅ Table is empty - safe to proceed with migration and seeding\n";
    } elseif ($hasParentId || $hasLevel) {
        echo "⚠️  Hierarchy columns exist - review before proceeding:\n";
        echo "   1. Check if data is already hierarchical\n";
        echo "   2. Consider rollback if needed\n";
        echo "   3. Or skip migration and only run seeder\n";
    } else {
        echo "⚠️  Table has $totalRows existing rows:\n";
        echo "   Option A: Truncate table before seeding new data\n";
        echo "   Option B: Migrate existing data (requires mapping logic)\n";
        echo "   Option C: Keep existing + add new with hierarchy\n";
    }
    
    echo "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
