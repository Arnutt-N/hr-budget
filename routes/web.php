<?php
/**
 * Web Routes
 * 
 * Define all application routes
 */

use App\Core\Router;
// Legacy web remnants (document vault FileController, ThaID login) are
// referenced inline by fully-qualified name below — no top-level `use` needed.
// The budget-execution reporting web routes were retired post-cutover (recover
// from the `pre-budgets-retire` tag); all other web controllers were retired in
// the Phase 6 SPA cutover (recover from the `pre-spa-cutover` tag).
use App\Api\Controllers\AuthController as ApiAuthController;
use App\Api\Controllers\ThaIdController as ApiThaIdController;
use App\Api\Controllers\BudgetRequestController as ApiBudgetRequestController;
use App\Api\Controllers\FiscalYearController as ApiFiscalYearController;
use App\Api\Controllers\OrganizationController as ApiOrganizationController;
use App\Api\Controllers\BudgetCategoryController as ApiBudgetCategoryController;
use App\Api\Controllers\UserController as ApiUserController;
use App\Api\Controllers\NotificationController as ApiNotificationController;
use App\Api\Controllers\FileController as ApiFileController;
use App\Api\Controllers\VaultFolderController as ApiVaultFolderController;
use App\Api\Controllers\VaultFileController as ApiVaultFileController;
use App\Api\Controllers\DivisionController as ApiDivisionController;
use App\Api\Controllers\PlanController as ApiPlanController;
use App\Api\Controllers\TargetTypeController as ApiTargetTypeController;
use App\Api\Controllers\BudgetTargetController as ApiBudgetTargetController;
use App\Api\Controllers\DashboardController as ApiDashboardController;
use App\Api\Controllers\BudgetExecutionController as ApiBudgetExecutionController;
use App\Api\Controllers\DisbursementSessionController as ApiDisbursementSessionController;
use App\Api\Controllers\DisbursementRecordController as ApiDisbursementRecordController;
use App\Api\Controllers\RoleController as ApiRoleController;
use App\Api\Controllers\PermissionController as ApiPermissionController;
use App\Api\Controllers\AccessGrantController as ApiAccessGrantController;
use App\Api\Controllers\MeController as ApiMeController;
use App\Api\Controllers\ApprovalChainController as ApiApprovalChainController;
use App\Api\Responses\ApiResponse;

// ====== REST API v1 Routes ======
Router::get('/api/v1/health', function () {
    ApiResponse::ok([
        'version' => '0.1.0',
        'time'    => date('c'),
        'env'     => $_ENV['APP_ENV'] ?? 'unknown',
    ]);
});
Router::post('/api/v1/auth/login', [ApiAuthController::class, 'login']);
Router::post('/api/v1/auth/logout', [ApiAuthController::class, 'logout']);
Router::get('/api/v1/auth/me', [ApiAuthController::class, 'me']);

// ThaID (DOPA) OAuth2 — config-gated; status is XHR-JSON, login/callback are
// browser redirects. Dormant unless THAID_* credentials are configured.
Router::get('/api/v1/auth/thaid/status',   [ApiThaIdController::class, 'status']);
Router::get('/api/v1/auth/thaid/login',    [ApiThaIdController::class, 'login']);
Router::get('/api/v1/auth/thaid/callback', [ApiThaIdController::class, 'callback']);

// Fiscal Year CRUD
Router::get('/api/v1/fiscal-years', [ApiFiscalYearController::class, 'list']);
Router::post('/api/v1/fiscal-years', [ApiFiscalYearController::class, 'create']);
Router::get('/api/v1/fiscal-years/{id}', [ApiFiscalYearController::class, 'show']);
Router::put('/api/v1/fiscal-years/{id}', [ApiFiscalYearController::class, 'update']);
Router::delete('/api/v1/fiscal-years/{id}', [ApiFiscalYearController::class, 'delete']);
Router::post('/api/v1/fiscal-years/{id}/set-current', [ApiFiscalYearController::class, 'setCurrent']);

// Organization CRUD
Router::get('/api/v1/organizations', [ApiOrganizationController::class, 'list']);
Router::post('/api/v1/organizations', [ApiOrganizationController::class, 'create']);
Router::get('/api/v1/organizations/{id}', [ApiOrganizationController::class, 'show']);
Router::put('/api/v1/organizations/{id}', [ApiOrganizationController::class, 'update']);
Router::delete('/api/v1/organizations/{id}', [ApiOrganizationController::class, 'delete']);

// ====== RBAC: roles / permissions / access grants (Phase 1) ======
// Effective permissions + org scope of the current user (drives SPA UI).
Router::get('/api/v1/me/permissions', [ApiMeController::class, 'permissions']);
// Permission catalogue (role.manage)
Router::get('/api/v1/permissions', [ApiPermissionController::class, 'list']);
// Role management (role.manage)
Router::get('/api/v1/roles', [ApiRoleController::class, 'list']);
Router::post('/api/v1/roles', [ApiRoleController::class, 'create']);
Router::get('/api/v1/roles/{id}', [ApiRoleController::class, 'show']);
Router::put('/api/v1/roles/{id}', [ApiRoleController::class, 'update']);
Router::delete('/api/v1/roles/{id}', [ApiRoleController::class, 'delete']);
// Per-user access grants (user.manage; org_admin limited to own subtree)
Router::get('/api/v1/users/{id}/access-grants', [ApiAccessGrantController::class, 'listForUser']);
Router::post('/api/v1/users/{id}/access-grants', [ApiAccessGrantController::class, 'create']);
Router::delete('/api/v1/access-grants/{id}', [ApiAccessGrantController::class, 'delete']);

// ====== Multi-step approval chain (Phase 4): กอง → กรม → กระทรวง ======
Router::get('/api/v1/approval-levels', [ApiApprovalChainController::class, 'levels']);
Router::get('/api/v1/requests/{id}/approval', [ApiApprovalChainController::class, 'status']);
Router::post('/api/v1/requests/{id}/approval/approve', [ApiApprovalChainController::class, 'approve']);
Router::post('/api/v1/requests/{id}/approval/reject', [ApiApprovalChainController::class, 'reject']);

// Budget Category CRUD
Router::get('/api/v1/categories', [ApiBudgetCategoryController::class, 'list']);
Router::get('/api/v1/categories/tree', [ApiBudgetCategoryController::class, 'tree']);
Router::post('/api/v1/categories', [ApiBudgetCategoryController::class, 'create']);
Router::get('/api/v1/categories/{id}', [ApiBudgetCategoryController::class, 'show']);
Router::put('/api/v1/categories/{id}', [ApiBudgetCategoryController::class, 'update']);
Router::delete('/api/v1/categories/{id}', [ApiBudgetCategoryController::class, 'delete']);
// Category Items
Router::get('/api/v1/categories/{id}/items', [ApiBudgetCategoryController::class, 'listItems']);
Router::post('/api/v1/categories/{id}/items', [ApiBudgetCategoryController::class, 'createItem']);
Router::put('/api/v1/categories/{categoryId}/items/{itemId}', [ApiBudgetCategoryController::class, 'updateItem']);
Router::delete('/api/v1/categories/{categoryId}/items/{itemId}', [ApiBudgetCategoryController::class, 'deleteItem']);
Router::post('/api/v1/categories/{categoryId}/items/{itemId}/restore', [ApiBudgetCategoryController::class, 'restoreItem']);

// User Management CRUD (admin-only)
Router::get('/api/v1/users', [ApiUserController::class, 'list']);
Router::post('/api/v1/users', [ApiUserController::class, 'create']);
Router::get('/api/v1/users/{id}', [ApiUserController::class, 'show']);
Router::put('/api/v1/users/{id}', [ApiUserController::class, 'update']);
Router::delete('/api/v1/users/{id}', [ApiUserController::class, 'delete']);

// Division CRUD (admin-only)
Router::get('/api/v1/divisions', [ApiDivisionController::class, 'list']);
Router::post('/api/v1/divisions', [ApiDivisionController::class, 'create']);
Router::get('/api/v1/divisions/{id}', [ApiDivisionController::class, 'show']);
Router::put('/api/v1/divisions/{id}', [ApiDivisionController::class, 'update']);
Router::delete('/api/v1/divisions/{id}', [ApiDivisionController::class, 'delete']);

// Budget Plan CRUD (admin-only, soft delete)
Router::get('/api/v1/plans', [ApiPlanController::class, 'list']);
Router::post('/api/v1/plans', [ApiPlanController::class, 'create']);
Router::get('/api/v1/plans/{id}', [ApiPlanController::class, 'show']);
Router::put('/api/v1/plans/{id}', [ApiPlanController::class, 'update']);
Router::delete('/api/v1/plans/{id}', [ApiPlanController::class, 'delete']);

// Target Type CRUD (admin-only)
Router::get('/api/v1/target-types', [ApiTargetTypeController::class, 'list']);
Router::post('/api/v1/target-types', [ApiTargetTypeController::class, 'create']);
Router::get('/api/v1/target-types/{id}', [ApiTargetTypeController::class, 'show']);
Router::put('/api/v1/target-types/{id}', [ApiTargetTypeController::class, 'update']);
Router::delete('/api/v1/target-types/{id}', [ApiTargetTypeController::class, 'delete']);

// Budget Target CRUD (admin-only)
Router::get('/api/v1/targets', [ApiBudgetTargetController::class, 'list']);
Router::post('/api/v1/targets', [ApiBudgetTargetController::class, 'create']);
Router::get('/api/v1/targets/{id}', [ApiBudgetTargetController::class, 'show']);
Router::put('/api/v1/targets/{id}', [ApiBudgetTargetController::class, 'update']);
Router::delete('/api/v1/targets/{id}', [ApiBudgetTargetController::class, 'delete']);

// Dashboard (read-only, any authenticated user)
Router::get('/api/v1/dashboard/summary', [ApiDashboardController::class, 'summary']);
Router::get('/api/v1/dashboard/chart-data', [ApiDashboardController::class, 'chartData']);

// Budget Execution reporting (read-only, any authenticated user)
Router::get('/api/v1/budget-execution/years', [ApiBudgetExecutionController::class, 'years']);
Router::get('/api/v1/budget-execution/export', [ApiBudgetExecutionController::class, 'export']);
Router::get('/api/v1/budget-execution', [ApiBudgetExecutionController::class, 'report']);

// Budget Request CRUD + Approval
Router::get('/api/v1/requests', [ApiBudgetRequestController::class, 'list']);
Router::post('/api/v1/requests', [ApiBudgetRequestController::class, 'create']);
Router::get('/api/v1/requests/{id}', [ApiBudgetRequestController::class, 'show']);
Router::put('/api/v1/requests/{id}', [ApiBudgetRequestController::class, 'update']);
Router::delete('/api/v1/requests/{id}', [ApiBudgetRequestController::class, 'delete']);
Router::post('/api/v1/requests/{id}/submit', [ApiBudgetRequestController::class, 'submit']);
Router::post('/api/v1/requests/{id}/approve', [ApiBudgetRequestController::class, 'approve']);
Router::post('/api/v1/requests/{id}/reject', [ApiBudgetRequestController::class, 'reject']);

// Notifications — static routes before parameterized
Router::get('/api/v1/notifications', [ApiNotificationController::class, 'list']);
Router::get('/api/v1/notifications/unread-count', [ApiNotificationController::class, 'unreadCount']);
Router::post('/api/v1/notifications/read-all', [ApiNotificationController::class, 'markAllRead']);
Router::post('/api/v1/notifications/{id}/read', [ApiNotificationController::class, 'markRead']);

// File Attachments
Router::post('/api/v1/requests/{id}/files', [ApiFileController::class, 'upload']);
Router::get('/api/v1/requests/{id}/files', [ApiFileController::class, 'list']);
Router::get('/api/v1/files/{id}/download', [ApiFileController::class, 'download']);
Router::delete('/api/v1/files/{id}', [ApiFileController::class, 'delete']);

// Document Vault — static / more-specific routes BEFORE parameterized {id}
Router::get('/api/v1/vault/years', [ApiVaultFolderController::class, 'years']);
Router::post('/api/v1/vault/years', [ApiVaultFolderController::class, 'initialize']);
Router::get('/api/v1/vault/folders/tree', [ApiVaultFolderController::class, 'tree']);
Router::get('/api/v1/vault/folders', [ApiVaultFolderController::class, 'listFolders']);
Router::post('/api/v1/vault/folders', [ApiVaultFolderController::class, 'create']);
Router::get('/api/v1/vault/folders/{id}/files', [ApiVaultFolderController::class, 'listFiles']);
Router::post('/api/v1/vault/folders/{id}/files', [ApiVaultFolderController::class, 'upload']);
Router::delete('/api/v1/vault/folders/{id}', [ApiVaultFolderController::class, 'delete']);
Router::get('/api/v1/vault/files/{id}/download', [ApiVaultFileController::class, 'download']);
Router::delete('/api/v1/vault/files/{id}', [ApiVaultFileController::class, 'delete']);

// Disbursement Tracking — static + more-specific routes before parameterized {id}
Router::get('/api/v1/expense-structure', [ApiDisbursementRecordController::class, 'expenseStructure']);
Router::get('/api/v1/disbursement-sessions', [ApiDisbursementSessionController::class, 'list']);
Router::post('/api/v1/disbursement-sessions', [ApiDisbursementSessionController::class, 'create']);
Router::get('/api/v1/disbursement-sessions/{id}/activities', [ApiDisbursementSessionController::class, 'activities']);
Router::get('/api/v1/disbursement-sessions/{id}', [ApiDisbursementSessionController::class, 'show']);
Router::delete('/api/v1/disbursement-sessions/{id}', [ApiDisbursementSessionController::class, 'delete']);
Router::post('/api/v1/disbursement-records', [ApiDisbursementRecordController::class, 'create']);
Router::get('/api/v1/disbursement-records/{id}', [ApiDisbursementRecordController::class, 'show']);
Router::put('/api/v1/disbursement-records/{id}', [ApiDisbursementRecordController::class, 'update']);

// ====== Legacy web remnants — post-cutover (Phase 6) ======
// Everything the Vue SPA replaces (auth login, dashboard, budget-request, admin
// CRUD, tracking/disbursement) was retired in the Phase 6 cutover (recover from
// the `pre-spa-cutover` tag). Budget-execution reporting and the document vault
// were retired afterwards once the SPA reached parity (recover from
// `pre-budgets-retire` / `pre-files-retire`). The only server-rendered web
// surface left is the ThaID login alias; every other path falls through to the
// SPA shell via Router::notFound().

// ThaID login — superseded by the SPA-facing /api/v1/auth/thaid/* flow.
// Kept as a backward-compatible 302 alias to the new entry point.
Router::get('/thaid/login', fn() => Router::redirect('/api/v1/auth/thaid/login'));

// Logout — ThaID still mints a PHP session, so keep a way to clear it. NOT web
// login, which stays retired. Clears the session and lands on the SPA root.
$logout = function () {
    \App\Core\Auth::logout();
    Router::redirect('/');
};
Router::get('/logout', $logout);
Router::post('/logout', $logout);
