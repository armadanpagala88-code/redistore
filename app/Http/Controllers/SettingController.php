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
            try {
                $file = $request->file('logo');
                
                // Ensure directory exists in uploads (which is a persistent volume)
                if (!file_exists(storage_path('app/public/settings'))) {
                    @mkdir(storage_path('app/public/settings'), 0755, true);
                }

                $file->move(storage_path('app/public/settings'), 'logo.png');
                $data['logo'] = 'settings/logo.png'; // Will be saved in loop below if we don't unset it, wait, loop skips 'logo'. We must handle it.
                Setting::updateOrCreate(['key' => 'logo'], ['value' => 'settings/logo.png']);
            } catch (\Exception $e) {
                \Log::error('Gagal upload logo: ' . $e->getMessage());
                // Lanjut menyimpan pengaturan lain meskipun logo gagal
            }
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
