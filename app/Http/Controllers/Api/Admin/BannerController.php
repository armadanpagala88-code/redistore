<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('created_at', 'desc')->get();
        return response()->json([
            'success' => true,
            'data' => $banners
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'gambar_banner' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'link_tujuan' => 'nullable|string',
            'is_aktif' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $imageName = time() . '_' . Str::slug($request->judul) . '.' . $request->gambar_banner->extension();
        $request->gambar_banner->move(public_path('images/banners'), $imageName);

        $banner = Banner::create([
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'gambar_banner' => 'banners/' . $imageName,
            'link_tujuan' => $request->link_tujuan,
            'is_aktif' => $request->is_aktif
        ]);

        return response()->json(['success' => true, 'data' => $banner]);
    }

    public function show(string $id)
    {
        $banner = Banner::findOrFail($id);
        return response()->json(['success' => true, 'data' => $banner]);
    }

    public function update(Request $request, string $id)
    {
        $banner = Banner::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'gambar_banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'link_tujuan' => 'nullable|string',
            'is_aktif' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $imagePath = $banner->gambar_banner;

        if ($request->hasFile('gambar_banner')) {
            // Hapus gambar lama
            if ($imagePath && File::exists(public_path('images/' . $imagePath))) {
                File::delete(public_path('images/' . $imagePath));
            }

            $imageName = time() . '_' . Str::slug($request->judul) . '.' . $request->gambar_banner->extension();
            $request->gambar_banner->move(public_path('images/banners'), $imageName);
            $imagePath = 'banners/' . $imageName;
        }

        $banner->update([
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'gambar_banner' => $imagePath,
            'link_tujuan' => $request->link_tujuan,
            'is_aktif' => $request->is_aktif
        ]);

        return response()->json(['success' => true, 'data' => $banner]);
    }

    public function destroy(string $id)
    {
        $banner = Banner::findOrFail($id);
        
        if ($banner->gambar_banner && File::exists(public_path('images/' . $banner->gambar_banner))) {
            File::delete(public_path('images/' . $banner->gambar_banner));
        }
        
        $banner->delete();

        return response()->json(['success' => true]);
    }
}
