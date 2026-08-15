<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Dtos\CreateVacancyRecruitmentDto;
use App\Dtos\UpdateVacancyRecruitmentDto;
use App\Repositories\PositionRepository;
use App\Repositories\VacancyRecruitmentRepository;

final class VacancyRecruitmentService
{
    public function __construct(
        private readonly VacancyRecruitmentRepository $repo = new VacancyRecruitmentRepository(),
        private readonly PositionRepository $positionRepo = new PositionRepository(),
    ) {}

    /** @return array{data: array[], meta: array} */
    public function list(int $page, int $perPage, array $filters = []): array
    {
        $offset = ($page - 1) * $perPage;
        $total = $this->repo->count($filters);
        $data = $this->repo->findAll($perPage, $offset, $filters);

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

    public function create(string $role, CreateVacancyRecruitmentDto $dto): ?int
    {
        if ($role !== 'admin') {
            return null;
        }
        if ($this->positionRepo->findById($dto->positionId) === null) {
            return null;
        }
        $fy = Database::queryOne("SELECT id FROM fiscal_years WHERE id = ?", [$dto->fiscalYearId]);
        if ($fy === null) {
            return null;
        }

        return $this->repo->insert([
            'position_id' => $dto->positionId,
            'fiscal_year_id' => $dto->fiscalYearId,
            'type' => $dto->type,
            'doc_no' => $dto->docNo,
            'doc_date' => $dto->docDate,
            'is_active' => 1,
        ]);
    }

    public function update(string $role, int $id, UpdateVacancyRecruitmentDto $dto): bool
    {
        if ($role !== 'admin') {
            return false;
        }
        if (!empty($dto->validate())) {
            return false;
        }
        if ($this->repo->findById($id) === null) {
            return false;
        }
        return $this->repo->update($id, $dto->toUpdateData());
    }

    public function delete(string $role, int $id): bool
    {
        if ($role !== 'admin') {
            return false;
        }
        if ($this->repo->findById($id) === null) {
            return false;
        }
        return $this->repo->softDelete($id);
    }
}
