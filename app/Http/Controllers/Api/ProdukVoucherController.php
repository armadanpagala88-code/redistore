<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProdukVoucher;
use Illuminate\Http\Request;

class ProdukVoucherController extends Controller
{
    public function index()
    {
        $produks = ProdukVoucher::with('kategori')->where('is_aktif', true)->get();
        return response()->json([
            'success' => true,
            'data' => $produks
        ]);
    }

    public function show($id)
    {
        $produk = ProdukVoucher::with('kategori')->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $produk
        ]);
    }
}
