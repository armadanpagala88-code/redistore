<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    /**
     * Mengirim pesan WhatsApp melalui API Fonnte
     *
     * @param string $target Nomor WhatsApp tujuan (contoh: '08123456789')
     * @param string $message Isi pesan
     * @return bool
     */
    public static function sendMessage($target, $message)
    {
        $token = env('FONNTE_TOKEN');

        if (empty($token) || $token === 'your_fonnte_token_here') {
            Log::warning('Fonnte token tidak diset. Pesan tidak dikirim: ' . $message);
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->post('https://api.fonnte.com/send', [
                'target' => $target,
                'message' => $message,
                'countryCode' => '62', // Default Indonesia
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('Gagal mengirim WhatsApp via Fonnte: ' . $response->body());
            return false;
            
        } catch (\Exception $e) {
            Log::error('Fonnte API Exception: ' . $e->getMessage());
            return false;
        }
    }
}
