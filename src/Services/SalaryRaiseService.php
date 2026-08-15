<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Dtos\CreateSalaryRaiseRoundDto;
use App\Repositories\SalaryRaiseProgressRepository;
use App\Repositories\SalaryRaiseRoundRepository;

final class SalaryRaiseService
{
    public function __construct(
        private readonly SalaryRaiseRoundRepository $roundRepo = new SalaryRaiseRoundRepository(),
        private readonly SalaryRaiseProgressRepository $progressRepo = new SalaryRaiseProgressRepository(),
    ) {}

    public function listRounds(): array
    {
        return $this->roundRepo->findAll();
    }

    public function create(string $role, CreateSalaryRaiseRoundDto $dto): ?int
    {
        if ($role !== 'admin') {
            return null;
        }

        if ($this->roundRepo->findByMonthYear($dto->roundMonth, $dto->roundYearBe) !== null) {
            return null; // รอบนี้มีอยู่แล้ว (UNIQUE month+year)
        }

        return $this->roundRepo->insert([
            'round_month' => $dto->roundMonth,
            'round_year_be' => $dto->roundYearBe,
            'effective_date' => $dto->effectiveDate,
            'fiscal_year_id' => $dto->fiscalYearId,
            'include_in_budget' => $dto->includeInBudget ? 1 : 0,
            'is_active' => 1,
        ]);
    }

    /** เปิด/ปิดสวิตช์นับในงบ — "เปิดใช้วันหน้าคือ UPDATE ไม่ใช่ migration" ตามเอกสารออกแบบ */
    public function setIncludeInBudget(string $role, int $roundId, bool $include): bool
    {
        if ($role !== 'admin') {
            return false;
        }

        if ($this->roundRepo->findById($roundId) === null) {
            return false;
        }

        return $this->roundRepo->update($roundId, ['include_in_budget' => $include ? 1 : 0]);
    }

    public function listProgress(int $roundId): ?array
    {
        if ($this->roundRepo->findById($roundId) === null) {
            return null;
        }
        return $this->progressRepo->findByRound($roundId);
    }

    /** อัปเดตสถานะหน่วยงาน — upsert ต่อ (round, org) · completed ใส่ completed_at อัตโนมัติ */
    public function markProgress(string $role, int $roundId, int $organizationId, string $status, ?string $docNo): bool
    {
        if ($role !== 'admin') {
            return false;
        }

        if (!in_array($status, ['completed', 'pending'], true)) {
            return false;
        }

        if ($this->roundRepo->findById($roundId) === null) {
            return false;
        }

        $data = [
            'status' => $status,
            'completed_at' => $status === 'completed' ? date('Y-m-d H:i:s') : null,
            'doc_no' => $docNo,
        ];

        $existing = $this->progressRepo->findByRoundAndOrg($roundId, $organizationId);
        if ($existing === null) {
            try {
                $this->progressRepo->insert($data + [
                    'round_id' => $roundId,
                    'organization_id' => $organizationId,
                    'is_active' => 1,
                ]);
                return true;
            } catch (\Throwable $e) {
                return false; // organization_id ไม่มีจริง (FK)
            }
        }

        return $this->progressRepo->update((int) $existing['id'], $data);
    }

    /** สร้างแถว pending ให้ทุกหน่วยงานที่ยังไม่มี — จุดเริ่มติดตามรอบใหม่ */
    public function seedProgressForAllOrganizations(string $role, int $roundId): ?int
    {
        if ($role !== 'admin') {
            return null;
        }

        if ($this->roundRepo->findById($roundId) === null) {
            return null;
        }

        Database::beginTransaction();
        try {
            $rows = Database::query("SELECT id FROM organizations");
            $created = 0;
            foreach ($rows as $row) {
                $orgId = (int) $row['id'];
                if ($this->progressRepo->findByRoundAndOrg($roundId, $orgId) !== null) {
                    continue;
                }
                $this->progressRepo->insert([
                    'round_id' => $roundId,
                    'organization_id' => $orgId,
                    'status' => 'pending',
                    'is_active' => 1,
                ]);
                $created++;
            }
            Database::commit();
            return $created;
        } catch (\Throwable $e) {
            Database::rollback();
            return null;
        }
    }
}
