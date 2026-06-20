<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FlashSale;

class PublicFlashSaleController extends Controller
{
    public function active()
    {
        $now = now();
        
        $flashSales = FlashSale::with(['items.produk.kategori'])
            ->where('is_active', true)
            ->where('waktu_mulai', '<=', $now)
            ->where('waktu_selesai', '>=', $now)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $flashSales
        ]);
    }
}
