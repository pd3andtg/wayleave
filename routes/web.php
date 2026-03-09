<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

// Landing page
Route::get('/', function () {
    return view('landing');
})->name('landing');

// Guest-only routes (redirect to dashboard if already logged in)
Route::middleware('guest')->group(function () {
    Route::get('/login',                [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',               [AuthController::class, 'login']);
    Route::get('/register',             [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',            [AuthController::class, 'register'])->name('register.submit');
    Route::get('/register/company',     [AuthController::class, 'showRegisterCompany'])->name('register.company');
    Route::post('/register/company',    [AuthController::class, 'registerCompany'])->name('register.company.submit');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Projects (to be implemented)
    Route::get('/projects', fn() => 'Coming soon')->name('projects.index');
    Route::get('/projects/{project}', fn() => 'Coming soon')->name('projects.show');

    // Admin routes (to be implemented)
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/companies', fn() => 'Coming soon')->name('companies.index');
        Route::get('/users',     fn() => 'Coming soon')->name('users.index');
        Route::get('/units',     fn() => 'Coming soon')->name('units.index');
    });
});
