<?php

use App\Http\Controllers\Admin\AdminCompanyController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminUnitController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\Steps\BqInvController;
use App\Http\Controllers\Steps\CpcApplicationController;
use App\Http\Controllers\Steps\CpcReceivedController;
use App\Http\Controllers\Steps\InvPaymentController;
use App\Http\Controllers\Steps\PermitReceivedController;
use App\Http\Controllers\Steps\PermitSubmissionController;
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

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // ── Projects ──────────────────────────────────────────────────────────────
    Route::get('/projects',           [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/create',    [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects',          [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');

    // File download — handles both local disk and S3 signed URLs
    Route::get('/projects/{project}/download', [ProjectController::class, 'downloadFile'])->name('projects.download');

    // ── Step 2: BQ/INV Upload (Contractor) ───────────────────────────────────
    Route::post('/projects/{project}/bq-inv',         [BqInvController::class, 'store'])->name('projects.bq-inv.store');

    // ── Step 3: Officer Endorsement + Invoice Payments ────────────────────────
    Route::post('/projects/{project}/bq-inv/endorse', [BqInvController::class, 'endorse'])->name('projects.bq-inv.endorse');
    Route::post('/projects/{project}/inv-payments',   [InvPaymentController::class, 'store'])->name('projects.inv-payments.store');

    // ── Step 4: Wayleave PBT Upload (Contractor) ─────────────────────────────
    Route::post('/projects/{project}/wayleave-pbts',                          [WayleavePhbtController::class, 'store'])->name('projects.wayleave-pbts.store');

    // ── Step 5: Officer Endorses per PBT ─────────────────────────────────────
    Route::post('/projects/{project}/wayleave-pbts/{wayleavePhbt}/endorse',   [WayleavePhbtController::class, 'endorse'])->name('projects.wayleave-pbts.endorse');

    // ── Step 6: Permit Submission (Contractor) ────────────────────────────────
    Route::post('/projects/{project}/permit-submission', [PermitSubmissionController::class, 'store'])->name('projects.permit-submission.store');

    // ── Step 7: Permit Received (Contractor) ──────────────────────────────────
    Route::post('/projects/{project}/permit-received',   [PermitReceivedController::class, 'store'])->name('projects.permit-received.store');

    // ── Step 8: Work Notices + Site Photos (Contractor) ──────────────────────
    Route::post('/projects/{project}/work-notice',       [WorkNoticeController::class, 'store'])->name('projects.work-notice.store');

    // ── Step 9: CPC Application (Contractor) ──────────────────────────────────
    Route::post('/projects/{project}/cpc-application',   [CpcApplicationController::class, 'store'])->name('projects.cpc-application.store');

    // ── Step 10: CPC Received → Project Completed (Contractor) ───────────────
    Route::post('/projects/{project}/cpc-received',      [CpcReceivedController::class, 'store'])->name('projects.cpc-received.store');

    // ── Admin pages (admin role only) ─────────────────────────────────────────
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        // Company registration requests — approve or reject
        Route::get('/companies',                        [AdminCompanyController::class, 'index'])->name('companies.index');
        Route::post('/companies/{company}/approve',     [AdminCompanyController::class, 'approve'])->name('companies.approve');
        Route::post('/companies/{company}/reject',      [AdminCompanyController::class, 'reject'])->name('companies.reject');

        // User management — view all users, change officer/admin roles
        Route::get('/users',                            [AdminUserController::class, 'index'])->name('users.index');
        Route::post('/users/{user}/role',               [AdminUserController::class, 'updateRole'])->name('users.update-role');

        // Unit management — add new units (regions)
        Route::get('/units',                            [AdminUnitController::class, 'index'])->name('units.index');
        Route::post('/units',                           [AdminUnitController::class, 'store'])->name('units.store');
    });
});
