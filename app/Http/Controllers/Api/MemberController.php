<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MutasiSaldo;
use App\Models\Transaksi;

class MemberController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();

        // Get last 5 mutasi saldo
        $mutasi = MutasiSaldo::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Get last 5 transaksi pembelian
        $transaksi = Transaksi::with(['details'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'mutasi_terakhir' => $mutasi,
                'transaksi_terakhir' => $transaksi
            ]
        ]);
    }

    public function redeemPoints(Request $request)
    {
        $request->validate([
            'points' => 'required|integer|min:100'
        ]);

        $user = $request->user();
        $pointsToRedeem = $request->points;

        if ($user->poin < $pointsToRedeem) {
            return response()->json(['success' => false, 'message' => 'Poin tidak mencukupi'], 400);
        }

        // 1 Point = Rp 10
        $saldoToGive = $pointsToRedeem * 10;

        // Kurangi poin
        $user->poin -= $pointsToRedeem;
        
        // Tambah saldo
        $user->saldo += $saldoToGive;
        $user->save();

        // Catat mutasi
        MutasiSaldo::create([
            'user_id' => $user->id,
            'tipe' => 'Masuk',
            'nominal' => $saldoToGive,
            'keterangan' => "Penukaran {$pointsToRedeem} Poin Loyalitas"
        ]);

        return response()->json([
            'success' => true,
            'message' => "Berhasil menukar {$pointsToRedeem} Poin menjadi Saldo Rp " . number_format($saldoToGive, 0, ',', '.')
        ]);
    }

    public function updatePhoto(Request $request)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $user = $request->user();

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/profil'), $filename);

            // Delete old photo if exists
            if ($user->foto_profil && file_exists(public_path('uploads/profil/' . $user->foto_profil))) {
                unlink(public_path('uploads/profil/' . $user->foto_profil));
            }

            $user->foto_profil = $filename;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Foto profil berhasil diperbarui',
                'foto_url' => url('/uploads/profil/' . $filename),
                'foto_profil' => $filename
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal mengupload foto'
        ], 400);
    }
}
