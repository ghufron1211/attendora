<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LogbookController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\HolidayController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root to dashboard or login
Route::get('/', function () {
    return 'Attendora Railway Success';
});

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard (accessible by all authenticated users)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Map (accessible by all authenticated users)
    Route::get('/map', [MapController::class, 'index'])->name('map');

    // ─── User-only routes ───
    // Admin TIDAK boleh akses halaman absensi dan logbook user
    Route::middleware('role:user')->group(function () {
        // Attendance
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance');
        Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn']);
        Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut']);

        // Logbook (user)
        Route::get('/logbook', [LogbookController::class, 'index'])->name('logbook');
        Route::get('/logbook/create', [LogbookController::class, 'create'])->name('logbook.create');
        Route::post('/logbook', [LogbookController::class, 'store']);
    });

    // ─── Admin-only routes ───
    Route::middleware('role:admin')->group(function () {
        // User management
        Route::get('/admin/users', [UserManagementController::class, 'index'])->name('admin.users');
        Route::post('/admin/users/{user}/restore', [UserManagementController::class, 'restore'])->name('admin.users.restore');
        Route::delete('/admin/users/{user}/force', [UserManagementController::class, 'forceDelete'])->name('admin.users.forceDelete');
        Route::delete('/admin/users/{user}', [UserManagementController::class, 'destroy'])->name('admin.users.destroy');

        // Logbook review
        Route::get('/admin/logbook', [LogbookController::class, 'adminIndex'])->name('admin.logbook');
        Route::post('/admin/logbook/{logbook}/approve', [LogbookController::class, 'approve']);
        Route::post('/admin/logbook/{logbook}/reject', [LogbookController::class, 'reject']);

        // Export
        Route::get('/admin/export/excel', [ExportController::class, 'exportExcel'])->name('admin.export.excel');

        // Holiday Management
        Route::get('/admin/holidays', [HolidayController::class, 'index'])->name('admin.holidays');
        Route::post('/admin/holidays', [HolidayController::class, 'store'])->name('admin.holidays.store');
        Route::put('/admin/holidays/{holiday}', [HolidayController::class, 'update'])->name('admin.holidays.update');
        Route::delete('/admin/holidays/{holiday}', [HolidayController::class, 'destroy'])->name('admin.holidays.destroy');
    });
});
