<?php
/**
 * Import Script Configuration
 * 
 * This file contains all configuration options for the budget structure
 * import script.
 */

return [
    // ============================================
    // File Paths
    // ============================================
    'csv_file' => __DIR__ . '/../docs/budget_structure2schema.csv',
    'log_dir' => __DIR__ . '/../logs',
    
    // ============================================
    // Import Settings
    // ============================================
    'fiscal_year' => 2569,
    
    // ============================================
    // Cleanup Options
    // ============================================
    // cleanup_before_import: Clean FY-specific data only
    'cleanup_before_import' => true,
    
    // full_cleanup_mode: ⚠️ WARNING - Deletes ALL master data
    'full_cleanup_mode' => false,
    
    // ============================================
    // Execution Options
    // ============================================
    // dry_run: Preview changes without committing to database
    'dry_run' => false,
    
    // verbose: Show detailed logging
    'verbose' => true,
    
    // ============================================
    // Performance Options
    // ============================================
    // batch_size: Commit every N rows (helps with large CSV files)
    'batch_size' => 1000,
    
    // memory_limit: PHP memory limit
    'memory_limit' => '512M',
    
    // ============================================
    // Database Connection
    // ============================================
    'db' => [
        'host' => 'localhost',
        'name' => 'hr_budget',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4'
    ],
    
    // ============================================
    // Validation Rules
    // ============================================
    'validation' => [
        'max_name_length' => 500,
        'max_code_length' => 50,
        'max_description_length' => 1000,
        'skip_invalid_rows' => true, // Continue on validation errors
    ],
];
