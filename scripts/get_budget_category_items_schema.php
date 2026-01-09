<?php
/**
 * Fetch and display the schema of `budget_category_items` table.
 * Uses PDO to connect to the local MySQL database (Laragon default).
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
} catch (\Exception $e) {
    echo "Connection failed: " . $e->getMessage();
    exit(1);
}

// Check if table exists
$stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ? AND table_name = ?");
$stmt->execute([$db, 'budget_category_items']);
if ($stmt->fetchColumn() == 0) {
    echo "Table `budget_category_items` does not exist in database `$db`.\n";
    exit(0);
}

// Retrieve column information
$sql = "SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_KEY, EXTRA
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
        ORDER BY ORDINAL_POSITION";
$stmt = $pdo->prepare($sql);
$stmt->execute([$db, 'budget_category_items']);
$columns = $stmt->fetchAll();

if (empty($columns)) {
    echo "No columns found for `budget_category_items`.\n";
    exit(0);
}

// Output as Markdown table
echo "# Schema of `budget_category_items`\n\n";
echo "| Column | Type | Nullable | Default | Key | Extra |\n";
echo "|--------|------|----------|---------|-----|-------|\n";
foreach ($columns as $col) {
    $default = $col['COLUMN_DEFAULT'] === null ? 'NULL' : $col['COLUMN_DEFAULT'];
    echo "| {$col['COLUMN_NAME']} | {$col['COLUMN_TYPE']} | {$col['IS_NULLABLE']} | {$default} | {$col['COLUMN_KEY']} | {$col['EXTRA']} |\n";
}
?>
