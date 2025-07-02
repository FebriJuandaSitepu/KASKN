<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    AuthApiController,
    DashboardApiController,
    UserApiController,
    KonsumenApiController,
    KonsumenAuthApiController,
    PembayaranApiController,
    LaporanApiController,
    TopupApiController,
    NotifikasiApiController
};

// =======================
// PUBLIC ROUTES (No Auth Required)
// =======================
Route::prefix('v1')->group(function () {
    
    // Health check
    Route::get('/health', function () {
        return response()->json([
            'status' => 'OK',
            'version' => '1.0',
            'timestamp' => now()
        ]);
    });

    // ✅ FIXED: Konsumen Authentication endpoints (PUBLIC) - NO RATE LIMITING FOR NOW
    Route::prefix('konsumen')->group(function () {
        Route::post('/login', [KonsumenAuthApiController::class, 'login']);
        Route::post('/register', [KonsumenAuthApiController::class, 'register']);
    });
    
    // Public info
    Route::get('/informasi', [UserApiController::class, 'informasi']);
});

// =======================
// AUTHENTICATED ROUTES (Konsumen)
// =======================
Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {

    // ✅ FIXED: Konsumen AUTH ENDPOINTS
    Route::prefix('konsumen')->group(function () {
        Route::post('/logout', [KonsumenAuthApiController::class, 'logout']);
        Route::get('/profile', [KonsumenAuthApiController::class, 'profile']);
        Route::put('/update', [KonsumenAuthApiController::class, 'updateProfile']);
    });

    // ✅ FIXED: KONSUMEN ENDPOINTS (Protected)
    Route::prefix('konsumen')->group(function () {
        Route::get('/', [KonsumenApiController::class, 'index']);
        Route::post('/', [KonsumenApiController::class, 'store']);
        Route::get('/{id}', [KonsumenApiController::class, 'show']);
        Route::put('/{id}', [KonsumenApiController::class, 'update']);
        Route::delete('/{id}', [KonsumenApiController::class, 'destroy']);
        Route::get('/{id}/statistics', [KonsumenApiController::class, 'statistics']);
        
        // Admin only
        Route::middleware(['isadmin'])->group(function () {
            Route::post('/{id}/reset-password', [KonsumenApiController::class, 'resetPassword']);
            Route::post('/{id}/update-saldo', [KonsumenApiController::class, 'updateSaldo']);
        });
    });

    // ✅ FIXED: PEMBAYARAN ENDPOINTS (Protected)
    Route::prefix('pembayaran')->group(function () {
        Route::get('/', [PembayaranApiController::class, 'index']);
        Route::post('/', [PembayaranApiController::class, 'store']);
        Route::get('/{id}', [PembayaranApiController::class, 'show']);
        Route::put('/{id}', [PembayaranApiController::class, 'update']);
        Route::delete('/{id}', [PembayaranApiController::class, 'destroy']);
        
        // Admin only
        Route::middleware(['isadmin'])->group(function () {
            Route::patch('/{id}/status', [PembayaranApiController::class, 'updateStatus']);
            Route::get('/statistik/all', [PembayaranApiController::class, 'statistik']);
        });
    });

    // ✅ FIXED: TOPUP ENDPOINTS (Protected)
    Route::prefix('topup')->group(function () {
        Route::get('/', [TopupApiController::class, 'index']);
        Route::post('/', [TopupApiController::class, 'store']); // From Flutter
        Route::get('/{id}', [TopupApiController::class, 'show']);
        Route::post('/scan', [TopupApiController::class, 'verifikasiQR']);
        Route::get('/histori/all', [TopupApiController::class, 'histori']);
        
        // Admin only endpoints
        Route::middleware(['isadmin'])->group(function () {
            Route::patch('/{id}/confirm', [TopupApiController::class, 'confirm']);
            Route::patch('/{id}/reject', [TopupApiController::class, 'reject']);
            Route::post('/manual', [TopupApiController::class, 'topupManual']);
        });
    });

    // ✅ FIXED: NOTIFIKASI ENDPOINTS
    Route::prefix('notifikasi')->group(function () {
        Route::get('/', [NotifikasiApiController::class, 'index']);
        
        // Admin only
        Route::middleware(['isadmin'])->group(function () {
            Route::post('/', [NotifikasiApiController::class, 'kirim']);
        });
    });

    // ✅ FIXED: ADMIN ONLY ENDPOINTS
    Route::middleware(['isadmin'])->prefix('admin')->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [DashboardApiController::class, 'index']);
        Route::get('/dashboard/stats', [DashboardApiController::class, 'stats']);
        
        // User management (for admin users)
        Route::get('/users', [UserApiController::class, 'index']);
        
        // Laporan
        Route::prefix('laporan')->group(function () {
            Route::get('/rekap-transaksi', [LaporanApiController::class, 'rekapTransaksi']);
            Route::get('/ringkasan', [LaporanApiController::class, 'ringkasan']);
            Route::post('/periode', [LaporanApiController::class, 'laporanPeriode']);
            Route::post('/export', [LaporanApiController::class, 'export']);
        });
    });
});

// =======================
// LEGACY USER ROUTES (Keep for admin web interface)
// =======================
Route::prefix('v1')->group(function () {
    // Admin user authentication (for web admin)
    Route::post('/login', [AuthApiController::class, 'login']);
    Route::post('/register', [AuthApiController::class, 'register']);
    
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/logout', [AuthApiController::class, 'logout']);
        Route::get('/user/me', [AuthApiController::class, 'me']);
    });
});

// =======================
// API FALLBACK
// =======================
Route::fallback(function () {
    return response()->json([
        'status' => false,
        'message' => 'API endpoint tidak ditemukan',
        'available_versions' => ['v1']
    ], 404);
});