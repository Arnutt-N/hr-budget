<?php

declare(strict_types=1);

namespace App\Dtos;

final class BudgetRequestListQueryDto
{
    private const VALID_STATUSES = ['draft', 'saved', 'confirmed', 'pending', 'approved', 'rejected'];
    private const MAX_PER_PAGE = 100;

    public function __construct(
        public readonly ?string $status = null,
        public readonly ?int $fiscalYear = null,
        public readonly ?string $search = null,
        public readonly int $page = 1,
        public readonly int $perPage = 20,
    ) {}

    /**
     * @return array<string,string>
     */
    public function validate(): array
    {
        $errors = [];

        if ($this->status !== null && !in_array($this->status, self::VALID_STATUSES, true)) {
            $errors['status'] = 'สถานะไม่ถูกต้อง';
        }

        if ($this->page < 1) {
            $errors['page'] = 'หมายเลขหน้าต้องมากกว่า 0';
        }

        if ($this->perPage < 1 || $this->perPage > self::MAX_PER_PAGE) {
            $errors['per_page'] = 'จำนวนต่อหน้าต้องอยู่ระหว่าง 1-100';
        }

        return $errors;
    }

    public function offset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }

    public static function fromQueryString(): self
    {
        $status = $_GET['status'] ?? null;
        if ($status !== null) {
            $status = trim((string) $status);
            if ($status === '') {
                $status = null;
            }
        }

        $fiscalYear = isset($_GET['fiscal_year']) ? (int) $_GET['fiscal_year'] : null;
        if ($fiscalYear === 0) {
            $fiscalYear = null;
        }

        $search = $_GET['search'] ?? null;
        if ($search !== null) {
            $search = trim((string) $search);
            if ($search === '') {
                $search = null;
            }
        }

        return new self(
            status: $status,
            fiscalYear: $fiscalYear,
            search: $search,
            page: max(1, (int) ($_GET['page'] ?? 1)),
            perPage: min(self::MAX_PER_PAGE, max(1, (int) ($_GET['per_page'] ?? 20))),
        );
    }

    public function toFilters(): array
    {
        $filters = [];
        if ($this->status !== null) {
            $filters['status'] = $this->status;
        }
        if ($this->fiscalYear !== null) {
            $filters['fiscal_year'] = $this->fiscalYear;
        }
        if ($this->search !== null) {
            $filters['search'] = $this->search;
        }
        return $filters;
    }
}
