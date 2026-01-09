<?php
/**
 * Export Budget Execution Data to Excel
 */

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap application to use Auth and Models
$app = require_once __DIR__ . '/../src/bootstrap.php';

use App\Core\Auth;
use App\Models\BudgetExecution;

// Ensure user is logged in
session_start();
if (!Auth::check()) {
    header('Location: login.php');
    exit;
}

$fiscalYear = (int)($_GET['year'] ?? (date('Y') + 543));
$filters = [
    'org_id' => $_GET['org_id'] ?? null,
    'plan_name' => $_GET['plan_name'] ?? null,
    'search' => $_GET['search'] ?? null
];

// Fetch Data
$data = BudgetExecution::getWithStructure($fiscalYear, $filters);

// Create Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set Document Properties
$spreadsheet->getProperties()
    ->setCreator('HR Budget System')
    ->setTitle('รายงานผลการเบิกจ่ายงบประมาณ ปี ' . $fiscalYear);

// Styling
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4B5563']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
];

$dataStyle = [
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
];

// Header Row
$headers = [
    'A' => 'หน่วยงาน',
    'B' => 'แผนงาน',
    'C' => 'ผลผลิต/โครงการ',
    'D' => 'กิจกรรมหลัก',
    'E' => 'หมวดรายจ่าย',
    'F' => 'รายการ',
    'G' => 'งบจัดสรร',
    'H' => 'เบิกจ่าย',
    'I' => 'PO คงค้าง',
    'J' => 'รวมใช้ไป',
    'K' => 'คงเหลือ',
    'L' => '% ใช้ไป'
];

foreach ($headers as $col => $text) {
    $sheet->setCellValue($col . '1', $text);
    $sheet->getColumnDimension($col)->setAutoSize(true);
}
$sheet->getStyle('A1:L1')->applyFromArray($headerStyle);

// Data Rows
$row = 2;
foreach ($data as $item) {
    $allocated = (float) $item['budget_allocated_amount'];
    $disbursed = (float) $item['disbursed_amount'];
    $po = (float) $item['po_pending_amount'];
    $spending = (float) $item['total_spending_amount'];
    $balance = (float) $item['balance_amount'];
    $percent = (float) $item['percent_disburse_incl_po'];

    $sheet->setCellValue('A' . $row, $item['org_name']);
    $sheet->setCellValue('B' . $row, $item['plan_name']);
    $sheet->setCellValue('C' . $row, $item['output_name']);
    $sheet->setCellValue('D' . $row, $item['activity_name']);
    $sheet->setCellValue('E' . $row, $item['category_name'] ?? '-'); // Assuming category_name might be needed or is item_name
    $sheet->setCellValue('F' . $row, $item['item_name']);
    $sheet->setCellValue('G' . $row, $allocated);
    $sheet->setCellValue('H' . $row, $disbursed);
    $sheet->setCellValue('I' . $row, $po);
    $sheet->setCellValue('J' . $row, $spending);
    $sheet->setCellValue('K' . $row, $balance);
    $sheet->setCellValue('L' . $row, $percent / 100); // For percentage formatting

    // Number Formatting
    $sheet->getStyle('G' . $row . ':K' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle('L' . $row)->getNumberFormat()->setFormatCode('0.0%');
    
    $sheet->getStyle('A' . $row . ':L' . $row)->applyFromArray($dataStyle);
    
    $row++;
}

// Final Styling
$sheet->setTitle('Budget Execution');

// Output
$filename = 'budget_execution_' . $fiscalYear . '_' . date('Ymd_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
