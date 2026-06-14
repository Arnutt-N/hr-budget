<?php

declare(strict_types=1);

namespace App\Dtos;

final class DisbursementSessionListQueryDto
{
    private const MAX_PER_PAGE = 100;

    public function __construct(
        public readonly ?int $fiscalYear = null,
        public readonly ?int $organizationId = null,
        public readonly ?int $recordMonth = null,
        public readonly int $page = 1,
        public readonly int $perPage = 20,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if ($this->page < 1) {
            $errors['page'] = 'หมายเลขหน้าต้องมากกว่า 0';
        }

        if ($this->perPage < 1 || $this->perPage > self::MAX_PER_PAGE) {
            $errors['per_page'] = 'จำนวนต่อหน้าต้องอยู่ระหว่าง 1-100';
        }

        if ($this->recordMonth !== null && ($this->recordMonth < 1 || $this->recordMonth > 12)) {
            $errors['record_month'] = 'เดือนต้องอยู่ระหว่าง 1-12';
        }

        if ($this->fiscalYear !== null && ($this->fiscalYear < 2400 || $this->fiscalYear > 2700)) {
            $errors['fiscal_year'] = 'ปีงบประมาณต้องอยู่ระหว่าง 2400-2700';
        }

        return $errors;
    }

    public function offset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }

    public static function fromQueryString(): self
    {
        $fiscalYear = isset($_GET['fiscal_year']) ? (int) $_GET['fiscal_year'] : null;
        if ($fiscalYear === 0) {
            $fiscalYear = null;
        }

        $organizationId = isset($_GET['organization_id']) ? (int) $_GET['organization_id'] : null;
        if ($organizationId === 0) {
            $organizationId = null;
        }

        $recordMonth = isset($_GET['record_month']) ? (int) $_GET['record_month'] : null;
        if ($recordMonth === 0) {
            $recordMonth = null;
        }

        return new self(
            fiscalYear: $fiscalYear,
            organizationId: $organizationId,
            recordMonth: $recordMonth,
            page: max(1, (int) ($_GET['page'] ?? 1)),
            perPage: min(self::MAX_PER_PAGE, max(1, (int) ($_GET['per_page'] ?? 20))),
        );
    }

    /** @return array<string,int> */
    public function toFilters(): array
    {
        $filters = [];
        if ($this->fiscalYear !== null) {
            $filters['fiscal_year'] = $this->fiscalYear;
        }
        if ($this->organizationId !== null) {
            $filters['organization_id'] = $this->organizationId;
        }
        if ($this->recordMonth !== null) {
            $filters['record_month'] = $this->recordMonth;
        }
        return $filters;
    }
}
