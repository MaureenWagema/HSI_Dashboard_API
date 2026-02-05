<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\PdepartmentController;
use App\Http\Controllers\Admin\PremiumController;
use App\Http\Controllers\Admin\KpiController;
use App\Http\Controllers\Admin\RatiosController;
use App\Http\Controllers\Admin\NEPController;
use App\Http\Controllers\Admin\ActualPremiumController;
use App\Http\Controllers\Admin\PowerBIController;
use App\Http\Controllers\Admin\BudgetController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| All routes in this file are automatically prefixed with `/api`
| and use the "api" middleware group by default.
|--------------------------------------------------------------------------
*/

// Public routes

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/kpi-items', [KpiController::class, 'createKpiItem']);
Route::get('/kpi-items', [KpiController::class, 'getKpiItems']);
Route::get('ratios/claims', [RatiosController::class, 'getClaimsRatio']);
Route::get('ratios/claims/average', [RatiosController::class, 'getClaimsAverage']);
Route::get('ratios/claims/debug', [RatiosController::class, 'debugClaimsRatio']);
Route::get('nep', [NEPController::class, 'getNEPSummary']);
Route::get('/ratios/expenses', [RatiosController::class, 'getExpenseRatio']);
Route::get('/ratios/combined', [RatiosController::class, 'getCombinedRatio']);
Route::get('/ratios/overall', [RatiosController::class, 'getOverallRatios']);
Route::get('/account-mappings', [RatiosController::class, 'getAccountMappings']);
Route::post('/sync-data', [RatiosController::class, 'syncData']);
Route::get('/sync-status', [\App\Http\Controllers\Api\SyncStatusController::class, 'getStatus']);

    Route::apiResource('budgets', BudgetController::class);


// Actual Premium Routes
Route::get('/actual-premiums', [ActualPremiumController::class, 'getActualPremiums']);
Route::post('/actual-premiums/sync', [ActualPremiumController::class, 'syncActualPremiums']);

// Protected routes (requires authentication and superadmin privileges)
Route::middleware(['auth:api', 'superadmin'])->group(function () {
    // Admin routes
    Route::prefix('admin')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::patch('/users/{id}/status', [UserController::class, 'toggleStatus']);
        Route::delete('/users/{id}', [UserController::class, 'deleteUsers']);
        Route::patch('/users/{id}/password', [UserController::class, 'changePassword']);
    });

    Route::get('/users/count', [UserController::class, 'countUsers']);
    Route::get('/departments/count', [DepartmentController::class, 'countDepartments']);

    // Premium Routes
    Route::get('/premiums', [PremiumController::class, 'index']);
    Route::post('/premiums', [PremiumController::class, 'store']);
    Route::delete('/premiums/{id}', [PremiumController::class, 'destroy']);
    Route::apiResource('pdepartments', PdepartmentController::class);
    Route::get('/premiums/ytd', [PremiumController::class, 'getYtd']);
    Route::apiResource('premiums', PremiumController::class);

//auth
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

 //powerbi emmbedding   

    Route::get('/powerbi/embed', [PowerBIController::class, 'getEmbedConfig']);

    // KPI Routes
    Route::prefix('kpi')->group(function () {
        // Departments
        Route::get('/departments', [KpiController::class, 'getDepartments']);
        Route::get('/departments/{id}', [KpiController::class, 'getDepartment']);
        Route::post('/departments', [KpiController::class, 'createDepartment']);
        Route::put('/departments/{id}', [KpiController::class, 'updateDepartment']);
        Route::delete('/departments/{id}', [KpiController::class, 'deleteDepartment']);

        // KPI Items
        Route::get('/kpi-items', [KpiController::class, 'getKpiItems']);
        Route::get('/kpi-items/{id}', [KpiController::class, 'getKpiItem']);
        Route::post('/kpi-items', [KpiController::class, 'createKpiItem']);
        Route::put('/kpi-items/{id}', [KpiController::class, 'updateKpiItem']);
        Route::delete('/kpi-items/{id}', [KpiController::class, 'deleteKpiItem']);

        // Employees
        Route::get('/employees', [KpiController::class, 'getEmployees']);
        Route::get('/employees/{id}', [KpiController::class, 'getEmployee']);
        Route::post('/employees', [KpiController::class, 'createEmployee']);
        Route::put('/employees/{id}', [KpiController::class, 'updateEmployee']);
        Route::delete('/employees/{id}', [KpiController::class, 'deleteEmployee']);

        // KPI Scores
        Route::get('/kpi-scores', [KpiController::class, 'getKpiScores']);
        Route::get('/kpi-scores/{id}', [KpiController::class, 'getKpiScore']);
        Route::post('/kpi-scores', [KpiController::class, 'createKpiScore']);
        Route::put('/kpi-scores/{id}', [KpiController::class, 'updateKpiScore']);
        Route::delete('/kpi-scores/{id}', [KpiController::class, 'deleteKpiScore']);

        // Employee Summaries
        Route::get('/employee-summaries', [KpiController::class, 'getEmployeeSummaries']);
        Route::get('/employee-summaries/{id}', [KpiController::class, 'getEmployeeSummary']);
        Route::post('/employee-summaries', [KpiController::class, 'createEmployeeSummary']);
        Route::put('/employee-summaries/{id}', [KpiController::class, 'updateEmployeeSummary']);
        Route::delete('/employee-summaries/{id}', [KpiController::class, 'deleteEmployeeSummary']);

        // Utility Routes
        Route::post('/calculate-summary', [KpiController::class, 'calculateEmployeeSummary']);
        Route::get('/dashboard', [KpiController::class, 'getDashboardData']);
    });

});
