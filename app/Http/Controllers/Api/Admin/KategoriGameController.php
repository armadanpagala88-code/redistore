<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KategoriGame;
use Illuminate\Support\Str;

class KategoriGameController extends Controller
{
    public function index()
    {
        $kategori = KategoriGame::orderBy('created_at', 'desc')->get();
        return response()->json([
            'success' => true,
            'data' => $kategori
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_game' => 'required|string',
            'deskripsi' => 'nullable|string',
            'gambar_logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'is_aktif' => 'boolean'
        ]);

        $data = $request->except('gambar_logo');
        $data['slug'] = Str::slug($request->nama_game);
        
        if ($request->hasFile('gambar_logo')) {
            $file = $request->file('gambar_logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images'), $filename);
            $data['gambar_logo'] = $filename;
        }

        $kategori = KategoriGame::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Game berhasil ditambahkan',
            'data' => $kategori
        ]);
    }

    public function show($id)
    {
        $kategori = KategoriGame::findOrFail($id);
        return response()->json(['success' => true, 'data' => $kategori]);
    }

    public function update(Request $request, $id)
    {
        $kategori = KategoriGame::findOrFail($id);

        $request->validate([
            'nama_game' => 'required|string',
            'deskripsi' => 'nullable|string',
            'is_aktif' => 'boolean'
        ]);

        $data = $request->except('gambar_logo');
        $data['slug'] = Str::slug($request->nama_game);

        if ($request->hasFile('gambar_logo')) {
            $file = $request->file('gambar_logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images'), $filename);
            $data['gambar_logo'] = $filename;
        }

        $kategori->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Game berhasil diperbarui',
            'data' => $kategori
        ]);
    }

    public function destroy($id)
    {
        $kategori = KategoriGame::findOrFail($id);
        $kategori->delete();

        return response()->json([
            'success' => true,
            'message' => 'Game berhasil dihapus'
        ]);
    }
}
