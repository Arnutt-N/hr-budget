<?php
/**
 * Seeder script to populate `budget_category_items` with hierarchical data
 * from `research/budget_structure_reference.csv`.
 *
 * The CSV has columns: รายการ 0, รายการ 1, รายการ 2, รายการ 3, รายการ 4, รายการ 5
 * plus other metadata columns. We will walk through each row and insert
 * items level‑by‑level, linking them via `parent_id`.
 */
$host = 'localhost';
$db   = 'hr_budget';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage();
    exit(1);
}

$csvPath = __DIR__ . '/../research/budget_structure_reference.csv';
if (!file_exists($csvPath)) {
    echo "CSV file not found at $csvPath\n";
    exit(1);
}

$handle = fopen($csvPath, 'r');
if ($handle === false) {
    echo "Failed to open CSV file.\n";
    exit(1);
}

// Read header
$header = fgetcsv($handle);
// Identify level columns (รายการ 0‑5)
$levelCols = [];
foreach ($header as $idx => $col) {
    if (preg_match('/^รายการ\s*[0-5]$/', $col)) {
        $levelCols[$col] = $idx;
    }
}

$insertStmt = $pdo->prepare(
    "INSERT INTO budget_category_items (name, code, parent_id, level) VALUES (:name, :code, :parent_id, :level)"
);

$cache = [];
while (($row = fgetcsv($handle)) !== false) {
    $parentId = null;
    foreach ($levelCols as $colName => $colIdx) {
        // Extract level number from column name (e.g., "รายการ 3" -> 3)
        preg_match('/(\d+)/', $colName, $matches);
        $level = isset($matches[1]) ? (int)$matches[1] : 0;
        $name = trim($row[$colIdx]);
        if ($name === '' || $name === 'รายการย่อย ...') {
            // Skip placeholder or empty values
            continue;
        }
        // Build a deterministic code like 1.2.3 etc.
        $codeParts = [];
        if ($parentId !== null) {
            // fetch parent code from cache
            $codeParts[] = $cache[$parentId]['code'];
        }
        $codeParts[] = $level . '_' . preg_replace('/\s+/', '_', $name);
        $code = implode('.', $codeParts);

        // Check if this exact name+level already exists under same parent to avoid duplicates
        $key = $parentId . '|' . $name . '|' . $level;
        if (isset($cache[$key])) {
            $parentId = $cache[$key]['id'];
            continue;
        }

        $insertStmt->execute([
            ':name'      => $name,
            ':code'      => $code,
            ':parent_id' => $parentId,
            ':level'     => $level,
        ]);
        $newId = $pdo->lastInsertId();
        // Cache for later child insertions
        $cache[$key] = ['id' => $newId, 'code' => $code];
        $parentId = $newId; // next level becomes child of this
    }
}

fclose($handle);

echo "Seeding completed.\n";
?>
