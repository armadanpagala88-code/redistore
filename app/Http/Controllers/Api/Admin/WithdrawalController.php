<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Withdrawal;
use App\Models\MutasiSaldo;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
{
    // Ambil daftar penarikan dana
    public function index()
    {
        $withdrawals = Withdrawal::with('user:id,username,nama_lengkap,email')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $withdrawals
        ]);
    }

    // Proses penarikan dana (Approve / Reject)
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Approved,Rejected',
            'catatan_admin' => 'nullable|string'
        ]);

        $withdrawal = Withdrawal::findOrFail($id);

        if ($withdrawal->status !== 'Pending') {
            return response()->json([
                'success' => false,
                'message' => 'Status penarikan ini sudah diproses dan tidak dapat diubah lagi.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $withdrawal->status = $request->status;
            $withdrawal->catatan_admin = $request->catatan_admin;
            $withdrawal->save();

            if ($request->status === 'Rejected') {
                // Kembalikan saldo ke user
                $user = User::find($withdrawal->user_id);
                $user->saldo += $withdrawal->nominal;
                $user->save();

                // Catat mutasi pengembalian
                MutasiSaldo::create([
                    'user_id' => $user->id,
                    'jenis' => 'Masuk',
                    'nominal' => $withdrawal->nominal,
                    'saldo_akhir' => $user->saldo,
                    'keterangan' => 'Pengembalian Dana (Tarik Saldo Ditolak)'
                ]);
            }

            // Jika Approved, tidak perlu kembalikan saldo karena sudah dikurangi saat pengajuan
            // Uangnya ditransfer secara manual oleh Admin ke rekening Member.

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Status penarikan dana berhasil diperbarui',
                'data' => $withdrawal
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    public function stats()
    {
        $pending = Withdrawal::where('status', 'Pending')->count();
        
        return response()->json([
            'success' => true,
            'data' => [
                'pending' => $pending
            ]
        ]);
    }
}
