<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KategoriGame;
use Illuminate\Http\Request;

class KategoriGameController extends Controller
{
    public function index()
    {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $output = \Illuminate\Support\Facades\Artisan::output();
        
        return response()->json([
            'success' => true,
            'message' => 'Migrations executed successfully',
            'output' => $output
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

    public function destroy($id)
    {
        $kategori = KategoriGame::findOrFail($id);
        
        if ($kategori->gambar_logo) {
            $path = public_path('images/' . $kategori->gambar_logo);
            if (file_exists($path)) {
                unlink($path);
            }
        }
        
        $kategori->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Kategori game berhasil dihapus'
        ]);
    }

    public function runMigrations()
    {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        return \Illuminate\Support\Facades\Artisan::output();
    }
}
