<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ulasan;
use Illuminate\Http\Request;

class PublicUlasanController extends Controller
{
    public function getByKategori($kategoriId)
    {
        $ulasan = Ulasan::with(['user', 'produkVoucher'])
            ->whereHas('produkVoucher', function($q) use ($kategoriId) {
                $q->where('kategori_game_id', $kategoriId);
            })
            ->where('is_aktif', true)
            ->latest()
            ->take(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $ulasan
        ]);
    }
}
