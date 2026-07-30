<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ImageUploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120' // 5MB max
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            
            // Simpan ke direktori public/img/uploads atau storage
            // Saya akan menggunakan public_path() agar konsisten dengan rute /img/
            $file->move(public_path('img/uploads'), $filename);
            
            return response()->json([
                'success' => true,
                'url' => '/img/uploads/' . $filename
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal mengunggah gambar'
        ], 400);
    }
}
