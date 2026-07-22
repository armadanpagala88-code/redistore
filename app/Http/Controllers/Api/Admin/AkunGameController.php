<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AkunGame;

class AkunGameController extends Controller
{
    public function index(Request $request)
    {
        $query = AkunGame::with(['kategori', 'penjual'])->latest();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('judul_akun', 'like', "%{$search}%")
                  ->orWhere('login_via', 'like', "%{$search}%")
                  ->orWhereHas('penjual', function ($q2) use ($search) {
                      $q2->where('nama_lengkap', 'like', "%{$search}%")
                         ->orWhere('username', 'like', "%{$search}%");
                  })
                  ->orWhereHas('kategori', function ($q2) use ($search) {
                      $q2->where('nama_game', 'like', "%{$search}%");
                  });
            });
        }

        $akuns = $query->paginate(15);
            
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
            'gambar_utama' => 'nullable',
            'gambar_utama.*' => 'image|max:2048'
        ]);

        $data = $request->only(['judul_akun', 'harga', 'login_via']);

        if ($request->hasFile('gambar_utama')) {
            $files = $request->file('gambar_utama');
            if (!is_array($files)) {
                $files = [$files];
            }
            
            $filenames = [];
            foreach (array_slice($files, 0, 3) as $file) {
                $filename = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                $file->move(public_path('images/akun'), $filename);
                $filenames[] = $filename;
            }
            
            // Delete old images if exist
            if ($akun->gambar_utama && file_exists(public_path('images/akun/' . $akun->gambar_utama))) {
                @unlink(public_path('images/akun/' . $akun->gambar_utama));
            }
            if ($akun->gambar_lainnya) {
                foreach ($akun->gambar_lainnya as $oldGambar) {
                    if (file_exists(public_path('images/akun/' . $oldGambar))) {
                        @unlink(public_path('images/akun/' . $oldGambar));
                    }
                }
            }
            
            $data['gambar_utama'] = $filenames[0];
            if (count($filenames) > 1) {
                $data['gambar_lainnya'] = array_slice($filenames, 1);
            } else {
                $data['gambar_lainnya'] = null;
            }
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
