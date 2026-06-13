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

        foreach ($data as $key => $value) {
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
