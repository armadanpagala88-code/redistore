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
