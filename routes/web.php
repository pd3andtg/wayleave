<?php

use App\Http\Controllers\Admin\AdminCompanyController;
use App\Http\Controllers\Admin\AdminNodeController;
use App\Http\Controllers\Admin\AdminUnitController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DocumentReferenceController;
use App\Http\Controllers\ExampleImageController;
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
    Route::get('/projects/{project}',    [ProjectController::class, 'show'])->name('projects.show');
    Route::put('/projects/{project}',    [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    // Cancel project — anyone with project access can cancel (reason compulsory)
    Route::post('/projects/{project}/cancel',   [ProjectController::class, 'cancel'])->name('projects.cancel');

    // Reopen cancelled project — admin only (enforced via policy)
    Route::post('/projects/{project}/reopen',   [ProjectController::class, 'reopen'])->name('projects.reopen');

    // File download — handles both local disk and S3 signed URLs
    Route::get('/projects/{project}/download', [ProjectController::class, 'downloadFile'])->name('projects.download');

    // ── Sections 2 & 3: BOQ/INV Items (shared table) ─────────────────────────
    // store: contractor adds new row (visible in both Section 2 and Section 3)
    // update: officer/admin updates eds_no, payment_status, endorsed file per row
    Route::post('/projects/{project}/boq-inv-items',                       [BqInvController::class, 'store'])->name('projects.boq-inv-items.store');
    Route::post('/projects/{project}/boq-inv-items/{boqInvItem}',          [BqInvController::class, 'update'])->name('projects.boq-inv-items.update');
    Route::delete('/projects/{project}/boq-inv-items/{boqInvItem}',        [BqInvController::class, 'destroy'])->name('projects.boq-inv-items.destroy');

    // ── Section 4: Wayleave PBT Upload (Contractor) ───────────────────────────
    Route::post('/projects/{project}/wayleave-pbts',                              [WayleavePhbtController::class, 'store'])->name('projects.wayleave-pbts.store');
    Route::post('/projects/{project}/wayleave-pbts/{wayleavePhbt}/replace',       [WayleavePhbtController::class, 'replace'])->name('projects.wayleave-pbts.replace');

    // ── Section 5: Officer Endorses Wayleave File ─────────────────────────────
    Route::post('/projects/{project}/wayleave-pbts/{wayleavePhbt}/endorse',       [WayleavePhbtController::class, 'endorse'])->name('projects.wayleave-pbts.endorse');

    // ── Section 6: Wayleave Payment Details (Officer) ─────────────────────────
    Route::post('/projects/{project}/wayleave-payments',                          [WayleavePaymentController::class, 'store'])->name('projects.wayleave-payments.store');
    Route::post('/projects/{project}/wayleave-payments/pbt',                      [WayleavePaymentController::class, 'storePbt'])->name('projects.wayleave-payments.store-pbt');

    // ── Section 7: BG & BD Received from FINSSO (Officer) ─────────────────────
    Route::post('/projects/{project}/wayleave-payments/{wayleavePayment}/received', [WayleavePaymentController::class, 'updateReceived'])->name('projects.wayleave-payments.received');

    // ── Section 8: Permit Submission to PBT (Contractor) ──────────────────────
    Route::post('/projects/{project}/permit-submission',                          [PermitSubmissionController::class, 'store'])->name('projects.permit-submission.store');
    Route::put('/projects/{project}/permit-submission/{permitSubmission}',        [PermitSubmissionController::class, 'update'])->name('projects.permit-submission.update');
    Route::delete('/projects/{project}/permit-submission/{permitSubmission}',     [PermitSubmissionController::class, 'destroy'])->name('projects.permit-submission.destroy');

    // ── Section 9: Permit Received (Contractor/Officer) ───────────────────────
    Route::post('/projects/{project}/permit-received',                            [PermitReceivedController::class, 'store'])->name('projects.permit-received.store');
    Route::put('/projects/{project}/permit-received/{permitReceived}',            [PermitReceivedController::class, 'update'])->name('projects.permit-received.update');
    Route::delete('/projects/{project}/permit-received/{permitReceived}',         [PermitReceivedController::class, 'destroy'])->name('projects.permit-received.destroy');

    // ── Section 10: Notis Mula Kerja (Contractor) ─────────────────────────────
    Route::post('/projects/{project}/notis-mula',        [WorkNoticeController::class, 'storeNotisMula'])->name('projects.notis-mula.store');

    // ── Section 11: Notis Siap Kerja (Contractor) ─────────────────────────────
    Route::post('/projects/{project}/notis-siap',        [WorkNoticeController::class, 'storeNotisSiap'])->name('projects.notis-siap.store');

    // ── Section 12: CPC Application (Contractor) ──────────────────────────────
    Route::post('/projects/{project}/cpc-application',                                        [CpcApplicationController::class, 'store'])->name('projects.cpc-application.store');
    Route::delete('/projects/{project}/cpc-application/{cpcApplication}/file/{field}',       [CpcApplicationController::class, 'destroyFile'])->name('projects.cpc-application.destroy-file');
    Route::delete('/projects/{project}/cpc-application/{cpcApplication}/files',              [CpcApplicationController::class, 'destroyAllFiles'])->name('projects.cpc-application.destroy-all-files');

    // ── Section 13: CPC Received → Project Completed (Contractor) ────────────
    Route::post('/projects/{project}/cpc-received',      [CpcReceivedController::class, 'store'])->name('projects.cpc-received.store');

    // ── Document References (reference library for contractors/officers) ────────
    Route::get('/document-references',                          [DocumentReferenceController::class, 'index'])->name('document-references.index');
    Route::get('/document-references/{documentReference}/download', [DocumentReferenceController::class, 'download'])->name('document-references.download');
    Route::middleware('role:admin')->group(function () {
        Route::post('/document-references',                     [DocumentReferenceController::class, 'store'])->name('document-references.store');
        Route::put('/document-references/{documentReference}',  [DocumentReferenceController::class, 'update'])->name('document-references.update');
        Route::delete('/document-references/{documentReference}', [DocumentReferenceController::class, 'destroy'])->name('document-references.destroy');
    });

    // ── Example/reference images (shown as visual guides on project detail) ───
    // View: all authenticated users. Upload/replace: admin + officer only.
    Route::get('/example-images/{key}',  [ExampleImageController::class, 'show'])->name('example-images.show');
    Route::middleware('role:admin|officer')->group(function () {
        Route::post('/example-images/{key}', [ExampleImageController::class, 'upload'])->name('example-images.upload');
    });

    // ── User Approval Queue (admin + officer) ────────────────────────────────
    Route::middleware('role:admin|officer')->group(function () {
        Route::get('/approvals',                          [ApprovalController::class, 'index'])->name('approvals.index');
        Route::post('/approvals/{user}/approve',          [ApprovalController::class, 'approve'])->name('approvals.approve');
        Route::post('/approvals/{user}/reject',           [ApprovalController::class, 'reject'])->name('approvals.reject');
    });

    // ── Admin pages (admin role only) ─────────────────────────────────────────
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        // Company registration requests — register directly, approve, reject, edit, delete
        Route::get('/companies',                        [AdminCompanyController::class, 'index'])->name('companies.index');
        Route::post('/companies',                       [AdminCompanyController::class, 'store'])->name('companies.store');
        Route::post('/companies/{company}/approve',     [AdminCompanyController::class, 'approve'])->name('companies.approve');
        Route::post('/companies/{company}/reject',      [AdminCompanyController::class, 'reject'])->name('companies.reject');
        Route::patch('/companies/{company}',            [AdminCompanyController::class, 'update'])->name('companies.update');
        Route::delete('/companies/{company}',           [AdminCompanyController::class, 'destroy'])->name('companies.destroy');

        // User management — register, view all users, edit, delete, change officer roles, suspend/reactivate
        Route::get('/users',                            [AdminUserController::class, 'index'])->name('users.index');
        Route::post('/users',                           [AdminUserController::class, 'store'])->name('users.store');
        Route::post('/users/{user}/role',               [AdminUserController::class, 'updateRole'])->name('users.update-role');
        Route::patch('/users/{user}',                   [AdminUserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}',                  [AdminUserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{user}/suspend',            [AdminUserController::class, 'suspend'])->name('users.suspend');
        Route::post('/users/{user}/reactivate',         [AdminUserController::class, 'reactivate'])->name('users.reactivate');

        // Unit management — add, rename, delete units (regions)
        Route::get('/units',                            [AdminUnitController::class, 'index'])->name('units.index');
        Route::post('/units',                           [AdminUnitController::class, 'store'])->name('units.store');
        Route::patch('/units/{unit}',                   [AdminUnitController::class, 'update'])->name('units.update');
        Route::delete('/units/{unit}',                  [AdminUnitController::class, 'destroy'])->name('units.destroy');

        // Node management — add/edit/delete TM nodes (Admin manages via UI)
        Route::get('/nodes',                            [AdminNodeController::class, 'index'])->name('nodes.index');
        Route::post('/nodes',                           [AdminNodeController::class, 'store'])->name('nodes.store');
        Route::post('/nodes/bulk',                      [AdminNodeController::class, 'storeBulk'])->name('nodes.storeBulk');
        Route::patch('/nodes/{node}',                   [AdminNodeController::class, 'update'])->name('nodes.update');
        Route::delete('/nodes/{node}',                  [AdminNodeController::class, 'destroy'])->name('nodes.destroy');
    });
});
