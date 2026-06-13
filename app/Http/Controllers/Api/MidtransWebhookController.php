<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Services\FonnteService;
use Midtrans\Config;
use Midtrans\Notification;

class MidtransWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Set configuration
        Config::$serverKey = env('MIDTRANS_SERVER_KEY', 'SB-Mid-server-YOUR-KEY-HERE');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);

        try {
            $notification = new Notification();
            
            $transactionStatus = $notification->transaction_status;
            $orderId = $notification->order_id;
            $fraudStatus = $notification->fraud_status;

            $transaksi = Transaksi::find($orderId);
            
            if (!$transaksi) {
                return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
            }

            $success = false;

            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'challenge') {
                    $transaksi->status_transaksi = 'Pending';
                } else if ($fraudStatus == 'accept') {
                    $transaksi->status_transaksi = 'Success';
                    $success = true;
                }
            } else if ($transactionStatus == 'settlement') {
                $transaksi->status_transaksi = 'Success';
                $success = true;
            } else if ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
                $transaksi->status_transaksi = 'Failed';
            } else if ($transactionStatus == 'pending') {
                $transaksi->status_transaksi = 'Pending';
            }

            $transaksi->save();

            if ($success) {
                // Panggil API Digiflazz untuk memproses top up secara otomatis
                $digiflazz = new \App\Services\DigiflazzService();
                $digiflazz->createTransaction($transaksi);

                // Tambahkan Poin Loyalitas jika pembeli adalah Member
                if ($transaksi->user_id) {
                    $user = \App\Models\User::find($transaksi->user_id);
                    if ($user) {
                        // 1 Poin untuk setiap Rp 1.000
                        $pointsEarned = floor($transaksi->total_bayar / 1000);
                        $user->poin += $pointsEarned;
                        $user->save();
                    }
                }

                // Kirim Email Struk
                if ($transaksi->email_pembeli) {
                    \Illuminate\Support\Facades\Mail::to($transaksi->email_pembeli)->send(new \App\Mail\InvoiceMail($transaksi));
                }

                $msg = "Hore! Pembayaran untuk pesanan *$orderId* telah BERHASIL kami terima.\n\nSistem sedang memproses pesanan Anda secara otomatis. Terima kasih telah berbelanja di Redistore!";
                FonnteService::sendMessage($transaksi->no_whatsapp, $msg);
            }

            return response()->json(['message' => 'Webhook berhasil diproses']);
        } catch (\Exception $e) {
            \Log::error('Midtrans Webhook Error: ' . $e->getMessage());
            return response()->json(['message' => 'Error processing webhook'], 500);
        }
    }
}
