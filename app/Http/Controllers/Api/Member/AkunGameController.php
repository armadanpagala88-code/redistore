<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AkunGame;
use Illuminate\Support\Facades\Storage;

class AkunGameController extends Controller
{
    public function index()
    {
        $akuns = AkunGame::with('kategori')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $akuns
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kategori_game_id' => 'required|exists:kategori_games,id',
            'judul_akun' => 'required|string|max:255',
            'deskripsi_akun' => 'required|string',
            'harga' => 'required|numeric|min:1000',
            'login_via' => 'required|string',
            'email_akun' => 'required|string',
            'password_akun' => 'required|string',
            'catatan_akun' => 'nullable|string',
            'gambar_utama' => 'required|image|max:2048'
        ]);

        $data = $request->except('gambar_utama');
        $data['user_id'] = auth()->id();
        $data['status'] = 'Pending';

        if ($request->hasFile('gambar_utama')) {
            $file = $request->file('gambar_utama');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/akun'), $filename);
            $data['gambar_utama'] = $filename;
        }

        $akun = AkunGame::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Akun berhasil diposting dan menunggu persetujuan Admin',
            'data' => $akun
        ]);
    }

    public function show($id)
    {
        $akun = AkunGame::with('kategori')
            ->where('user_id', auth()->id())
            ->findOrFail($id);
            
        return response()->json([
            'success' => true,
            'data' => $akun
        ]);
    }

    public function update(Request $request, $id)
    {
        $akun = AkunGame::where('user_id', auth()->id())->findOrFail($id);

        if ($akun->status === 'Terjual') {
            return response()->json(['success' => false, 'message' => 'Akun yang sudah terjual tidak dapat diubah'], 400);
        }

        $request->validate([
            'kategori_game_id' => 'required|exists:kategori_games,id',
            'judul_akun' => 'required|string|max:255',
            'deskripsi_akun' => 'required|string',
            'harga' => 'required|numeric|min:1000',
            'login_via' => 'required|string',
            'email_akun' => 'required|string',
            'password_akun' => 'required|string',
            'catatan_akun' => 'nullable|string',
            'gambar_utama' => 'nullable|image|max:2048'
        ]);

        $data = $request->except(['gambar_utama', '_method']);

        if ($request->hasFile('gambar_utama')) {
            $file = $request->file('gambar_utama');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/akun'), $filename);
            
            // Delete old image if exists
            if ($akun->gambar_utama && file_exists(public_path('images/akun/' . $akun->gambar_utama))) {
                @unlink(public_path('images/akun/' . $akun->gambar_utama));
            }
            $data['gambar_utama'] = $filename;
        }

        $akun->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Akun berhasil diperbarui',
            'data' => $akun
        ]);
    }

    public function destroy($id)
    {
        $akun = AkunGame::where('user_id', auth()->id())->findOrFail($id);
        
        if ($akun->status === 'Terjual') {
            return response()->json(['success' => false, 'message' => 'Akun yang sudah terjual tidak dapat dihapus'], 400);
        }

        $akun->delete();

        return response()->json([
            'success' => true,
            'message' => 'Akun berhasil dihapus'
        ]);
    }
}
