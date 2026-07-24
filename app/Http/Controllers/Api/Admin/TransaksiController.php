<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Services\FonnteService;

class TransaksiController extends Controller
{
    public function index()
    {
        // Mengambil semua transaksi urut terbaru
        $transaksis = Transaksi::with(['details', 'user'])->orderBy('created_at', 'desc')->get();
        return response()->json([
            'success' => true,
            'data' => $transaksis
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Unpaid,Pending,Success,Failed,Cancel,Refund'
        ]);

        $transaksi = Transaksi::with('details')->findOrFail($id);
        $transaksi->status_transaksi = $request->status;
        $transaksi->save();

        // Kirim Notifikasi jika Success atau Failed
        if ($request->status === 'Success') {
            if ($transaksi->tipe_transaksi === 'BeliAkun' && $transaksi->akun_game_id) {
                $akun = \App\Models\AkunGame::find($transaksi->akun_game_id);
                if ($akun) {
                    $msg = "Horee! Pembayaran pesanan *$id* telah *BERHASIL* diverifikasi.\n\nBerikut adalah data login akun game Anda:\n\nEmail/Username: *" . $akun->email_akun . "*\nPassword: *" . $akun->password_akun . "*\nLogin Via: *" . $akun->login_via . "*\n\nCatatan Penjual: " . ($akun->catatan_akun ? $akun->catatan_akun : "-") . "\n\nTerima kasih telah berbelanja di Redistore! Harap segera amankan akun Anda.";
                    FonnteService::sendMessage($transaksi->no_whatsapp, $msg);
                }
            } else {
                $msg = "Horee! Pembayaran pesanan *$id* telah *BERHASIL* diverifikasi.\n\nPesanan top up game Anda telah kami kirim. Terima kasih telah berbelanja di Redistore!";
                FonnteService::sendMessage($transaksi->no_whatsapp, $msg);
            }
        } else if ($request->status === 'Failed') {
            $msg = "Mohon maaf, pembayaran untuk pesanan *$id* *GAGAL* diverifikasi.\n\nSilakan periksa kembali bukti pembayaran Anda atau hubungi Admin kami.";
            FonnteService::sendMessage($transaksi->no_whatsapp, $msg);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status transaksi berhasil diperbarui',
            'data' => $transaksi
        ]);
    }

    public function showBukti($filename)
    {
        $path = storage_path('app/public/bukti/' . $filename);
        if (!file_exists($path)) {
            // Check old path fallback just in case
            $oldPath = public_path('uploads/bukti/' . $filename);
            if (file_exists($oldPath)) {
                return response()->file($oldPath);
            }
            return response()->json(['message' => 'File not found.'], 404);
        }
        return response()->file($path);
    }

    public function stats()
    {
        $total = Transaksi::count();
        $success = Transaksi::where('status_transaksi', 'Success')->count();
        $pending = Transaksi::where('status_transaksi', 'Pending')->count();
        $unpaid = Transaksi::where('status_transaksi', 'Unpaid')->count();
        $failed = Transaksi::whereIn('status_transaksi', ['Failed', 'Cancel', 'Refund'])->count();
        
        return response()->json([
            'success' => true,
            'data' => [
                'total' => $total,
                'success' => $success,
                'pending' => $pending,
                'unpaid' => $unpaid,
                'failed' => $failed
            ]
        ]);
    }
}
