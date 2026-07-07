<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AkunGame;

class AkunGameController extends Controller
{
    public function index()
    {
        $akuns = AkunGame::with(['kategori', 'penjual'])->latest()->get();
            
        return response()->json([
            'success' => true,
            'data' => $akuns
        ]);
    }

    public function show($id)
    {
        $akun = AkunGame::with(['kategori', 'penjual'])->findOrFail($id);
            
        return response()->json([
            'success' => true,
            'data' => $akun
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Pending,Tersedia,Ditolak',
        ]);

        $akun = AkunGame::findOrFail($id);
        
        if ($akun->status === 'Terjual') {
            return response()->json(['success' => false, 'message' => 'Akun sudah terjual tidak dapat diubah statusnya'], 400);
        }

        $akun->status = $request->status;
        $akun->save();

        return response()->json([
            'success' => true,
            'message' => 'Status akun berhasil diperbarui',
            'data' => $akun
        ]);
    }

    public function update(Request $request, $id)
    {
        $akun = AkunGame::findOrFail($id);

        if ($akun->status === 'Terjual') {
            return response()->json(['success' => false, 'message' => 'Akun yang sudah terjual tidak dapat diubah'], 400);
        }

        $request->validate([
            'judul_akun' => 'required|string|max:255',
            'harga' => 'required|numeric|min:1000',
            'login_via' => 'required|string',
            'gambar_utama' => 'nullable|image|max:2048'
        ]);

        $data = $request->only(['judul_akun', 'harga', 'login_via']);

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
            'message' => 'Detail akun berhasil diperbarui oleh Admin',
            'data' => $akun
        ]);
    }

    public function destroy($id)
    {
        $akun = AkunGame::findOrFail($id);
        
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
