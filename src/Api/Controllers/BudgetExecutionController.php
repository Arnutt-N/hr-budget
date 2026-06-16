<?php

declare(strict_types=1);

namespace App\Api\Controllers;

use App\Api\Middleware\AuthMiddleware;
use App\Api\Middleware\CorsMiddleware;
use App\Api\Responses\ApiResponse;
use App\Services\BudgetExecutionService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Read-only budget-execution report API. Any authenticated user may read
 * (no admin gate) — mirrors the legacy /budgets web page which only required
 * a logged-in session. Replaces App\Controllers\BudgetExecutionController.
 */
final class BudgetExecutionController
{
    public function __construct(
        private readonly BudgetExecutionService $service = new BudgetExecutionService(),
    ) {}

    public function report(): void
    {
        CorsMiddleware::apply();
        AuthMiddleware::require();

        try {
            ApiResponse::ok($this->service->report($this->resolveFiscalYear(), $this->resolveOrg()));
        } catch (\Throwable $e) {
            error_log("[BudgetExecutionController::report] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function years(): void
    {
        CorsMiddleware::apply();
        AuthMiddleware::require();

        try {
            ApiResponse::ok($this->service->availableYears());
        } catch (\Throwable $e) {
            error_log("[BudgetExecutionController::years] {$e->getMessage()}");
            ApiResponse::error('เกิดข้อผิดพลาดในระบบ', 500);
        }
    }

    public function export(): void
    {
        CorsMiddleware::apply();
        AuthMiddleware::require();

        try {
            $fiscalYear = $this->resolveFiscalYear();
            $rows = $this->service->exportRows($fiscalYear, $this->resolveOrg());
            $this->streamXlsx($rows, $fiscalYear);
        } catch (\Throwable $e) {
            error_log("[BudgetExecutionController::export] {$e->getMessage()}");
            ApiResponse::error('สร้างไฟล์ส่งออกไม่สำเร็จ', 500);
        }
    }

    /**
     * Build + stream the report as an xlsx attachment, then exit.
     * Filename uses the integer fiscal year only (no user-controlled string).
     *
     * @param array<int,array<string,mixed>> $rows
     */
    private function streamXlsx(array $rows, int $fiscalYear): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Budget Execution');
        $spreadsheet->getProperties()
            ->setCreator('HR Budget System')
            ->setTitle('รายงานผลการเบิกจ่ายงบประมาณ ปี ' . $fiscalYear);

        $headers = [
            'A' => 'หน่วยงาน',
            'B' => 'ผลผลิต/โครงการ',
            'C' => 'กิจกรรม',
            'D' => 'งบจัดสรร',
            'E' => 'โอน/เปลี่ยนแปลง',
            'F' => 'งบสุทธิ',
            'G' => 'เบิกจ่าย',
            'H' => 'PO คงค้าง',
            'I' => 'รอเบิก',
            'J' => 'รวมใช้ไป',
            'K' => 'คงเหลือ',
            'L' => '% ใช้ไป',
        ];
        foreach ($headers as $col => $text) {
            $sheet->setCellValue($col . '1', $text);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getStyle('A1:L1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4B5563']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        $rowNum = 2;
        foreach ($rows as $r) {
            $sheet->setCellValue('A' . $rowNum, $r['org_name']);
            $sheet->setCellValue('B' . $rowNum, $r['project_name']);
            $sheet->setCellValue('C' . $rowNum, $r['activity_name']);
            $sheet->setCellValue('D' . $rowNum, $r['allocated']);
            $sheet->setCellValue('E' . $rowNum, $r['transfer']);
            $sheet->setCellValue('F' . $rowNum, $r['net_budget']);
            $sheet->setCellValue('G' . $rowNum, $r['disbursed']);
            $sheet->setCellValue('H' . $rowNum, $r['po']);
            $sheet->setCellValue('I' . $rowNum, $r['pending']);
            $sheet->setCellValue('J' . $rowNum, $r['total_used']);
            $sheet->setCellValue('K' . $rowNum, $r['balance']);
            $sheet->setCellValue('L' . $rowNum, ((float) $r['used_percent']) / 100);

            $sheet->getStyle('D' . $rowNum . ':K' . $rowNum)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('L' . $rowNum)->getNumberFormat()->setFormatCode('0.0%');
            $sheet->getStyle('A' . $rowNum . ':L' . $rowNum)
                ->applyFromArray(['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]]);

            $rowNum++;
        }

        $filename = sprintf('budget_execution_%d.xlsx', $fiscalYear);
        if (!headers_sent()) {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('X-Content-Type-Options: nosniff');
            header('Cache-Control: max-age=0');
        }
        (new Xlsx($spreadsheet))->save('php://output');
        exit;
    }

    /** Fiscal year (Buddhist era) from ?year=, else config default. */
    private function resolveFiscalYear(): int
    {
        $param = $_GET['year'] ?? null;
        if ($param !== null && ctype_digit((string) $param)) {
            return (int) $param;
        }

        $config = require __DIR__ . '/../../../config/app.php';
        return (int) ($config['fiscal_year']['current'] ?? 2569);
    }

    /** Optional organization filter from ?org=; null when absent/invalid/0. */
    private function resolveOrg(): ?int
    {
        $param = $_GET['org'] ?? null;
        if ($param !== null && ctype_digit((string) $param) && (int) $param > 0) {
            return (int) $param;
        }

        return null;
    }
}
