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
            'gambar_utama' => 'required',
            'gambar_utama.*' => 'image|max:2048'
        ]);

        $data = $request->except('gambar_utama');
        $data['user_id'] = auth()->id();
        $data['status'] = 'Tersedia';

        if ($request->hasFile('gambar_utama')) {
            $files = $request->file('gambar_utama');
            if (!is_array($files)) {
                $files = [$files];
            }
            
            $filenames = [];
            foreach (array_slice($files, 0, 3) as $file) {
                $filename = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                $file->move(storage_path('app/public/akun'), $filename);
                $filenames[] = $filename;
            }
            
            $data['gambar_utama'] = $filenames[0];
            if (count($filenames) > 1) {
                $data['gambar_lainnya'] = array_slice($filenames, 1);
            }
        }

        $akun = AkunGame::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Akun berhasil diposting dan langsung tersedia di marketplace',
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
            'gambar_utama' => 'nullable',
            'gambar_utama.*' => 'image|max:2048'
        ]);

        $data = $request->except(['gambar_utama', '_method']);

        if ($request->hasFile('gambar_utama')) {
            $files = $request->file('gambar_utama');
            if (!is_array($files)) {
                $files = [$files];
            }
            
            $filenames = [];
            foreach (array_slice($files, 0, 3) as $file) {
                $filename = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                $file->move(storage_path('app/public/akun'), $filename);
                $filenames[] = $filename;
            }
            
            // Delete old images if exist
            if ($akun->gambar_utama && file_exists(storage_path('app/public/akun/' . $akun->gambar_utama))) {
                @unlink(storage_path('app/public/akun/' . $akun->gambar_utama));
            } else if ($akun->gambar_utama && file_exists(public_path('images/akun/' . $akun->gambar_utama))) {
                @unlink(public_path('images/akun/' . $akun->gambar_utama));
            }
            if ($akun->gambar_lainnya) {
                foreach ($akun->gambar_lainnya as $oldGambar) {
                    if (file_exists(storage_path('app/public/akun/' . $oldGambar))) {
                        @unlink(storage_path('app/public/akun/' . $oldGambar));
                    } else if (file_exists(public_path('images/akun/' . $oldGambar))) {
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
