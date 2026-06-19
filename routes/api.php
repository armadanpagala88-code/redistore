<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\KategoriGameController;
use App\Http\Controllers\Api\ProdukVoucherController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/kategori-game', [KategoriGameController::class, 'index']);
Route::get('/kategori-game/{slug}', [KategoriGameController::class, 'show']);
Route::get('/produk-voucher', [ProdukVoucherController::class, 'index']);
Route::get('/produk-voucher/{id}', [ProdukVoucherController::class, 'show']);
Route::get('/banners', [App\Http\Controllers\Api\BannerController::class, 'index']);

// Checkout Transaksi
Route::post('/checkout', [App\Http\Controllers\Api\CheckoutController::class, 'store']);
Route::get('/checkout/{id}', [App\Http\Controllers\Api\CheckoutController::class, 'show']);
Route::post('/checkout/{id}/upload-bukti', [App\Http\Controllers\Api\CheckoutController::class, 'uploadBukti']);
Route::post('/promo/check', [App\Http\Controllers\Api\CheckoutController::class, 'checkPromo']);

// Midtrans Webhook
Route::post('/midtrans/webhook', [App\Http\Controllers\Api\MidtransWebhookController::class, 'handle']);

// Digiflazz Webhook
Route::post('/digiflazz/webhook', [App\Http\Controllers\Api\DigiflazzWebhookController::class, 'handle']);

// Autentikasi
Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login']);
Route::post('/register', [App\Http\Controllers\Api\AuthController::class, 'register']);
Route::get('/me', [App\Http\Controllers\Api\AuthController::class, 'me'])->middleware('auth:sanctum');

// Rute Admin & Member (Terlindungi)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [App\Http\Controllers\Api\AuthController::class, 'logout']);
    
    // Member Routes
    Route::get('/member/dashboard', [App\Http\Controllers\Api\MemberController::class, 'dashboard']);
    Route::post('/member/redeem-points', [App\Http\Controllers\Api\MemberController::class, 'redeemPoints']);
    Route::post('/member/update-photo', [App\Http\Controllers\Api\MemberController::class, 'updatePhoto']);
    
    // Dasbor & Laporan
    Route::get('/admin/dashboard-stats', [App\Http\Controllers\Api\Admin\DashboardController::class, 'stats']);
    Route::get('/admin/laporan', [App\Http\Controllers\Api\Admin\LaporanController::class, 'index']);

    // Transaksi
    Route::get('/admin/transaksi', [App\Http\Controllers\Api\Admin\TransaksiController::class, 'index']);
    Route::put('/admin/transaksi/{id}/status', [App\Http\Controllers\Api\Admin\TransaksiController::class, 'updateStatus']);
    
    // Kategori Game
    Route::apiResource('/admin/kategori', App\Http\Controllers\Api\Admin\KategoriGameController::class);
    
    // Produk Voucher
    Route::apiResource('/admin/produk', App\Http\Controllers\Api\Admin\ProdukVoucherController::class);
    Route::post('/admin/digiflazz/sync', [App\Http\Controllers\Api\Admin\DigiflazzSyncController::class, 'sync']);
    
    // Banner / Iklan
    Route::apiResource('/admin/banner', App\Http\Controllers\Api\Admin\BannerController::class);
    
    // Artikel
    Route::apiResource('admin/artikel', \App\Http\Controllers\Api\Admin\ArtikelController::class);
    Route::apiResource('admin/users', \App\Http\Controllers\Api\Admin\UserController::class);
    
    // Settings
    Route::post('/admin/settings', [App\Http\Controllers\SettingController::class, 'update']);
});

// Settings (Public)
Route::get('/settings', [App\Http\Controllers\SettingController::class, 'index']);

// Artikel (Public)
Route::get('/artikels', [App\Http\Controllers\Api\ArtikelController::class, 'index']);
Route::get('/artikels/{slug}', [App\Http\Controllers\Api\ArtikelController::class, 'show']);
