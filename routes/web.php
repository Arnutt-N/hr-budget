<?php
/**
 * Web Routes
 * 
 * Define all application routes
 */

use App\Core\Router;
// Legacy web controllers kept for documented parity-gap routes only
// (ThaID login + budget-execution reporting). All other web controllers were
// retired in the Phase 6 SPA cutover — recover from the `pre-spa-cutover` tag.
use App\Controllers\AuthController;
use App\Controllers\BudgetExecutionController;
use App\Api\Controllers\AuthController as ApiAuthController;
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
use App\Api\Controllers\DisbursementSessionController as ApiDisbursementSessionController;
use App\Api\Controllers\DisbursementRecordController as ApiDisbursementRecordController;
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

// ====== Legacy web/MVC remnants — pending SPA parity (Phase 6 cutover) ======
// Everything the Vue SPA replaces (auth login, dashboard, budget-request,
// admin CRUD, tracking/disbursement) was retired here; its controllers/views
// were `git rm`-ed (recover from the `pre-spa-cutover` tag). The routes below
// are the only server-rendered web surfaces left — each has NO SPA equivalent:
//   - ThaID login (sets a session, redirects)
//   - Budget-execution reporting (read-only overview + Excel export)
//   - Document vault (per-fiscal-year file/folder management)
// All other paths fall through to the SPA shell via Router::notFound().

// ThaID login (parity gap — SPA login has no ThaID flow)
Router::get('/thaid/login', [AuthController::class, 'thaidLogin']);

// Logout (kept session remnants — ThaID, /budgets, /files — still use PHP
// session auth, so users need a way to end it). NOT web login, which stays
// retired. Clears the session and lands on the SPA root.
$logout = function () {
    \App\Core\Auth::logout();
    Router::redirect('/');
};
Router::get('/logout', $logout);
Router::post('/logout', $logout);

// Budget Execution reporting (parity gap — no SPA overview/export page)
Router::get('/budgets', [BudgetExecutionController::class, 'index']);
Router::get('/budgets/export', [BudgetExecutionController::class, 'export']);
Router::get('/execution', function() { Router::redirect('/budgets'); });

// Document vault (parity gap — SPA only has request-attachment upload)
Router::get('/files', [\App\Controllers\FileController::class, 'index']);
Router::post('/files/upload', [\App\Controllers\FileController::class, 'upload']);
Router::get('/files/{id}/download', [\App\Controllers\FileController::class, 'download']);
Router::post('/files/{id}/delete', [\App\Controllers\FileController::class, 'deleteFile']);
Router::post('/folders', [\App\Controllers\FileController::class, 'createFolder']);
Router::post('/folders/{id}/delete', [\App\Controllers\FileController::class, 'deleteFolder']);
Router::post('/files/init', [\App\Controllers\FileController::class, 'initializeYear']);
