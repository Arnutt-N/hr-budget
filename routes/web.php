<?php
/**
 * Web Routes
 * 
 * Define all application routes
 */

use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\BudgetController;
use App\Controllers\BudgetRequestController;
use App\Controllers\BudgetExecutionController;
use App\Controllers\DisbursementController;

// Authentication Routes
Router::get('/login', [AuthController::class, 'showLogin']);
Router::post('/login', [AuthController::class, 'login']);
Router::get('/logout', [AuthController::class, 'logout']);
Router::post('/logout', [AuthController::class, 'logout']);
Router::get('/thaid/login', [AuthController::class, 'thaidLogin']);
Router::get('/forgot-password', [AuthController::class, 'showForgotPassword']);
Router::post('/forgot-password', [AuthController::class, 'forgotPassword']);

// Dashboard Routes
Router::get('/', [DashboardController::class, 'index']);
Router::get('/dashboard', [DashboardController::class, 'index']);
Router::get('/api/dashboard/chart-data', [DashboardController::class, 'getChartData']);

// Budget Execution Routes
Router::get('/budgets', [BudgetExecutionController::class, 'index']);
Router::get('/budgets/export', [BudgetExecutionController::class, 'export']);

// Budget List
Router::get('/budgets/list', [BudgetController::class, 'index']);

// Legacy redirect
Router::get('/execution', function() { Router::redirect('/budgets'); });

// Disbursement Form Flow Routes
Router::get('/budgets/tracking', [BudgetController::class, 'tracking']);
Router::get('/budgets/tracking/create', [BudgetController::class, 'trackingCreate']);
Router::post('/budgets/tracking/store-session', [BudgetController::class, 'storeSession']);
Router::get('/budgets/tracking/activities', [BudgetController::class, 'activities']);
Router::post('/budgets/tracking/create-record', [BudgetController::class, 'createRecord']);
Router::get('/budgets/tracking/{id}/form', [BudgetController::class, 'disbursementForm']);
Router::post('/budgets/tracking/{id}/save', [BudgetController::class, 'saveDisbursement']);
Router::post('/budgets/tracking/{id}/delete', [BudgetController::class, 'deleteSession']);

// Legacy / Helper Routes
Router::get('/budgets/tracking/tab', [BudgetController::class, 'getTrackingTab']);
Router::get('/budgets/create', [BudgetController::class, 'create']);
Router::post('/budgets', [BudgetController::class, 'store']);
Router::get('/budgets/{id}/edit', [BudgetController::class, 'edit']);
Router::post('/budgets/{id}', [BudgetController::class, 'update']);
Router::post('/budgets/{id}/delete', [BudgetController::class, 'destroy']);

// Budget Request Routes
Router::get('/requests/dashboard', [BudgetRequestController::class, 'dashboard']);
Router::get('/requests', [BudgetRequestController::class, 'index']);
Router::get('/requests/create', [BudgetRequestController::class, 'create']);
Router::post('/requests', [BudgetRequestController::class, 'store']);
Router::get('/requests/{id}/edit', [BudgetRequestController::class, 'edit']);
Router::post('/requests/{id}/update', [BudgetRequestController::class, 'update']);
Router::get('/requests/{id}', [BudgetRequestController::class, 'show']);
Router::post('/requests/{id}/submit', [BudgetRequestController::class, 'submit']);
Router::post('/requests/{id}/approve', [BudgetRequestController::class, 'approve']);
Router::post('/requests/{id}/reject', [BudgetRequestController::class, 'reject']);
Router::post('/requests/{id}/delete', [BudgetRequestController::class, 'destroy']);
Router::post('/requests/{id}/items', [BudgetRequestController::class, 'storeItem']);
Router::get('/requests/{id}/items/category', [BudgetRequestController::class, 'getCategoryItems']);
Router::post('/requests/{id}/items/update', [BudgetRequestController::class, 'updateItem']);
Router::post('/requests/{id}/items/{itemId}/delete', [BudgetRequestController::class, 'destroyItem']);

// Admin Routes
Router::get('/admin/categories', [\App\Controllers\AdminBudgetCategoryController::class, 'index']);
Router::get('/admin/categories/create', [\App\Controllers\AdminBudgetCategoryController::class, 'create']);
Router::post('/admin/categories/store', [\App\Controllers\AdminBudgetCategoryController::class, 'store']);
Router::get('/admin/categories/{id}/edit', [\App\Controllers\AdminBudgetCategoryController::class, 'edit']);
Router::post('/admin/categories/{id}/update', [\App\Controllers\AdminBudgetCategoryController::class, 'update']);
Router::post('/admin/categories/{id}/delete', [\App\Controllers\AdminBudgetCategoryController::class, 'destroy']);

Router::get('/admin/organizations', [\App\Controllers\AdminOrganizationController::class, 'index']);
Router::get('/admin/organizations/create', [\App\Controllers\AdminOrganizationController::class, 'create']);
Router::post('/admin/organizations/store', [\App\Controllers\AdminOrganizationController::class, 'store']);
Router::get('/admin/organizations/{id}/edit', [\App\Controllers\AdminOrganizationController::class, 'edit']);
Router::post('/admin/organizations/{id}/update', [\App\Controllers\AdminOrganizationController::class, 'update']);
Router::post('/admin/organizations/{id}/delete', [\App\Controllers\AdminOrganizationController::class, 'destroy']);

Router::get('/admin/target-types', [\App\Controllers\AdminTargetTypeController::class, 'index']);
Router::get('/admin/target-types/create', [\App\Controllers\AdminTargetTypeController::class, 'create']);
Router::post('/admin/target-types/store', [\App\Controllers\AdminTargetTypeController::class, 'store']);
Router::get('/admin/target-types/{id}/edit', [\App\Controllers\AdminTargetTypeController::class, 'edit']);
Router::post('/admin/target-types/{id}/update', [\App\Controllers\AdminTargetTypeController::class, 'update']);
Router::post('/admin/target-types/{id}/delete', [\App\Controllers\AdminTargetTypeController::class, 'destroy']);

Router::get('/admin/category-items', [\App\Controllers\AdminBudgetCategoryItemController::class, 'index']);
Router::get('/admin/category-items/create', [\App\Controllers\AdminBudgetCategoryItemController::class, 'create']);
Router::post('/admin/category-items/store', [\App\Controllers\AdminBudgetCategoryItemController::class, 'store']);
Router::get('/admin/category-items/{id}/edit', [\App\Controllers\AdminBudgetCategoryItemController::class, 'edit']);
Router::post('/admin/category-items/{id}/update', [\App\Controllers\AdminBudgetCategoryItemController::class, 'update']);
Router::post('/admin/category-items/{id}/delete', [\App\Controllers\AdminBudgetCategoryItemController::class, 'destroy']);
Router::post('/admin/category-items/{id}/restore', [\App\Controllers\AdminBudgetCategoryItemController::class, 'restore']);
Router::post('/admin/category-items/{id}/toggle', [\App\Controllers\AdminBudgetCategoryItemController::class, 'toggleActive']);

Router::get('/budgets/targets', [\App\Controllers\BudgetTargetController::class, 'index']);
Router::get('/budgets/targets/create', [\App\Controllers\BudgetTargetController::class, 'create']);
Router::post('/budgets/targets/store', [\App\Controllers\BudgetTargetController::class, 'store']);
Router::get('/budgets/targets/{id}/edit', [\App\Controllers\BudgetTargetController::class, 'edit']);
Router::post('/budgets/targets/{id}/update', [\App\Controllers\BudgetTargetController::class, 'update']);
Router::post('/budgets/targets/{id}/delete', [\App\Controllers\BudgetTargetController::class, 'destroy']);

Router::get('/budgets/tracking/tab', [BudgetController::class, 'getTrackingTab']);

Router::get('/files', [\App\Controllers\FileController::class, 'index']);
Router::post('/files/upload', [\App\Controllers\FileController::class, 'upload']);
Router::get('/files/{id}/download', [\App\Controllers\FileController::class, 'download']);
Router::post('/files/{id}/delete', [\App\Controllers\FileController::class, 'deleteFile']);
Router::post('/folders', [\App\Controllers\FileController::class, 'createFolder']);
Router::post('/folders/{id}/delete', [\App\Controllers\FileController::class, 'deleteFolder']);
Router::post('/files/init', [\App\Controllers\FileController::class, 'initializeYear']);

Router::get('/budgets/disbursements', [DisbursementController::class, 'index']);
Router::get('/budgets/disbursements/create', [DisbursementController::class, 'create']);
Router::post('/budgets/disbursements', [DisbursementController::class, 'store']);
Router::get('/budgets/disbursements/{id}', [DisbursementController::class, 'show']);
Router::get('/budgets/disbursements/{id}/edit', [DisbursementController::class, 'edit']);
Router::post('/budgets/disbursements/{id}', [DisbursementController::class, 'update']);
Router::post('/budgets/disbursements/{id}/delete', [DisbursementController::class, 'destroy']);
Router::get('/budgets/disbursements/{id}/items/create', [DisbursementController::class, 'createItem']);
Router::post('/budgets/disbursements/{id}/items', [DisbursementController::class, 'storeItem']);
Router::get('/budgets/disbursements/{id}/items/{itemId}/edit', [DisbursementController::class, 'editItem']);
Router::post('/budgets/disbursements/{id}/items/{itemId}', [DisbursementController::class, 'updateItem']);
Router::post('/budgets/disbursements/{id}/items/{itemId}/delete', [DisbursementController::class, 'destroyItem']);
Router::get('/api/budget-plans/outputs', [DisbursementController::class, 'getOutputs']);
Router::get('/api/budget-plans/activities', [DisbursementController::class, 'getActivities']);
