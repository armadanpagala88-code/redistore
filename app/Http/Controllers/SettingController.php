<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Get all settings as key-value pairs
     */
    public function index()
    {
        $settings = Setting::whereNotIn('key', ['fonnte_token', 'midtrans_server_key'])->pluck('value', 'key');
        // We will allow midtrans_client_key to be public as it is needed for frontend
        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }

    /**
     * Get ALL settings for admin panel
     */
    public function adminIndex()
    {
        $settings = Setting::all()->pluck('value', 'key');
        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }

    /**
     * Update settings (Bulk)
     */
    public function update(Request $request)
    {
        $data = $request->all();

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $file->move(public_path('images'), 'logo.png');
            @copy(public_path('images/logo.png'), resource_path('images/logo.png'));
        }

        foreach ($data as $key => $value) {
            if ($key === 'logo') continue;
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan berhasil disimpan'
        ]);
    }
}
