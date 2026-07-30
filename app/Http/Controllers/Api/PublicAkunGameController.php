<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AkunGame;

class PublicAkunGameController extends Controller
{
    public function index(Request $request)
    {
        $query = AkunGame::with('kategori')
            ->where('status', 'Tersedia');

        // Filter by kategori slug
        if ($request->filled('kategori_slug')) {
            $query->whereHas('kategori', function ($q) use ($request) {
                $q->where('slug', $request->kategori_slug);
            });
        }

        // Filter by search keyword (judul_akun)
        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('judul_akun', 'like', "%{$keyword}%")
                  ->orWhereHas('kategori', function ($q2) use ($keyword) {
                      $q2->where('nama_game', 'like', "%{$keyword}%");
                  });
            });
        }

        // Sorting
        $sort = $request->get('sort', 'terbaru');
        match ($sort) {
            'harga_asc'  => $query->orderBy('harga', 'asc'),
            'harga_desc' => $query->orderBy('harga', 'desc'),
            default      => $query->latest(),
        };

        if ($request->has('limit')) {
            $limit = (int) $request->limit;
            $akuns = $query->limit($limit)->get();
        } else {
            // Jika dipanggil tanpa parameter limit (misal dari halaman Katalog), gunakan pagination
            $perPage = (int) $request->get('per_page', 16);
            $akuns = $query->paginate($perPage);
        }

        return response()->json([
            'success' => true,
            'data' => $akuns
        ]);
    }

    public function show($id)
    {
        $akun = AkunGame::with(['kategori', 'penjual:id,nama_lengkap,username'])->findOrFail($id);

        // Jangan kembalikan email_akun dan password_akun jika belum lunas!
        // Di public view, hide creds.
        $akun->makeHidden(['email_akun', 'password_akun']);

        return response()->json([
            'success' => true,
            'data' => $akun
        ]);
    }
}
