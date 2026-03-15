<?php

use App\Http\Controllers\Admin\AdminCompanyController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminUnitController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\Steps\BqInvController;
use App\Http\Controllers\Steps\CpcApplicationController;
use App\Http\Controllers\Steps\CpcReceivedController;
use App\Http\Controllers\Steps\PermitReceivedController;
use App\Http\Controllers\Steps\PermitSubmissionController;
use App\Http\Controllers\Steps\WayleavePaymentController;
use App\Http\Controllers\Steps\WayleavePhbtController;
use App\Http\Controllers\Steps\WorkNoticeController;
use Illuminate\Support\Facades\Route;

// Landing page
Route::get('/', function () {
    return view('landing');
})->name('landing');

// Guest-only routes (redirect to dashboard if already logged in)
Route::middleware('guest')->group(function () {
    Route::get('/login',                  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',                 [AuthController::class, 'login']);
    Route::get('/register',               [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',              [AuthController::class, 'register'])->name('register.submit');
    Route::get('/register/company',       [AuthController::class, 'showRegisterCompany'])->name('register.company');
    Route::post('/register/company',      [AuthController::class, 'registerCompany'])->name('register.company.submit');

    // Password reset
    Route::get('/forgot-password',        [ForgotPasswordController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password',       [ForgotPasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password',        [ForgotPasswordController::class, 'resetPassword'])->name('password.update');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ── Projects / Dashboard ──────────────────────────────────────────────────
    // /dashboard and /projects both load the project list — the dashboard IS the project list.
    Route::get('/dashboard', [ProjectController::class, 'index'])->name('dashboard');
    Route::get('/projects',  [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/create',    [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects',          [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');

    // File download — handles both local disk and S3 signed URLs
    Route::get('/projects/{project}/download', [ProjectController::class, 'downloadFile'])->name('projects.download');

    // ── Step 4: BQ/INV File Upload (Contractor — up to 6 files) ─────────────
    Route::post('/projects/{project}/bq-inv-files',                              [BqInvController::class, 'store'])->name('projects.bq-inv-files.store');

    // ── Step 5: Officer Endorses each BQ/INV File ────────────────────────────
    Route::post('/projects/{project}/bq-inv-files/{bqInvFile}/endorse',          [BqInvController::class, 'endorse'])->name('projects.bq-inv-files.endorse');

    // ── Step 6: Wayleave PBT Upload (Contractor — up to 3 PBTs) ─────────────
    Route::post('/projects/{project}/wayleave-pbts',                              [WayleavePhbtController::class, 'store'])->name('projects.wayleave-pbts.store');

    // ── Step 3 (Contractor): Replace wayleave file before endorsement ─────────
    Route::post('/projects/{project}/wayleave-pbts/{wayleavePhbt}/replace',       [WayleavePhbtController::class, 'replace'])->name('projects.wayleave-pbts.replace');

    // ── Step 6 (Officer): Overwrite wayleave file + auto-set endorsement ─────
    Route::post('/projects/{project}/wayleave-pbts/{wayleavePhbt}/endorse',       [WayleavePhbtController::class, 'endorse'])->name('projects.wayleave-pbts.endorse');

    // ── Step 7: Officer Records FI + Deposit Payment per PBT ─────────────────
    Route::post('/projects/{project}/wayleave-payments',                          [WayleavePaymentController::class, 'store'])->name('projects.wayleave-payments.store');

    // ── Step 8: Permit Submission to KUTT (Contractor) ───────────────────────
    Route::post('/projects/{project}/permit-submission', [PermitSubmissionController::class, 'store'])->name('projects.permit-submission.store');

    // ── Step 9: Permit Received (Contractor) ──────────────────────────────────
    Route::post('/projects/{project}/permit-received',   [PermitReceivedController::class, 'store'])->name('projects.permit-received.store');

    // ── Step 10: Work Notices (Contractor) ────────────────────────────────────
    Route::post('/projects/{project}/work-notice',       [WorkNoticeController::class, 'store'])->name('projects.work-notice.store');

    // ── Step 11: CPC Application (Contractor) ─────────────────────────────────
    Route::post('/projects/{project}/cpc-application',   [CpcApplicationController::class, 'store'])->name('projects.cpc-application.store');

    // ── Step 12: CPC Received → Project Completed (Contractor) ───────────────
    Route::post('/projects/{project}/cpc-received',      [CpcReceivedController::class, 'store'])->name('projects.cpc-received.store');

    // ── User Approval Queue (admin + officer) ────────────────────────────────
    Route::middleware('role:admin|officer')->group(function () {
        Route::get('/approvals',                          [ApprovalController::class, 'index'])->name('approvals.index');
        Route::post('/approvals/{user}/approve',          [ApprovalController::class, 'approve'])->name('approvals.approve');
        Route::post('/approvals/{user}/reject',           [ApprovalController::class, 'reject'])->name('approvals.reject');
    });

    // ── Admin pages (admin role only) ─────────────────────────────────────────
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        // Company registration requests — approve or reject
        Route::get('/companies',                        [AdminCompanyController::class, 'index'])->name('companies.index');
        Route::post('/companies/{company}/approve',     [AdminCompanyController::class, 'approve'])->name('companies.approve');
        Route::post('/companies/{company}/reject',      [AdminCompanyController::class, 'reject'])->name('companies.reject');
        Route::patch('/companies/{company}',            [AdminCompanyController::class, 'update'])->name('companies.update');
        Route::delete('/companies/{company}',           [AdminCompanyController::class, 'destroy'])->name('companies.destroy');

        // User management — view all users, change officer/admin roles
        Route::get('/users',                            [AdminUserController::class, 'index'])->name('users.index');
        Route::post('/users/{user}/role',               [AdminUserController::class, 'updateRole'])->name('users.update-role');
        Route::post('/users/{user}/suspend',            [AdminUserController::class, 'suspend'])->name('users.suspend');
        Route::post('/users/{user}/reactivate',         [AdminUserController::class, 'reactivate'])->name('users.reactivate');

        // Unit management — add new units (regions)
        Route::get('/units',                            [AdminUnitController::class, 'index'])->name('units.index');
        Route::post('/units',                           [AdminUnitController::class, 'store'])->name('units.store');
    });
});
