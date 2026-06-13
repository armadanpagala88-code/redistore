<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Artikel;
use Illuminate\Support\Str;

class ArtikelController extends Controller
{
    public function index()
    {
        $artikels = Artikel::orderBy('created_at', 'desc')->get();
        return response()->json([
            'success' => true,
            'data' => $artikels
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
            'is_published' => 'boolean',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $slug = Str::slug($request->judul);
        $count = Artikel::where('slug', $slug)->count();
        if ($count > 0) {
            $slug = $slug . '-' . time();
        }

        $gambarPath = null;
        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/artikel'), $filename);
            $gambarPath = '/uploads/artikel/' . $filename;
        }

        $artikel = Artikel::create([
            'judul' => $request->judul,
            'slug' => $slug,
            'konten' => $request->konten,
            'gambar' => $gambarPath,
            'is_published' => $request->boolean('is_published', true)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Artikel berhasil dibuat',
            'data' => $artikel
        ]);
    }

    public function show($id)
    {
        $artikel = Artikel::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $artikel
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
            'is_published' => 'boolean',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $artikel = Artikel::findOrFail($id);
        
        $slug = Str::slug($request->judul);
        if ($slug !== $artikel->slug) {
            $count = Artikel::where('slug', $slug)->where('id', '!=', $id)->count();
            if ($count > 0) {
                $slug = $slug . '-' . time();
            }
            $artikel->slug = $slug;
        }

        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/artikel'), $filename);
            
            // Delete old file if exists
            if ($artikel->gambar && file_exists(public_path($artikel->gambar))) {
                @unlink(public_path($artikel->gambar));
            }
            
            $artikel->gambar = '/uploads/artikel/' . $filename;
        }

        $artikel->judul = $request->judul;
        $artikel->konten = $request->konten;
        if ($request->has('is_published')) {
            $artikel->is_published = $request->boolean('is_published');
        }
        $artikel->save();

        return response()->json([
            'success' => true,
            'message' => 'Artikel berhasil diupdate',
            'data' => $artikel
        ]);
    }

    public function destroy($id)
    {
        $artikel = Artikel::findOrFail($id);
        
        // Hapus file gambar
        if ($artikel->gambar && file_exists(public_path($artikel->gambar))) {
            @unlink(public_path($artikel->gambar));
        }

        $artikel->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Artikel berhasil dihapus'
        ]);
    }
}
