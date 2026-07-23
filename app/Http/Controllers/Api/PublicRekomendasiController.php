<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AkunGame;
use Illuminate\Http\Request;

class PublicRekomendasiController extends Controller
{
    public function index()
    {
        // Option A: Automatically recommend available AkunGame randomly
        $rekomendasi = AkunGame::with(['kategori', 'penjual'])
            ->where('status', 'Tersedia')
            ->inRandomOrder()
            ->take(4) // 4 items for a nice row
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $rekomendasi
        ]);
    }
}
