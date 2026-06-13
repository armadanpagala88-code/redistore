<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Transaksi;
use App\Services\FonnteService;

class DigiflazzService
{
    private $username;
    private $apiKey;
    private $baseUrl;

    public function __construct()
    {
        $this->username = env('DIGIFLAZZ_USERNAME', 'YOUR_USERNAME');
        $this->apiKey = env('DIGIFLAZZ_API_KEY', 'YOUR_API_KEY');
        // By default sandbox. For production: https://api.digiflazz.com/v1
        $this->baseUrl = env('DIGIFLAZZ_BASE_URL', 'https://api.digiflazz.com/v1');
    }

    private function generateSign($cmd)
    {
        return md5($this->username . $this->apiKey . $cmd);
    }

    /**
     * Mendapatkan daftar harga lengkap (Prepaid)
     */
    public function getPriceList()
    {
        $cmd = "prepaid"; // atau "pasca" untuk PPOB
        $sign = $this->generateSign($cmd);

        $payload = [
            'cmd' => $cmd,
            'username' => $this->username,
            'sign' => $sign,
        ];

        try {
            $response = Http::post($this->baseUrl . '/price-list', $payload);
            $result = $response->json();
            
            if (isset($result['data'])) {
                return $result['data'];
            }
            return [];
        } catch (\Exception $e) {
            \Log::error('Digiflazz Price List Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Membeli produk via API Digiflazz
     */
    public function createTransaction(Transaksi $transaksi)
    {
        if (!$transaksi || !$transaksi->details->first()) {
            return false;
        }

        $detail = $transaksi->details->first();
        // Asumsikan kode SKU digiflazz disimpan di field nominal atau kita perlu tambah field sku_digiflazz di produk_vouchers
        // Untuk saat ini kita asumsikan 'nominal' di produk voucher menyimpan SKU Digiflazz
        $sku = $detail->nama_produk; // e.g. "ML86"
        
        $customerNo = $transaksi->user_id_game;
        if ($transaksi->zone_id) {
            $customerNo .= $transaksi->zone_id;
        }

        $refId = $transaksi->id;
        $sign = $this->generateSign($refId);

        $payload = [
            'username' => $this->username,
            'buyer_sku_code' => $sku,
            'customer_no' => $customerNo,
            'ref_id' => $refId,
            'sign' => $sign,
        ];

        try {
            $response = Http::post($this->baseUrl . '/transaction', $payload);
            $result = $response->json();

            if (isset($result['data'])) {
                $data = $result['data'];
                $transaksi->status_provider = $data['status'];
                $transaksi->pesan_provider = $data['message'];
                
                if (isset($data['sn'])) {
                    $transaksi->sn = $data['sn'];
                }
                
                $transaksi->save();

                // Jika status Sukses dari Digiflazz langsung
                if ($data['status'] === 'Sukses') {
                    $msg = "Top Up untuk pesanan *$refId* telah BERHASIL diproses oleh sistem!\n\nSN: *" . ($data['sn'] ?? '-') . "*\n\nTerima kasih telah berbelanja di Redistore!";
                    FonnteService::sendMessage($transaksi->no_whatsapp, $msg);
                }

                return true;
            } else {
                \Log::error('Digiflazz Error Response: ' . json_encode($result));
                return false;
            }
        } catch (\Exception $e) {
            \Log::error('Digiflazz Exception: ' . $e->getMessage());
            return false;
        }
    }
}
