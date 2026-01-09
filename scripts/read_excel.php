<?php
/**
 * Excel Reader - reads xlsx file structure with better formatting
 */
header('Content-Type: text/html; charset=utf-8');

$filePath = 'docs/budget_structure2schema.xlsx';

if (!file_exists($filePath)) {
    die("File not found: $filePath\n");
}

$zip = new ZipArchive();
if ($zip->open($filePath) !== true) {
    die("Cannot open xlsx file\n");
}

// Get sheet names
$workbookXml = $zip->getFromName('xl/workbook.xml');
preg_match_all('/<sheet name="([^"]+)"/', $workbookXml, $sheetMatches);
$sheetNames = $sheetMatches[1];

// Get shared strings
$sharedStrings = [];
$ssXml = $zip->getFromName('xl/sharedStrings.xml');
if ($ssXml) {
    // Use SimpleXML for better parsing
    $ssXml = simplexml_load_string($ssXml);
    foreach ($ssXml->si as $si) {
        // Handle both simple <t> and rich text <r><t>
        $text = '';
        if (isset($si->t)) {
            $text = (string)$si->t;
        } elseif (isset($si->r)) {
            foreach ($si->r as $r) {
                $text .= (string)$r->t;
            }
        }
        $sharedStrings[] = $text;
    }
}

echo "<html><head><style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1 { color: #333; }
h2 { color: #666; margin-top: 40px; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
table { border-collapse: collapse; margin: 10px 0; width: 100%; }
th, td { border: 1px solid #ccc; padding: 8px; text-align: left; font-size: 12px; }
th { background: #f0f0f0; }
tr:nth-child(even) { background: #f9f9f9; }
.sheet-nav { background: #333; padding: 10px; margin-bottom: 20px; }
.sheet-nav a { color: white; margin-right: 15px; text-decoration: none; }
.sheet-nav a:hover { text-decoration: underline; }
</style></head><body>";

echo "<h1>📊 Budget Structure Schema Analysis</h1>";
echo "<p><strong>File:</strong> $filePath</p>";
echo "<p><strong>Sheets found:</strong> " . count($sheetNames) . "</p>";

// Sheet navigation
echo "<div class='sheet-nav'>";
foreach ($sheetNames as $i => $name) {
    $idx = $i + 1;
    echo "<a href='#sheet$idx'>$idx. $name</a>";
}
echo "</div>";

// Function to get cell value
function getCellValue($cell, $sharedStrings) {
    $value = '';
    if (isset($cell->v)) {
        $value = (string)$cell->v;
        // Check if it's a shared string
        $attrs = $cell->attributes();
        if (isset($attrs['t']) && $attrs['t'] == 's') {
            $idx = (int)$value;
            $value = isset($sharedStrings[$idx]) ? $sharedStrings[$idx] : "[$idx]";
        }
    } elseif (isset($cell->is)) {
        // Inline string
        $value = (string)$cell->is->t;
    }
    return $value;
}

// Function to convert column letter to number
function colToNum($col) {
    $col = strtoupper($col);
    $len = strlen($col);
    $num = 0;
    for ($i = 0; $i < $len; $i++) {
        $num = $num * 26 + (ord($col[$i]) - ord('A') + 1);
    }
    return $num;
}

// Read each sheet
for ($s = 1; $s <= count($sheetNames); $s++) {
    $sheetXml = $zip->getFromName("xl/worksheets/sheet{$s}.xml");
    if (!$sheetXml) continue;
    
    $sheetName = $sheetNames[$s-1];
    echo "<h2 id='sheet$s'>Sheet $s: $sheetName</h2>";
    
    $sheet = @simplexml_load_string($sheetXml);
    if (!$sheet) {
        echo "<p>Error parsing sheet</p>";
        continue;
    }
    
    // Find max column
    $maxCol = 0;
    $rows = [];
    
    if (isset($sheet->sheetData->row)) {
        foreach ($sheet->sheetData->row as $row) {
            $rowNum = (int)$row->attributes()['r'];
            $rowData = [];
            
            if (isset($row->c)) {
                foreach ($row->c as $cell) {
                    $cellRef = (string)$cell->attributes()['r'];
                    preg_match('/([A-Z]+)(\d+)/', $cellRef, $matches);
                    $colLetter = $matches[1];
                    $colNum = colToNum($colLetter);
                    $maxCol = max($maxCol, $colNum);
                    
                    $value = getCellValue($cell, $sharedStrings);
                    $rowData[$colNum] = $value;
                }
            }
            $rows[$rowNum] = $rowData;
        }
    }
    
    if (empty($rows)) {
        echo "<p><em>Sheet is empty</em></p>";
        continue;
    }
    
    // Generate column headers
    $colHeaders = [];
    for ($c = 1; $c <= $maxCol; $c++) {
        $letter = '';
        $temp = $c;
        while ($temp > 0) {
            $temp--;
            $letter = chr(65 + ($temp % 26)) . $letter;
            $temp = intval($temp / 26);
        }
        $colHeaders[$c] = $letter;
    }
    
    // Output table
    echo "<table>";
    echo "<tr><th>Row</th>";
    for ($c = 1; $c <= $maxCol; $c++) {
        echo "<th>" . $colHeaders[$c] . "</th>";
    }
    echo "</tr>";
    
    ksort($rows);
    foreach ($rows as $rowNum => $rowData) {
        echo "<tr><td><strong>$rowNum</strong></td>";
        for ($c = 1; $c <= $maxCol; $c++) {
            $val = isset($rowData[$c]) ? htmlspecialchars($rowData[$c]) : '';
            echo "<td>$val</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}

$zip->close();
echo "</body></html>";
