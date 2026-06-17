<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\AccessGrantRepository;
use App\Repositories\ApprovalChainRepository;

/**
 * Drives the multi-step budget-request approval chain (กอง → กรม → กระทรวง).
 *
 * Each level is bound to an approver role (approval_levels.role_code). To act at
 * the current level an actor must hold that role (active grant) AND have the
 * request's organization within their scope. Approving the last level finalizes
 * the request; rejecting at any level rejects it.
 */
final class ApprovalChainService
{
    public function __construct(
        private readonly ApprovalChainRepository $repo = new ApprovalChainRepository(),
        private readonly AccessScopeResolver $resolver = new AccessScopeResolver(),
        private readonly AccessGrantRepository $grants = new AccessGrantRepository(),
    ) {}

    /** @return array<int,array> the configured chain levels */
    public function levels(): array
    {
        return $this->repo->levels();
    }

    /**
     * Enter the request into the chain at level 1 (called on submit).
     * @return array{ok:bool,error?:string}
     */
    public function start(int $requestId): array
    {
        $req = $this->repo->findRequest($requestId);
        if ($req === null) {
            return ['ok' => false, 'error' => 'not_found'];
        }
        $this->repo->updateRequest($requestId, ['request_status' => 'pending', 'current_level' => 1]);
        return ['ok' => true];
    }

    /**
     * Act on the current step.
     * @param array<string,mixed> $actor
     * @param string $decision 'approve'|'reject'
     * @return array{ok:bool,status?:string,level?:?int,error?:string}
     */
    public function act(array $actor, int $requestId, string $decision, ?string $note): array
    {
        if (!in_array($decision, ['approve', 'reject'], true)) {
            return ['ok' => false, 'error' => 'invalid_decision'];
        }

        $req = $this->repo->findRequest($requestId);
        if ($req === null) {
            return ['ok' => false, 'error' => 'not_found'];
        }
        $level = $req['current_level'] !== null ? (int) $req['current_level'] : 0;
        if ($level < 1) {
            return ['ok' => false, 'error' => 'not_in_chain'];
        }
        if (($req['request_status'] ?? '') !== 'pending') {
            return ['ok' => false, 'error' => 'not_pending'];
        }

        $cfg = $this->repo->levelByNumber($level);
        if ($cfg === null) {
            return ['ok' => false, 'error' => 'no_level_config'];
        }

        $isSuper = ($actor['role'] ?? '') === 'admin';
        if (!$isSuper) {
            if (!$this->grants->userHasActiveRole((int) ($actor['id'] ?? 0), $cfg['role_code'])) {
                return ['ok' => false, 'error' => 'forbidden_wrong_level_role'];
            }
            $scope = $this->resolver->resolve($actor);
            $orgId = $req['org_id'] !== null ? (int) $req['org_id'] : null;
            if (!$scope['hasAll'] && ($orgId === null || !in_array($orgId, $scope['orgIds'], true))) {
                return ['ok' => false, 'error' => 'forbidden_out_of_scope'];
            }
        }

        $this->repo->recordAction([
            'budget_request_id' => $requestId,
            'action' => $decision === 'approve' ? 'approved' : 'rejected',
            'level' => $level,
            'user_id' => (int) ($actor['id'] ?? 0),
            'note' => $note,
        ]);

        if ($decision === 'reject') {
            $this->repo->updateRequest($requestId, ['request_status' => 'rejected', 'current_level' => null]);
            return ['ok' => true, 'status' => 'rejected', 'level' => null];
        }

        // approve: advance or finalize
        if ($level >= $this->repo->maxLevel()) {
            $this->repo->updateRequest($requestId, ['request_status' => 'approved', 'current_level' => null]);
            return ['ok' => true, 'status' => 'approved', 'level' => null];
        }
        $next = $level + 1;
        $this->repo->updateRequest($requestId, ['current_level' => $next]);
        return ['ok' => true, 'status' => 'pending', 'level' => $next];
    }

    /** @return array{request_status:?string,current_level:?int,history:array} */
    public function status(int $requestId): ?array
    {
        $req = $this->repo->findRequest($requestId);
        if ($req === null) {
            return null;
        }
        return [
            'request_status' => $req['request_status'] ?? null,
            'current_level' => $req['current_level'] !== null ? (int) $req['current_level'] : null,
            'history' => $this->repo->historyFor($requestId),
        ];
    }
}
