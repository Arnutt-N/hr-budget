<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Dtos\CreateFiscalYearDto;
use App\Dtos\UpdateFiscalYearDto;
use App\Repositories\FiscalYearRepository;

final class FiscalYearService
{
    public function __construct(
        private readonly FiscalYearRepository $repo = new FiscalYearRepository(),
    ) {}

    /** @return array{data: array[], meta: array} */
    public function list(int $page = 1, int $perPage = 50): array
    {
        $offset = ($page - 1) * $perPage;
        $total = $this->repo->count();
        $data = $this->repo->findAll($perPage, $offset);

        return [
            'data' => $data,
            'meta' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => $perPage > 0 ? (int) ceil($total / $perPage) : 0,
            ],
        ];
    }

    public function findById(int $id): ?array
    {
        return $this->repo->findById($id);
    }

    public function create(string $role, CreateFiscalYearDto $dto): ?int
    {
        if ($role !== 'admin') {
            return null;
        }

        $existing = $this->repo->findByYear($dto->year);
        if ($existing !== null) {
            return null;
        }

        Database::beginTransaction();
        try {
            if ($dto->isCurrent) {
                $this->repo->clearCurrent();
            }

            $id = $this->repo->insert([
                'year' => $dto->year,
                'start_date' => $dto->startDate,
                'end_date' => $dto->endDate,
                'is_current' => $dto->isCurrent ? 1 : 0,
                'is_closed' => 0,
            ]);

            Database::commit();
            return $id;
        } catch (\Throwable $e) {
            Database::rollback();
            return null;
        }
    }

    public function update(string $role, int $id, UpdateFiscalYearDto $dto): bool
    {
        if ($role !== 'admin') {
            return false;
        }

        $existing = $this->repo->findById($id);
        if ($existing === null) {
            return false;
        }

        $updateData = [];
        if ($dto->year !== null) {
            $updateData['year'] = $dto->year;
        }
        if ($dto->startDate !== null) {
            $updateData['start_date'] = $dto->startDate;
        }
        if ($dto->endDate !== null) {
            $updateData['end_date'] = $dto->endDate;
        }
        if ($dto->isClosed !== null) {
            $updateData['is_closed'] = $dto->isClosed ? 1 : 0;
        }

        if ($dto->isCurrent === true) {
            Database::beginTransaction();
            try {
                $this->repo->clearCurrent();
                $updateData['is_current'] = 1;
                $this->repo->update($id, $updateData);
                Database::commit();
                return true;
            } catch (\Throwable $e) {
                Database::rollback();
                return false;
            }
        }

        if (!empty($updateData)) {
            return $this->repo->update($id, $updateData);
        }

        return true;
    }

    public function delete(string $role, int $id): bool
    {
        if ($role !== 'admin') {
            return false;
        }

        return $this->repo->delete($id);
    }

    public function setCurrent(string $role, int $id): bool
    {
        if ($role !== 'admin') {
            return false;
        }

        $existing = $this->repo->findById($id);
        if ($existing === null) {
            return false;
        }

        Database::beginTransaction();
        try {
            Database::queryOne("SELECT id FROM fiscal_years WHERE id = ? FOR UPDATE", [$id]);
            $this->repo->clearCurrent();
            $this->repo->setCurrent($id);
            Database::commit();
            return true;
        } catch (\Throwable $e) {
            Database::rollback();
            return false;
        }
    }
}
