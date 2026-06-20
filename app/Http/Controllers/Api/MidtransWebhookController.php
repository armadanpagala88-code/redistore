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
        $settingServerKey = \App\Models\Setting::where('key', 'midtrans_server_key')->first();
        $settingIsProd = \App\Models\Setting::where('key', 'midtrans_is_production')->first();

        // Set configuration
        Config::$serverKey = $settingServerKey && !empty($settingServerKey->value) ? $settingServerKey->value : env('MIDTRANS_SERVER_KEY', 'SB-Mid-server-YOUR-KEY-HERE');
        Config::$isProduction = $settingIsProd && $settingIsProd->value === '1' ? true : env('MIDTRANS_IS_PRODUCTION', false);

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
                if ($transaksi->tipe_transaksi === 'TopUp') {
                    // Panggil API Digiflazz untuk memproses top up secara otomatis
                    $digiflazz = new \App\Services\DigiflazzService();
                    $digiflazz->createTransaction($transaksi);
                    
                    $msg = "Hore! Pembayaran untuk pesanan *$orderId* telah BERHASIL kami terima.\n\nSistem sedang memproses pesanan Anda secara otomatis. Terima kasih telah berbelanja di Redistore!";
                } else {
                    // Logika untuk Jual Beli Akun
                    $akunGame = \App\Models\AkunGame::find($transaksi->akun_game_id);
                    if ($akunGame) {
                        $akunGame->status = 'Terjual';
                        $akunGame->save();
                        
                        // Tambahkan saldo ke penjual (dipotong fee admin)
                        $penjual = \App\Models\User::find($akunGame->user_id);
                        if ($penjual) {
                            $setting = \App\Models\Setting::where('key', 'biaya_admin_persen')->first();
                            $feePercent = $setting ? (float)$setting->value : 5.0; // default 5%
                            
                            $feeAmount = ($feePercent / 100) * $transaksi->total_bayar;
                            $saldoDiterima = $transaksi->total_bayar - $feeAmount;
                            
                            $penjual->saldo += $saldoDiterima;
                            $penjual->save();
                        }
                    }
                    
                    $msg = "Pembayaran untuk pembelian Akun Game *$orderId* telah BERHASIL kami terima.\n\nAkun game akan segera diserahkan, silakan cek detail akun pada menu riwayat pesanan Anda!";
                }

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

                FonnteService::sendMessage($transaksi->no_whatsapp, $msg);
            }

            return response()->json(['message' => 'Webhook berhasil diproses']);
        } catch (\Exception $e) {
            \Log::error('Midtrans Webhook Error: ' . $e->getMessage());
            return response()->json(['message' => 'Error processing webhook'], 500);
        }
    }
}
