<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DisasterReportController;
use App\Http\Controllers\AidRequestController;
use App\Http\Controllers\DistributionController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\AidItemController;
use App\Http\Controllers\AidStockController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DisasterCategoryController;

/*
|--------------------------------------------------------------------------
| HALAMAN UTAMA
|--------------------------------------------------------------------------
*/

// Landing page: angka ringkasan diambil dari database lewat HomeController
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/home', function () {
    return redirect()->route('dashboard');
})->middleware('auth');

// REGISTRASI MASYARAKAT
Route::get('/register', [RegisterController::class, 'show'])
    ->middleware('guest')
    ->name('register');

Route::post('/register', [RegisterController::class, 'store'])
    ->middleware('guest')
    ->name('register.store');


/*
|--------------------------------------------------------------------------
| LOGIN
|--------------------------------------------------------------------------
*/

Route::get('/login', [LoginController::class, 'show'])
    ->middleware('guest')
    ->name('login');

Route::post('/login', [LoginController::class, 'login'])
    ->middleware('guest')
    ->name('login.process');


/*
|--------------------------------------------------------------------------
| LOGOUT
|--------------------------------------------------------------------------
*/

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');


/*
|--------------------------------------------------------------------------
| SEMUA PENGGUNA YANG SUDAH LOGIN
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Laporan bencana
    Route::resource('reports', DisasterReportController::class)
        ->only(['index', 'create', 'store', 'show']);

    // Permintaan bantuan
    Route::resource('aid-requests', AidRequestController::class)
        ->only(['index', 'create', 'store', 'show']);

    // Notifikasi
    Route::patch(
        '/notifications/{notification}/read',
        [NotificationController::class, 'markAsRead']
    )->name('notifications.read');

    // Distribusi: daftar
    Route::get('/distributions', [DistributionController::class, 'index'])
        ->name('distributions.index');

    // Distribusi: halaman tambah harus sebelum route detail
    Route::get('/distributions/create', [DistributionController::class, 'create'])
        ->middleware('role:admin,petugas')
        ->name('distributions.create');

    // Distribusi: simpan
    Route::post('/distributions', [DistributionController::class, 'store'])
        ->middleware('role:admin,petugas')
        ->name('distributions.store');

    // Distribusi: detail dengan ID numerik
    Route::get('/distributions/{distribution}', [DistributionController::class, 'show'])
        ->whereNumber('distribution')
        ->name('distributions.show');
});


/*
|--------------------------------------------------------------------------
| KHUSUS ADMIN DAN PETUGAS
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin,petugas'])->group(function () {

    // Peta
    Route::get('/map', [MapController::class, 'index'])
        ->name('map');

    // Verifikasi laporan
    Route::patch(
        '/reports/{report}/status',
        [DisasterReportController::class, 'updateStatus']
    )->name('reports.update-status');

    // Verifikasi permintaan bantuan
    Route::patch(
        '/aid-requests/{aid_request}/status',
        [AidRequestController::class, 'updateStatus']
    )->name('aid-requests.update-status');

    // Gudang
    Route::resource('warehouses', WarehouseController::class);

    // Barang bantuan
    Route::resource('aid-items', AidItemController::class);

    // Stok bantuan
    Route::resource('aid-stocks', AidStockController::class);

    // Perubahan status distribusi
    Route::patch(
        '/distributions/{distribution}/status',
        [DistributionController::class, 'updateStatus']
    )
        ->whereNumber('distribution')
        ->name('distributions.update-status');
});

/*
|--------------------------------------------------------------------------
| KHUSUS ADMIN
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->group(function () {

    // Kelola kategori bencana
    Route::resource('disaster-categories', DisasterCategoryController::class)
        ->except(['show']);
});

Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])
        ->name('password.request');

    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])
        ->name('password.email');

    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])
        ->name('password.reset');

    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])
        ->name('password.update');
});