---
name: reporting_export
description: Guide for generating reports and exporting data in PDF/Excel formats.
---

# Reporting & Export Guide

Standards for generating reports and exporting data.

## 📑 Table of Contents

- [Excel Export](#-excel-export)
- [PDF Export](#-pdf-export)
- [Report Templates](#-report-templates)

## 📊 Excel Export

### Using PhpSpreadsheet

```php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelExporter
{
    public function exportBudgets(array $budgets, string $filename): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('งบประมาณ');
        
        // Headers
        $headers = ['รหัส', 'ชื่องบประมาณ', 'จำนวนเงิน', 'ใช้ไป', 'คงเหลือ'];
        $sheet->fromArray($headers, null, 'A1');
        
        // Style headers
        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => 'solid', 'color' => ['rgb' => '3B82F6']]
        ]);
        
        // Data
        $row = 2;
        foreach ($budgets as $budget) {
            $sheet->setCellValue("A{$row}", $budget['id']);
            $sheet->setCellValue("B{$row}", $budget['name']);
            $sheet->setCellValue("C{$row}", $budget['total_amount']);
            $sheet->setCellValue("D{$row}", $budget['spent_amount']);
            $sheet->setCellValue("E{$row}", $budget['total_amount'] - $budget['spent_amount']);
            $row++;
        }
        
        // Auto-size columns
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$filename}.xlsx\"");
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }
}
```

## 📄 PDF Export

### Using DomPDF

```php
use Dompdf\Dompdf;
use Dompdf\Options;

class PdfExporter
{
    public function exportReport(string $html, string $filename): void
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'THSarabun');
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        $dompdf->stream($filename, ['Attachment' => true]);
    }
}
```

### PDF Template

```php
// resources/views/reports/budget_summary.php
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'THSarabun', sans-serif; font-size: 16px; }
        .header { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #3B82F6; color: white; }
        .total { font-weight: bold; background: #f0f0f0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>รายงานสรุปงบประมาณ</h1>
        <p>ประจำปีงบประมาณ <?= $fiscalYear ?></p>
    </div>
    
    <table>
        <tr>
            <th>รายการ</th>
            <th>งบประมาณ</th>
            <th>ใช้จ่าย</th>
            <th>คงเหลือ</th>
        </tr>
        <?php foreach ($budgets as $b): ?>
        <tr>
            <td><?= $b['name'] ?></td>
            <td style="text-align: right"><?= number_format($b['total']) ?></td>
            <td style="text-align: right"><?= number_format($b['spent']) ?></td>
            <td style="text-align: right"><?= number_format($b['remaining']) ?></td>
        </tr>
        <?php endforeach; ?>
        <tr class="total">
            <td>รวมทั้งสิ้น</td>
            <td style="text-align: right"><?= number_format($total) ?></td>
            <td style="text-align: right"><?= number_format($totalSpent) ?></td>
            <td style="text-align: right"><?= number_format($totalRemaining) ?></td>
        </tr>
    </table>
</body>
</html>
```

## 📋 Report Templates

### Export Controller

```php
class ReportController
{
    public function exportBudgets(Request $request): void
    {
        $format = $request->get('format', 'xlsx');
        $fiscalYear = $request->get('year', date('Y'));
        
        $budgets = Budget::getByFiscalYear($fiscalYear);
        
        $filename = "budget_report_{$fiscalYear}";
        
        if ($format === 'xlsx') {
            (new ExcelExporter())->exportBudgets($budgets, $filename);
        } elseif ($format === 'pdf') {
            $html = View::renderToString('reports/budget_summary', [
                'budgets' => $budgets,
                'fiscalYear' => $fiscalYear + 543
            ]);
            (new PdfExporter())->exportReport($html, $filename);
        }
    }
}
```
