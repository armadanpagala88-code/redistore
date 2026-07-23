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

        if ($request->has('kategori_slug')) {
            $query->whereHas('kategori', function ($q) use ($request) {
                $q->where('slug', $request->kategori_slug);
            });
        }

        if ($request->has('limit')) {
            $limit = (int) $request->limit;
            $akuns = $query->latest()->limit($limit)->get();
        } else {
            // Jika dipanggil tanpa parameter limit (misal dari halaman Katalog), gunakan pagination
            $akuns = $query->latest()->paginate(16);
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
