<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Artikel;

class ArtikelController extends Controller
{
    public function index()
    {
        $artikels = Artikel::where('is_published', true)->orderBy('created_at', 'desc')->get();
        return response()->json([
            'success' => true,
            'data' => $artikels
        ]);
    }

    public function show($slug)
    {
        $artikel = Artikel::where('slug', $slug)->where('is_published', true)->firstOrFail();
        
        // Tambah views
        $artikel->views += 1;
        $artikel->save();

        return response()->json([
            'success' => true,
            'data' => $artikel
        ]);
    }
}
