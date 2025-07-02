<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    UserController,
    DashboardController,
    KonsumenController,
    LaporanController,
    TopupController,
    PembayaranController,
    NotifikasiController,
    Auth\RegisterController,
    Auth\ForgotPasswordController,
    Auth\ResetPasswordController
};

// =======================
// AUTHENTIKASI ROUTES
// =======================
Route::get('/', [UserController::class, 'showLoginForm'])->name('login');
Route::post('/', [UserController::class, 'login'])->name('auth.login');

// Forgot Password Routes
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('ShowPass');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');

// Register Routes
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// =======================
// AUTHENTICATED USER ROUTES
// =======================
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [UserController::class, 'logout'])->name('logout');
    Route::get('/informasi', [UserController::class, 'showInformasi'])->name('informasi');
});

// =======================
// ADMIN ONLY ROUTES
// =======================
Route::middleware(['auth', 'isadmin'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/api-data', [DashboardController::class, 'apiData'])->name('dashboard.api');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'chartData'])->name('dashboard.chart');

    // ✅ KONSUMEN MANAGEMENT
    Route::resource('konsumen', KonsumenController::class)->parameters([
        'konsumen' => 'no_identitas' // Gunakan no_identitas sebagai parameter
    ]);
    Route::post('/konsumen/{no_identitas}/reset-password', [KonsumenController::class, 'resetPassword'])
        ->name('konsumen.resetPassword');

    // ✅ PEMBAYARAN MANAGEMENT
    Route::prefix('pembayaran')->name('pembayaran.')->group(function () {
        Route::get('/', [PembayaranController::class, 'index'])->name('index');
        Route::get('/create/{tipe?}', [PembayaranController::class, 'create'])->name('create');
        Route::post('/', [PembayaranController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [PembayaranController::class, 'edit'])->name('edit');
        Route::put('/{id}', [PembayaranController::class, 'update'])->name('update');
        Route::delete('/{id}', [PembayaranController::class, 'destroy'])->name('destroy');
        
        // ✅ FIXED: Route yang hilang untuk update status
        Route::patch('/{id}/status', [PembayaranController::class, 'updateStatus'])->name('status');
    });

    // ✅ TOPUP MANAGEMENT
    Route::prefix('admin/topup')->name('admin.topup.')->group(function () {
        Route::get('/', [TopupController::class, 'index'])->name('index');
        Route::get('/histori', [TopupController::class, 'histori'])->name('histori');
        Route::post('/manual', [TopupController::class, 'topupManual'])->name('manual');
        Route::post('/create-manual', [TopupController::class, 'topupManual'])->name('create');
        Route::post('/scan', [TopupController::class, 'verifikasiQR'])->name('verifikasiQR');
        Route::patch('/{id}/confirm', [TopupController::class, 'konfirmasi'])->name('konfirmasi');
        Route::get('/{id}', [TopupController::class, 'show'])->name('show');
    });
    
    // Alias untuk backward compatibility
    Route::get('/admin/topup', [TopupController::class, 'index'])->name('admin.topup');

    // ✅ NOTIFIKASI MANAGEMENT
    Route::prefix('notifikasi')->name('notifikasi.')->group(function () {
        Route::get('/', [NotifikasiController::class, 'index'])->name('index');
        Route::post('/kirim', [NotifikasiController::class, 'kirim'])->name('kirim');
        Route::get('/kirim-form', function () {
            $users = \App\Models\User::all();
            return view('notifikasi.kirim', compact('users'));
        })->name('kirim.form');
    });

    // ✅ LAPORAN
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/konsumen', [LaporanController::class, 'laporanKonsumen'])->name('index');
        Route::get('/periode', [LaporanController::class, 'laporanPeriode'])->name('periode');
        Route::get('/export', [LaporanController::class, 'export'])->name('export');
    });

    // ✅ USER MANAGEMENT (Admin users)
    Route::prefix('admin/users')->name('admin.users.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('index');
        Route::get('/{id}/edit', [\App\Http\Controllers\Admin\UserController::class, 'edit'])->name('edit');
        Route::put('/{id}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('update');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('destroy');
    });
});

// =======================
// FALLBACK & API HELPER ROUTES
// =======================

// Route fallback untuk handling 404
Route::fallback(function () {
    if (request()->expectsJson()) {
        return response()->json([
            'status' => false,
            'message' => 'Endpoint tidak ditemukan'
        ], 404);
    }
    
    return redirect()->route('login')->with('error', 'Halaman tidak ditemukan');
});

// Health check route
Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
})->name('health.check');