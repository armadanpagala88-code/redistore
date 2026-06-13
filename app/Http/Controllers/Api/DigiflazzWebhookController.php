<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Services\FonnteService;

class DigiflazzWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $secret = env('DIGIFLAZZ_WEBHOOK_SECRET', 'YOUR_WEBHOOK_SECRET');
        $signature = $request->header('X-Hub-Signature');
        
        $payload = $request->getContent();
        $hash = 'sha1=' . hash_hmac('sha1', $payload, $secret);

        if (!hash_equals($hash, $signature)) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        try {
            $data = $request->json('data');
            if (!$data) {
                return response()->json(['message' => 'No data found'], 400);
            }

            $refId = $data['ref_id'];
            $status = $data['status']; // Sukses, Gagal, Pending
            $sn = $data['sn'] ?? null;
            $message = $data['message'] ?? null;

            $transaksi = Transaksi::find($refId);
            
            if (!$transaksi) {
                return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
            }

            // Update status provider
            $transaksi->status_provider = $status;
            $transaksi->pesan_provider = $message;
            if ($sn) {
                $transaksi->sn = $sn;
            }
            $transaksi->save();

            // Kirim notifikasi WA
            if ($status === 'Sukses') {
                $msg = "Top Up untuk pesanan *$refId* telah BERHASIL diproses oleh sistem!\n\nSN: *" . ($sn ?? '-') . "*\n\nTerima kasih telah berbelanja di Redistore!";
                FonnteService::sendMessage($transaksi->no_whatsapp, $msg);
            } else if ($status === 'Gagal') {
                $msg = "Mohon maaf, Top Up untuk pesanan *$refId* GAGAL diproses oleh server dengan alasan:\n_" . $message . "_\n\nSilakan hubungi Admin untuk pengembalian dana atau bantuan lebih lanjut.";
                FonnteService::sendMessage($transaksi->no_whatsapp, $msg);
            }

            return response()->json(['message' => 'Webhook berhasil diproses']);
        } catch (\Exception $e) {
            \Log::error('Digiflazz Webhook Error: ' . $e->getMessage());
            return response()->json(['message' => 'Error processing webhook'], 500);
        }
    }
}
