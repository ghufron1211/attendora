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
    return auth()->check()
        ? redirect('/dashboard')
        : redirect('/login');
});

// Temporary Route for Database Migration & Seeding
Route::get('/debug-db-seed', function () {
    try {
        // Run migration
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        
        // Run seeder
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
        
        // Get counts
        $totalUsers = \App\Models\User::count();
        $totalAttendances = \App\Models\Attendance::count();
        $totalLogbooks = \App\Models\Logbook::count();
        $totalApproved = \App\Models\Logbook::where('status', 'approved')->count();
        
        // Get samples
        $latestAttendances = \App\Models\Attendance::with('user')
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->take(10)
            ->get()
            ->map(function ($att) {
                return [
                    'id' => $att->id,
                    'user' => $att->user ? $att->user->name : 'Unknown',
                    'tanggal' => $att->tanggal->toDateString(),
                    'jam_masuk' => $att->jam_masuk,
                    'jam_pulang' => $att->jam_pulang,
                    'status' => $att->status,
                    'device_info' => $att->device_info,
                ];
            });
            
        $latestLogbooks = \App\Models\Logbook::with('user', 'admin')
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->take(10)
            ->get()
            ->map(function ($lb) {
                return [
                    'id' => $lb->id,
                    'user' => $lb->user ? $lb->user->name : 'Unknown',
                    'tanggal' => $lb->tanggal->toDateString(),
                    'deskripsi' => $lb->deskripsi,
                    'status' => $lb->status,
                    'komentar_pembimbing' => $lb->komentar_pembimbing,
                    'tanda_tangan_admin' => $lb->tanda_tangan_admin ? 'Base64 Present' : 'Null',
                    'tanda_tangan_pembimbing' => $lb->tanda_tangan_pembimbing ? 'Base64 Present' : 'Null',
                ];
            });
            
        return response()->json([
            'success' => true,
            'message' => 'Database successfully migrated and seeded!',
            'stats' => [
                'total_users' => $totalUsers,
                'total_attendances' => $totalAttendances,
                'total_logbooks' => $totalLogbooks,
                'total_approved' => $totalApproved,
            ],
            'latest_attendances_sample' => $latestAttendances,
            'latest_logbooks_sample' => $latestLogbooks,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
});

Route::get('/debug-generate-logbooks', function () {
    try {
        // Run the logbook:generate command
        \Illuminate\Support\Facades\Artisan::call('logbook:generate');
        
        $totalLogbooks = \App\Models\Logbook::count();
        $totalApproved = \App\Models\Logbook::where('status', 'approved')->count();
        $totalPending = \App\Models\Logbook::where('status', 'pending')->count();
        $totalRejected = \App\Models\Logbook::where('status', 'rejected')->count();
        
        $latestLogbooks = \App\Models\Logbook::with('user')
            ->orderBy('tanggal', 'desc')
            ->take(10)
            ->get()
            ->map(function ($lb) {
                return [
                    'id' => $lb->id,
                    'user' => $lb->user ? $lb->user->name : 'Unknown',
                    'tanggal' => $lb->tanggal->toDateString(),
                    'deskripsi' => $lb->deskripsi,
                    'status' => $lb->status,
                    'komentar_pembimbing' => $lb->komentar_pembimbing,
                    'tanda_tangan_admin' => $lb->tanda_tangan_admin ? 'Base64 Signature Present' : 'Null',
                    'tanda_tangan_pembimbing' => $lb->tanda_tangan_pembimbing ? 'Base64 Signature Present' : 'Null',
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Logbooks successfully generated!',
            'stats' => [
                'total_logbooks_in_db' => $totalLogbooks,
                'total_approved' => $totalApproved,
                'total_pending' => $totalPending,
                'total_rejected' => $totalRejected,
            ],
            'latest_10_logbooks' => $latestLogbooks,
        ], 200, [], JSON_PRETTY_PRINT);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
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
