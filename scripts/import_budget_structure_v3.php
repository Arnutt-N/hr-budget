<?php
/**
 * Budget Structure Import Script v3.0 (Production-Ready)
 * 
 * Features:
 * - External configuration
 * - File-based logging
 * - Data validation
 * - Visual progress bar
 * - Dry-run mode
 * - Batch commits for large CSV files
 * 
 * Usage: php scripts/import_budget_structure_v3.php [--dry-run] [--verbose]
 * 
 * @version 3.0
 * @date 2026-01-01
 */

// ============================================
// Initialization
// ============================================
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Parse command line arguments
$options = getopt('', ['dry-run', 'verbose', 'help']);
$isDryRun = isset($options['dry-run']);
$isVerbose = isset($options['verbose']);

if (isset($options['help'])) {
    echo "Budget Structure Import Script v3.0\n";
    echo "Usage: php scripts/import_budget_structure_v3.php [OPTIONS]\n\n";
    echo "Options:\n";
    echo "  --dry-run    Preview changes without committing to database\n";
    echo "  --verbose    Show detailed logging\n";
    echo "  --help       Show this help message\n\n";
    exit(0);
}

// Load configuration
$configFile = __DIR__ . '/../config/import.php';
if (!file_exists($configFile)) {
    die("[ERROR] Configuration file not found: $configFile\n");
}
$config = require $configFile;

// Override config with command line options
if ($isDryRun) $config['dry_run'] = true;
if ($isVerbose) $config['verbose'] = true;

// Set memory limit
ini_set('memory_limit', $config['memory_limit']);
set_time_limit(0);

// ============================================
// Logging Setup
// ============================================
class Logger {
    private $logFile;
    private $verbose;
    private $startTime;
    
    public function __construct($logDir, $verbose = false) {
        $this->verbose = $verbose;
        $this->startTime = microtime(true);
        
        // Create logs directory if not exists
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Create log file
        $timestamp = date('Y-m-d_His');
        $this->logFile = $logDir . "/import_$timestamp.log";
        
        $this->info("==============================================");
        $this->info(" Budget Structure CSV Importer v3.0");
        $this->info(" Started: " . date('Y-m-d H:i:s'));
        $this->info("==============================================\n");
    }
    
    public function info($message) {
        $this->log('INFO', $message);
    }
    
    public function warn($message) {
        $this->log('WARN', $message);
    }
    
    public function error($message) {
        $this->log('ERROR', $message);
    }
    
    public function debug($message) {
        if ($this->verbose) {
            $this->log('DEBUG', $message);
        }
    }
    
    private function log($level, $message) {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] [$level] $message\n";
        
        // Write to file
        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
        
        // Echo to console
        echo $logEntry;
    }
    
    public function progress($current, $total, $message = '') {
        $percent = round(($current / $total) * 100, 1);
        $bar = str_repeat('█', floor($percent / 2));
        $space = str_repeat('░', 50 - floor($percent / 2));
        
        $elapsed = microtime(true) - $this->startTime;
        $rate = $current / max($elapsed, 1);
        $remaining = ($total - $current) / max($rate, 1);
        
        $eta = $remaining > 60 ? sprintf('%dm %ds', floor($remaining / 60), $remaining % 60) : sprintf('%ds', $remaining);
        
        echo "\r[{$bar}{$space}] {$percent}% | {$current}/{$total} | ETA: {$eta} {$message}";
        flush();
    }
    
    public function finish($message = '') {
        $elapsed = microtime(true) - $this->startTime;
        $this->info("\n$message");
        $this->info("Total time: " . sprintf('%.2f', $elapsed) . " seconds");
        $this->info("Log file: " . $this->logFile);
    }
}

$logger = new Logger($config['log_dir'], $config['verbose']);

// ============================================
// Data Validator
// ============================================
class Validator {
    private $rules;
    private $logger;
    
    public function __construct($rules, $logger) {
        $this->rules = $rules;
        $this->logger = $logger;
    }
    
    public function validate($data, $rowNum) {
        $errors = [];
        
        // Check name length
        if (isset($data['name']) && mb_strlen($data['name']) > $this->rules['max_name_length']) {
            $errors[] = "Name exceeds maximum length ({$this->rules['max_name_length']} chars)";
        }
        
        // Check code length
        if (isset($data['code']) && mb_strlen($data['code']) > $this->rules['max_code_length']) {
            $errors[] = "Code exceeds maximum length ({$this->rules['max_code_length']} chars)";
        }
        
        // Log validation errors
        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->logger->warn("Row $rowNum validation error: $error");
            }
            
            if (!$this->rules['skip_invalid_rows']) {
                throw new Exception("Validation failed at row $rowNum: " . implode(', ', $errors));
            }
            
            return false;
        }
        
        return true;
    }
}

$validator = new Validator($config['validation'], $logger);

// ============================================
// Database Connection
// ============================================
try {
    $dsn = "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset={$config['db']['charset']}";
    $pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec("SET NAMES utf8mb4");
    $logger->info("[OK] Database connected");
} catch (PDOException $e) {
    $logger->error("Database connection failed: " . $e->getMessage());
    exit(1);
}

// ============================================
// Dry-run Mode Warning
// ============================================
if ($config['dry_run']) {
    $logger->warn("⚠️  DRY-RUN MODE - No changes will be committed to database");
}

// ============================================
// Cache for Lookup/Insert operations
// ============================================
$cache = [
    'budget_types' => [],
    'plans' => [],
    'projects' => [],
    'activities' => [],
    'expense_types' => [],
    'expense_groups' => [],
    'expense_items' => [],
    'organizations' => [],
    'provinces' => [],
    'province_groups' => [],
    'province_zones' => [],
    'inspection_zones' => [],
];

// ============================================
// Helper Functions
// ============================================
function fixEncoding($str) {
    $str = trim($str);
    if (empty($str)) return $str;
    if (preg_match('/[\x{0E00}-\x{0E7F}]/u', $str)) return $str;
    $bytes = @utf8_decode($str);
    $fixed = @iconv('TIS-620', 'UTF-8//IGNORE', $bytes);
    return $fixed ?: $str;
}

function getOrInsert($pdo, &$cache, $table, $criteria, $data, $logger) {
    $cacheKey = implode('|', array_map(function($v) { return $v ?? 'NULL'; }, array_values($criteria)));
    
    if (isset($cache[$table][$cacheKey])) {
        return $cache[$table][$cacheKey];
    }
    
    $where = [];
    $params = [];
    foreach ($criteria as $col => $val) {
        if ($val === null) {
            $where[] = "`$col` IS NULL";
        } else {
            $where[] = "`$col` = ?";
            $params[] = $val;
        }
    }
    
    $sql = "SELECT id FROM `$table` WHERE " . implode(" AND ", $where) . " LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $id = $stmt->fetchColumn();
    
    if ($id) {
        $cache[$table][$cacheKey] = $id;
        return $id;
    }
    
    $cols = array_keys($data);
    $placeholders = str_repeat('?,', count($cols) - 1) . '?';
    $sql = "INSERT INTO `$table` (`" . implode('`,`', $cols) . "`) VALUES ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_values($data));
    $id = $pdo->lastInsertId();
    
    $logger->debug("Created $table: ID=$id, Name={$data['name_th']}");
    
    $cache[$table][$cacheKey] = $id;
    return $id;
}

function preloadCache($pdo, &$cache, $logger) {
    $logger->info("[INFO] Pre-loading cache...");
    
    $tables = [
        'budget_types' => 'SELECT id, name_th FROM budget_types',
        'expense_types' => 'SELECT id, name_th FROM expense_types',
        'provinces' => 'SELECT id, name_th FROM provinces',
        'province_groups' => 'SELECT id, name_th FROM province_groups',
        'inspection_zones' => 'SELECT id, name_th FROM inspection_zones',
    ];
    
    foreach ($tables as $table => $sql) {
        $stmt = $pdo->query($sql);
        $count = 0;
        while ($r = $stmt->fetch()) {
            $cache[$table][trim($r['name_th'])] = $r['id'];
            $count++;
        }
        $logger->debug("  - $table: $count records");
    }
}

// ============================================
// Open CSV File
// ============================================
if (!file_exists($config['csv_file'])) {
    $logger->error("CSV file not found: {$config['csv_file']}");
    exit(1);
}

$handle = fopen($config['csv_file'], 'r');
if (!$handle) {
    $logger->error("Could not open CSV file");
    exit(1);
}
$logger->info("[OK] CSV file opened: " . basename($config['csv_file']));

// Handle BOM
$bom = fread($handle, 3);
if ($bom !== "\xEF\xBB\xBF") rewind($handle);

// Read header
$header = fgetcsv($handle);
$logger->info("[INFO] CSV has " . count($header) . " columns");

// Count total rows for progress bar
$totalRows = 0;
while (fgetcsv($handle) !== false) $totalRows++;
rewind($handle);
fread($handle, 3); // Skip BOM again
fgetcsv($handle); // Skip header again
$logger->info("[INFO] Total rows to process: $totalRows");

// ============================================
// Cleanup if requested
// ============================================
if ($config['full_cleanup_mode']) {
    $logger->warn("⚠️  FULL CLEANUP MODE - Deleting ALL data from all tables...");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    $fullCleanupTables = [
        'budget_line_items', 'budget_trackings', 'disbursement_details', 'disbursement_headers',
        'activities', 'projects', 'plans', 'budget_types',
        'expense_items', 'expense_groups', 'expense_types',
        'organizations', 'provinces', 'province_zones', 'province_groups', 'inspection_zones',
    ];
    
    foreach ($fullCleanupTables as $table) {
        try {
            $deleted = $pdo->exec("DELETE FROM `$table`");
            $pdo->exec("ALTER TABLE `$table` AUTO_INCREMENT = 1");
            $logger->info("  - Wiped $table ($deleted records)");
        } catch (PDOException $e) {
            $logger->debug("  - Skipped $table (not found)");
        }
    }
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    $logger->info("[OK] Full cleanup completed!");
    
    foreach ($cache as $key => $val) $cache[$key] = [];
    $logger->info("[INFO] Cache cleared (fresh start).");
    
} elseif ($config['cleanup_before_import']) {
    $logger->warn("Cleaning up FY {$config['fiscal_year']} data only...");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    $tables = ['budget_line_items', 'activities', 'projects', 'plans'];
    foreach ($tables as $table) {
        $deleted = $pdo->exec("DELETE FROM `$table` WHERE fiscal_year = {$config['fiscal_year']}");
        $logger->info("  - Deleted $deleted records from $table");
    }
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    preloadCache($pdo, $cache, $logger);
}

// ============================================
// Main Import Loop
// ============================================
$logger->info("\n[INFO] Starting import...\n");
$pdo->beginTransaction();

$stats = [
    'rows_processed' => 0,
    'rows_skipped' => 0,
    'rows_invalid' => 0,
    'line_items_created' => 0,
    'last_commit' => 0,
];

try {
    while (($row = fgetcsv($handle)) !== false) {
        $stats['rows_processed']++;
        
        // Show progress
        if ($stats['rows_processed'] % 5 == 0 || $stats['rows_processed'] == $totalRows) {
            $logger->progress($stats['rows_processed'], $totalRows, "");
        }
        
        // Skip empty rows
        if (empty(array_filter($row))) {
            $stats['rows_skipped']++;
            continue;
        }
        
        // Parse CSV Row
        $fiscalYear = trim($row[0]) ?: $config['fiscal_year'];
        $budgetTypeName = fixEncoding(trim($row[1] ?? ''));
        $planName = fixEncoding(trim($row[2] ?? ''));
        $projectName = fixEncoding(trim($row[3] ?? ''));
        $activityName = fixEncoding(trim($row[4] ?? ''));
        $expenseTypeName = fixEncoding(trim($row[5] ?? ''));
        
        // Validate
        if (!$validator->validate(['name' => $budgetTypeName], $stats['rows_processed'])) {
            $stats['rows_invalid']++;
            if (!$config['validation']['skip_invalid_rows']) break;
            continue;
        }
        
        // Item levels 0-5
        $items = [];
        for ($i = 0; $i <= 5; $i++) {
            $items[$i] = fixEncoding(trim($row[6 + $i] ?? ''));
        }
        
        // Organization
        $ministryName = fixEncoding(trim($row[12] ?? ''));
        $deptName = fixEncoding(trim($row[13] ?? ''));
        $divName = fixEncoding(trim($row[14] ?? ''));
        $sectName = fixEncoding(trim($row[15] ?? ''));
        
        // Geography
        $provinceName = fixEncoding(trim($row[16] ?? ''));
        $regionType = fixEncoding(trim($row[17] ?? ''));
        $provGroupName = fixEncoding(trim($row[18] ?? ''));
        $provZoneName = fixEncoding(trim($row[19] ?? ''));
        $inspZoneName = fixEncoding(trim($row[20] ?? ''));
        $remark = fixEncoding(trim($row[21] ?? ''));
        
        if (empty($budgetTypeName)) {
            $stats['rows_skipped']++;
            continue;
        }
        
        // Insert data (same logic as v2, but with logger parameter)
        $budgetTypeId = getOrInsert($pdo, $cache, 'budget_types',
            ['name_th' => $budgetTypeName],
            ['name_th' => $budgetTypeName, 'code' => 'BT-' . substr(md5($budgetTypeName), 0, 6)],
            $logger
        );
        
        $planId = null;
        if ($planName) {
            $planId = getOrInsert($pdo, $cache, 'plans',
                ['name_th' => $planName, 'budget_type_id' => $budgetTypeId, 'fiscal_year' => $fiscalYear],
                ['name_th' => $planName, 'budget_type_id' => $budgetTypeId, 'fiscal_year' => $fiscalYear, 'code' => 'PL-' . substr(md5($planName), 0, 6)],
                $logger
            );
        }
        
        $projectId = null;
        if ($projectName && $planId) {
            $projectId = getOrInsert($pdo, $cache, 'projects',
                ['name_th' => $projectName, 'plan_id' => $planId, 'fiscal_year' => $fiscalYear],
                ['name_th' => $projectName, 'plan_id' => $planId, 'fiscal_year' => $fiscalYear, 'code' => 'PJ-' . substr(md5($projectName), 0, 6)],
                $logger
            );
        }
        
        $activityId = null;
        if ($activityName) {
            $actCriteria = ['name_th' => $activityName, 'fiscal_year' => $fiscalYear];
            if ($projectId) $actCriteria['project_id'] = $projectId;
            elseif ($planId) $actCriteria['plan_id'] = $planId;
            
            $actData = $actCriteria;
            $actData['code'] = 'AC-' . substr(md5($activityName), 0, 6);
            
            $activityId = getOrInsert($pdo, $cache, 'activities', $actCriteria, $actData, $logger);
        }
        
        $expenseTypeId = null;
        if ($expenseTypeName) {
            $expenseTypeName = trim($expenseTypeName);
            $expenseTypeId = getOrInsert($pdo, $cache, 'expense_types',
                ['name_th' => $expenseTypeName],
                ['name_th' => $expenseTypeName, 'code' => 'ET-' . substr(md5($expenseTypeName), 0, 4)],
                $logger
            );
        }
        
        $expenseGroupId = null;
        if ($items[0] && $expenseTypeId) {
            $expenseGroupId = getOrInsert($pdo, $cache, 'expense_groups',
                ['name_th' => $items[0], 'expense_type_id' => $expenseTypeId],
                ['name_th' => $items[0], 'expense_type_id' => $expenseTypeId, 'code' => 'EG-' . substr(md5($items[0]), 0, 4)],
                $logger
            );
        }
        
        $expenseItemParentId = null;
        $finalExpenseItemId = null;
        
        for ($level = 1; $level <= 5; $level++) {
            $itemName = $items[$level];
            if (empty($itemName) || $itemName === 'รายการย่อย ...') break;
            
            $criteria = [
                'name_th' => $itemName,
                'expense_group_id' => $expenseGroupId,
                'parent_id' => $expenseItemParentId
            ];
            
            $data = $criteria;
            $data['level'] = $level;
            $data['code'] = 'EI-' . substr(md5($itemName . $level), 0, 4);
            
            $itemId = getOrInsert($pdo, $cache, 'expense_items', $criteria, $data, $logger);
            $expenseItemParentId = $itemId;
            $finalExpenseItemId = $itemId;
        }
        
        $ministryId = $deptId = $divId = $sectId = null;
        $orgParentId = null;
        
        if ($ministryName) {
            $ministryId = getOrInsert($pdo, $cache, 'organizations',
                ['name_th' => $ministryName, 'level' => 0],
                ['name_th' => $ministryName, 'level' => 0, 'org_type' => 'ministry', 'code' => 'MN-' . substr(md5($ministryName), 0, 4)],
                $logger
            );
            $orgParentId = $ministryId;
        }
        
        if ($deptName && $orgParentId) {
            $deptId = getOrInsert($pdo, $cache, 'organizations',
                ['name_th' => $deptName, 'parent_id' => $orgParentId],
                ['name_th' => $deptName, 'parent_id' => $orgParentId, 'level' => 1, 'org_type' => 'department', 'code' => 'DP-' . substr(md5($deptName), 0, 4)],
                $logger
            );
            $orgParentId = $deptId;
        }
        
        if ($divName && $orgParentId) {
            $divId = getOrInsert($pdo, $cache, 'organizations',
                ['name_th' => $divName, 'parent_id' => $orgParentId],
                ['name_th' => $divName, 'parent_id' => $orgParentId, 'level' => 2, 'org_type' => 'division', 'code' => 'DV-' . substr(md5($divName), 0, 4)],
                $logger
            );
            $orgParentId = $divId;
        }
        
        if ($sectName && $orgParentId) {
            $regionMap = [
                'ส่วนกลาง' => 'central',
                'ส่วนภูมิภาค' => 'regional',
                'จังหวัด' => 'provincial',
                'ส่วนกลางที่ตั้งอยู่ในภูมิภาค' => 'central'
            ];
            $region = $regionMap[$regionType] ?? 'central';
            
            $sectId = getOrInsert($pdo, $cache, 'organizations',
                ['name_th' => $sectName, 'parent_id' => $orgParentId],
                ['name_th' => $sectName, 'parent_id' => $orgParentId, 'level' => 3, 'org_type' => 'section', 'region' => $region, 'code' => 'SC-' . substr(md5($sectName), 0, 4)],
                $logger
            );
        }
        
        $provinceId = $provGroupId = $provZoneId = $inspZoneId = null;
        
        if ($provinceName) {
            $provinceId = getOrInsert($pdo, $cache, 'provinces',
                ['name_th' => $provinceName],
                ['name_th' => $provinceName, 'code' => 'PR-' . substr(md5($provinceName), 0, 4)],
                $logger
            );
        }
        
        if ($provGroupName) {
            $provGroupId = getOrInsert($pdo, $cache, 'province_groups',
                ['name_th' => $provGroupName],
                ['name_th' => $provGroupName, 'code' => 'PG-' . substr(md5($provGroupName), 0, 4)],
                $logger
            );
        }
        
        if ($provZoneName) {
            $provZoneId = getOrInsert($pdo, $cache, 'province_zones',
                ['name_th' => $provZoneName],
                ['name_th' => $provZoneName, 'code' => 'PZ-' . substr(md5($provZoneName), 0, 4), 'province_group_id' => $provGroupId],
                $logger
            );
        }
        
        if ($inspZoneName) {
            $inspZoneId = getOrInsert($pdo, $cache, 'inspection_zones',
                ['name_th' => $inspZoneName],
                ['name_th' => $inspZoneName, 'code' => 'IZ-' . substr(md5($inspZoneName), 0, 4)],
                $logger
            );
        }
        
        $lineRegion = ($regionType === 'ส่วนภูมิภาค' || $regionType === 'จังหวัด') ? 'regional' : 'central';
        
        $sql = "INSERT INTO budget_line_items (
            fiscal_year, budget_type_id, plan_id, project_id, activity_id,
            expense_type_id, expense_group_id, expense_item_id,
            ministry_id, department_id, division_id, section_id,
            province_id, province_group_id, province_zone_id, inspection_zone_id,
            region_type, remarks
        ) VALUES (
            ?, ?, ?, ?, ?,
            ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?
        )";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $fiscalYear, $budgetTypeId, $planId, $projectId, $activityId,
            $expenseTypeId, $expenseGroupId, $finalExpenseItemId,
            $ministryId, $deptId, $divId, $sectId,
            $provinceId, $provGroupId, $provZoneId, $inspZoneId,
            $lineRegion, $remark
        ]);
        
        $stats['line_items_created']++;
        
        // Batch commit
        if ($stats['rows_processed'] - $stats['last_commit'] >= $config['batch_size']) {
            $pdo->commit();
            $pdo->beginTransaction();
            $stats['last_commit'] = $stats['rows_processed'];
            $logger->debug("Batch committed at row {$stats['rows_processed']}");
            
            // Clear cache periodically to manage memory
            if ($stats['rows_processed'] % ($config['batch_size'] * 2) == 0) {
                foreach ($cache as $key => $val) {
                    if (count($val) > 1000) {
                        $cache[$key] = array_slice($val, -500, 500, true);
                    }
                }
                $logger->debug("Cache trimmed at row {$stats['rows_processed']}");
            }
        }
    }
    
    // Final commit or rollback
    if ($config['dry_run']) {
        $pdo->rollBack();
        $logger->warn("\n[DRY-RUN] Changes rolled back (no data was actually saved)");
    } else {
        $pdo->commit();
        $logger->info("\n[OK] Import completed successfully!");
    }
    
} catch (Exception $e) {
    $pdo->rollBack();
    $logger->error("\nImport failed: " . $e->getMessage());
    $logger->error("Trace: " . $e->getTraceAsString());
    exit(1);
}

fclose($handle);

// ============================================
// Summary
// ============================================
$logger->info("\n==============================================");
$logger->info(" Import Summary");
$logger->info("==============================================");
$logger->info(" Rows Processed: {$stats['rows_processed']}");
$logger->info(" Rows Skipped:   {$stats['rows_skipped']}");
$logger->info(" Rows Invalid:   {$stats['rows_invalid']}");
$logger->info(" Line Items:     {$stats['line_items_created']}");
$logger->info("==============================================");

if (!$config['dry_run']) {
    $logger->info("\n[INFO] Current table counts:");
    $tables = ['budget_types', 'plans', 'projects', 'activities', 'expense_types', 'expense_groups', 'expense_items', 'organizations', 'budget_line_items'];
    foreach ($tables as $table) {
        $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        $logger->info("  - $table: $count records");
    }
}

$logger->finish("[DONE] Script finished");
