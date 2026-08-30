<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\BudgetExecutionRepository;
use App\Services\AccessScopeResolver;

/**
 * Budget-execution report orchestration for the SPA.
 *
 * Ports the legacy App\Controllers\BudgetExecutionController::index() logic into
 * the layered Repository → Service → Api Controller style. The repository runs
 * the SQL; this service groups rows by project, derives KPI figures, and shapes
 * the response (stats + project/activity breakdown + two ranked charts).
 *
 * Spending convention: total_used = disbursed + po + pending — the same
 * definition the SPA DashboardService already shows users, so this report's
 * headline numbers agree with the dashboard. Grand-total stats are summed from
 * the breakdown (not a separate query), so the header always equals Σ(rows).
 */
final class BudgetExecutionService
{
    private const CHART_TOP_PROJECTS = 5;
    private const ORG_CHART_LIMIT = 6;
    private const OTHERS_LABEL = 'อื่นๆ';
    private const UNNAMED_LABEL = 'ไม่ระบุ';

    /** Money + quarter columns accumulated from activity rows up to the project. */
    private const SUM_KEYS = ['allocated', 'transfer', 'disbursed', 'po', 'pending', 'q1', 'q2', 'q3', 'q4'];

    public function __construct(
        private readonly BudgetExecutionRepository $repo = new BudgetExecutionRepository(),
        private readonly AccessScopeResolver $scopeResolver = new AccessScopeResolver(),
    ) {}

    /**
     * Full execution report for one fiscal year (Buddhist era), optionally
     * scoped to a single organization.
     *
     * When `$user` is given the report is constrained to the caller's RBAC org
     * subtree (AccessScopeResolver::orgScopeFilter) and an explicit `$orgId`
     * outside that subtree denies the whole report; admins / 'all' grants see
     * everything. `$user = null` (legacy callers / tests) is unrestricted.
     *
     * @return array|null null = caller denied (org outside scope / no scope)
     */
    public function report(int $fiscalYear, ?int $orgId, ?array $user = null): ?array
    {
        $scope = $this->scopeFor($user, $orgId);
        if ($scope['denied']) {
            return null;
        }
        $filter = $scope['filter'];

        $grouped = [];
        foreach ($this->repo->breakdownRows($fiscalYear, $orgId, $filter) as $row) {
            $pid = (int) $row['project_id'];
            if (!isset($grouped[$pid])) {
                $grouped[$pid] = $this->newProject($pid, $row);
            }

            $money = $this->moneyFrom($row);
            $grouped[$pid]['activities'][] = array_merge(
                [
                    'activity_id' => (int) $row['activity_id'],
                    'activity_name' => (string) $row['activity_name'],
                ],
                $money,
                $this->derive($money),
            );

            foreach (self::SUM_KEYS as $key) {
                $grouped[$pid][$key] += $money[$key];
            }
        }

        $projects = array_map([$this, 'finalizeProject'], array_values($grouped));
        usort($projects, static fn (array $a, array $b): int => $b['allocated'] <=> $a['allocated']);

        return [
            'fiscal_year' => $fiscalYear,
            'organization_id' => $orgId,
            'stats' => $this->grandTotals($projects),
            'projects' => $projects,
            'category_chart' => $this->categoryChart($projects),
            'org_chart' => $this->orgChart($fiscalYear, $orgId, $filter),
        ];
    }

    /** Distinct fiscal years that have disbursement data, newest first. */
    public function availableYears(): array
    {
        return $this->repo->availableYears();
    }

    /**
     * Flat per-activity rows for the xlsx export. Denial propagates from
     * report() (null) so /export can never widen what /report hides.
     *
     * @return array<int,array<string,mixed>>|null null = caller denied
     */
    public function exportRows(int $fiscalYear, ?int $orgId, ?array $user = null): ?array
    {
        $report = $this->report($fiscalYear, $orgId, $user);
        if ($report === null) {
            return null;
        }

        $rows = [];
        foreach ($report['projects'] as $project) {
            foreach ($project['activities'] as $activity) {
                $rows[] = [
                    'org_name' => $project['org_name'],
                    'project_name' => $project['project_name'],
                    'activity_name' => $activity['activity_name'],
                    'allocated' => $activity['allocated'],
                    'transfer' => $activity['transfer'],
                    'net_budget' => $activity['net_budget'],
                    'disbursed' => $activity['disbursed'],
                    'po' => $activity['po'],
                    'pending' => $activity['pending'],
                    'total_used' => $activity['total_used'],
                    'balance' => $activity['balance'],
                    'used_percent' => $activity['used_percent'],
                ];
            }
        }

        return $rows;
    }

    /** @param array<string,mixed> $row */
    private function newProject(int $pid, array $row): array
    {
        $project = [
            'project_id' => $pid,
            'project_name' => (string) $row['project_name'],
            'org_name' => (string) $row['org_name'],
            'activities' => [],
        ];
        foreach (self::SUM_KEYS as $key) {
            $project[$key] = 0.0;
        }

        return $project;
    }

    /** @param array<string,mixed> $project */
    private function finalizeProject(array $project): array
    {
        $money = [];
        foreach (self::SUM_KEYS as $key) {
            $money[$key] = (float) $project[$key];
        }

        return array_merge(
            [
                'project_id' => $project['project_id'],
                'project_name' => $project['project_name'],
                'org_name' => $project['org_name'],
            ],
            $money,
            $this->derive($money),
            ['activities' => $project['activities']],
        );
    }

    /** @param array<string,mixed> $row */
    private function moneyFrom(array $row): array
    {
        return [
            'allocated' => (float) $row['allocated'],
            'transfer' => (float) $row['transfer'],
            'disbursed' => (float) $row['disbursed'],
            'po' => (float) $row['po'],
            'pending' => (float) $row['pending'],
            'q1' => (float) $row['q1'],
            'q2' => (float) $row['q2'],
            'q3' => (float) $row['q3'],
            'q4' => (float) $row['q4'],
        ];
    }

    /**
     * Derive net budget / used / balance / used% from raw money components.
     *
     * @param array<string,float> $m
     * @return array{net_budget:float,total_used:float,balance:float,used_percent:float}
     */
    private function derive(array $m): array
    {
        $net = $m['allocated'] + $m['transfer'];
        $used = $m['disbursed'] + $m['po'] + $m['pending'];

        return [
            'net_budget' => $net,
            'total_used' => $used,
            'balance' => $net - $used,
            'used_percent' => $net > 0 ? round($used / $net * 100, 1) : 0.0,
        ];
    }

    /** @param array<int,array<string,mixed>> $projects */
    private function grandTotals(array $projects): array
    {
        $allocated = $transfer = $disbursed = $po = $pending = 0.0;
        foreach ($projects as $p) {
            $allocated += $p['allocated'];
            $transfer += $p['transfer'];
            $disbursed += $p['disbursed'];
            $po += $p['po'];
            $pending += $p['pending'];
        }

        $net = $allocated + $transfer;
        $used = $disbursed + $po + $pending;

        return [
            'allocated' => $allocated,
            'transfer' => $transfer,
            'total_budget' => $net,
            'disbursed' => $disbursed,
            'pending' => $pending,
            'po' => $po,
            'total_used' => $used,
            'remaining' => $net - $used,
            'used_percent' => $net > 0 ? round($used / $net * 100, 1) : 0.0,
        ];
    }

    /**
     * Top-N projects by allocated + an "อื่นๆ" bucket for the remainder.
     * Expects $projects already sorted by allocated DESC.
     *
     * @param array<int,array<string,mixed>> $projects
     * @return array{labels:list<string>,values:list<float>}
     */
    private function categoryChart(array $projects): array
    {
        $top = array_slice($projects, 0, self::CHART_TOP_PROJECTS);
        $rest = array_slice($projects, self::CHART_TOP_PROJECTS);

        $labels = array_map(
            static fn (array $p): string => $p['project_name'] !== '' ? $p['project_name'] : self::UNNAMED_LABEL,
            $top,
        );
        $values = array_map(static fn (array $p): float => (float) $p['allocated'], $top);

        $othersSum = array_reduce($rest, static fn (float $c, array $p): float => $c + (float) $p['allocated'], 0.0);
        if ($othersSum > 0) {
            $labels[] = self::OTHERS_LABEL;
            $values[] = $othersSum;
        }

        return ['labels' => array_values($labels), 'values' => array_values($values)];
    }

    /**
     * Resolve the caller's report scope: denied (org outside granted subtree,
     * or '1=0' no-scope-at-all), unrestricted (admin / 'all' grant → no filter),
     * or a filter fragment constraining queries to the granted org ids.
     *
     * @param array<string,mixed>|null $user
     * @return array{denied:bool, filter:?array{sql:string,params:array<int,mixed>}}
     */
    private function scopeFor(?array $user, ?int $orgId): array
    {
        if ($user === null) {
            return ['denied' => false, 'filter' => null];
        }

        $filter = $this->scopeResolver->orgScopeFilter($user, 'ds.organization_id');
        if ($filter['sql'] === '1=0') {
            return ['denied' => true, 'filter' => null];
        }
        if ($filter['sql'] === '1=1') {
            return ['denied' => false, 'filter' => null];
        }
        if ($orgId !== null && !in_array($orgId, array_map(static fn ($v) => (int) $v, $filter['params']), true)) {
            return ['denied' => true, 'filter' => null];
        }

        return ['denied' => false, 'filter' => $filter];
    }

    /** @return array{labels:list<string>,values:list<float>} */
    private function orgChart(int $fiscalYear, ?int $orgId, ?array $filter): array
    {
        $rows = $this->repo->orgTotals($fiscalYear, $orgId, self::ORG_CHART_LIMIT, $filter);

        return [
            'labels' => array_map(static fn (array $r): string => (string) $r['name'], $rows),
            'values' => array_map(static fn (array $r): float => (float) $r['allocated'], $rows),
        ];
    }
}
