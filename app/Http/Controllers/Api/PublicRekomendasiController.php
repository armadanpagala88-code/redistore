<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rekomendasi;
use Illuminate\Http\Request;

class PublicRekomendasiController extends Controller
{
    public function index()
    {
        $rekomendasi = Rekomendasi::with(['produkVoucher.kategoriGame', 'user'])
            ->orderBy('skor', 'desc')
            ->take(10)
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $rekomendasi
        ]);
    }
}
