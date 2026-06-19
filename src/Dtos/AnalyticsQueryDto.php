<?php

declare(strict_types=1);

namespace App\Dtos;

/**
 * Input DTO for the analytics endpoints. Mirrors BudgetRequestListQueryDto:
 * readonly props, fromQueryString() pulls from $_GET, validate() returns a
 * field => Thai-message map (empty = valid).
 *
 * `dimension` is whitelisted against VALID_DIMENSIONS because the Service uses
 * it to pick a query shape (year/quarter/month) — never interpolate raw input
 * into SQL identifiers, so the whitelist doubles as an injection guard.
 *
 * `fiscal_year` is Buddhist era; absent → config('app').fiscal_year.current,
 * exactly like DashboardController::resolveFiscalYear().
 */
final class AnalyticsQueryDto
{
    public const VALID_DIMENSIONS = ['year', 'quarter', 'month'];

    private const FY_MIN = 2500;
    private const FY_MAX = 2600;

    public function __construct(
        public readonly int $fiscalYear,
        public readonly string $dimension = 'year',
    ) {}

    public static function fromQueryString(): self
    {
        $yearParam = $_GET['fiscal_year'] ?? null;
        if ($yearParam !== null && ctype_digit((string) $yearParam)) {
            $fiscalYear = (int) $yearParam;
        } else {
            $config = require __DIR__ . '/../../config/app.php';
            $fiscalYear = (int) ($config['fiscal_year']['current'] ?? 2569);
        }

        $dimension = $_GET['dimension'] ?? 'year';
        $dimension = trim((string) $dimension);
        if ($dimension === '') {
            $dimension = 'year';
        }

        return new self(
            fiscalYear: $fiscalYear,
            dimension: $dimension,
        );
    }

    /**
     * @return array<string,string>
     */
    public function validate(): array
    {
        $errors = [];

        if ($this->fiscalYear < self::FY_MIN || $this->fiscalYear > self::FY_MAX) {
            $errors['fiscal_year'] = 'ปีงบประมาณไม่ถูกต้อง';
        }

        if (!in_array($this->dimension, self::VALID_DIMENSIONS, true)) {
            $errors['dimension'] = 'มิติการเปรียบเทียบไม่ถูกต้อง';
        }

        return $errors;
    }
}
