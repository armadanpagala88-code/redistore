<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Withdrawal;
use App\Models\MutasiSaldo;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
{
    // Mengambil data riwayat penarikan dan info bank user
    public function index()
    {
        $user = auth()->user();
        $withdrawals = Withdrawal::where('user_id', $user->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'bank_info' => [
                    'nama_bank' => $user->nama_bank,
                    'nomor_rekening' => $user->nomor_rekening,
                    'atas_nama' => $user->atas_nama,
                ],
                'saldo' => $user->saldo,
                'withdrawals' => $withdrawals
            ]
        ]);
    }

    // Update info bank
    public function updateBank(Request $request)
    {
        $request->validate([
            'nama_bank' => 'required|string',
            'nomor_rekening' => 'required|string',
            'atas_nama' => 'required|string',
        ]);

        $user = auth()->user();
        $user->update([
            'nama_bank' => $request->nama_bank,
            'nomor_rekening' => $request->nomor_rekening,
            'atas_nama' => $request->atas_nama,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Info Rekening Bank berhasil diperbarui',
            'data' => [
                'nama_bank' => $user->nama_bank,
                'nomor_rekening' => $user->nomor_rekening,
                'atas_nama' => $user->atas_nama,
            ]
        ]);
    }

    // Ajukan penarikan
    public function store(Request $request)
    {
        $request->validate([
            'nominal' => 'required|numeric|min:50000',
        ]);

        $user = auth()->user();

        if (!$user->nama_bank || !$user->nomor_rekening || !$user->atas_nama) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan lengkapi data Rekening Bank Anda terlebih dahulu'
            ], 400);
        }

        // Cek apakah masih ada penarikan yang pending
        $pending = Withdrawal::where('user_id', auth()->id())->where('status', 'Pending')->first();
        if ($pending) {
            return response()->json([
                'success' => false,
                'message' => 'Anda masih memiliki pengajuan penarikan dana yang sedang diproses'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Lock user row for update to prevent race conditions (double spending)
            $user = User::where('id', auth()->id())->lockForUpdate()->first();

            if ($user->saldo < $request->nominal) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Saldo Anda tidak mencukupi'
                ], 400);
            }

            // Kurangi saldo
            $user->saldo -= $request->nominal;
            $user->save();

            // Catat mutasi
            MutasiSaldo::create([
                'user_id' => $user->id,
                'jenis' => 'Keluar',
                'nominal' => $request->nominal,
                'saldo_akhir' => $user->saldo,
                'keterangan' => 'Pengajuan Penarikan Dana (Tarik Saldo)'
            ]);

            // Buat withdrawal
            $withdrawal = Withdrawal::create([
                'user_id' => $user->id,
                'nominal' => $request->nominal,
                'info_bank' => "{$user->nama_bank} - {$user->nomor_rekening} (a/n {$user->atas_nama})",
                'status' => 'Pending'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan penarikan dana berhasil dikirim',
                'data' => $withdrawal
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
            ], 500);
        }
    }
}
