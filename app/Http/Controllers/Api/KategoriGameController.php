<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KategoriGame;
use Illuminate\Http\Request;

class KategoriGameController extends Controller
{
    public function index()
    {
        $kategori = KategoriGame::where('is_aktif', true)->get();
        return response()->json([
            'success' => true,
            'data' => $kategori
        ]);
    }

    public function show($slug)
    {
        $kategori = KategoriGame::with(['produks' => function($q) {
            $q->where('is_aktif', true)->orderBy('harga_jual', 'asc');
        }])->where('slug', $slug)->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $kategori
        ]);
    }
}
