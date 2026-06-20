<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DiskonVoucher;

class DiskonVoucherController extends Controller
{
    public function index()
    {
        $vouchers = DiskonVoucher::latest()->get();
        return response()->json([
            'success' => true,
            'data' => $vouchers
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_voucher' => 'required|string|unique:diskon_vouchers',
            'tipe' => 'required|in:Persen,Nominal',
            'jumlah_potongan' => 'required|numeric|min:0',
            'minimal_beli' => 'required|numeric|min:0',
            'kuota' => 'required|integer|min:1',
            'tgl_kadaluarsa' => 'required|date',
            'is_aktif' => 'boolean'
        ]);

        $voucher = DiskonVoucher::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Voucher berhasil ditambahkan',
            'data' => $voucher
        ]);
    }

    public function show($id)
    {
        $voucher = DiskonVoucher::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $voucher
        ]);
    }

    public function update(Request $request, $id)
    {
        $voucher = DiskonVoucher::findOrFail($id);

        $request->validate([
            'kode_voucher' => 'required|string|unique:diskon_vouchers,kode_voucher,'.$id,
            'tipe' => 'required|in:Persen,Nominal',
            'jumlah_potongan' => 'required|numeric|min:0',
            'minimal_beli' => 'required|numeric|min:0',
            'kuota' => 'required|integer|min:0',
            'tgl_kadaluarsa' => 'required|date',
            'is_aktif' => 'boolean'
        ]);

        $voucher->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Voucher berhasil diupdate',
            'data' => $voucher
        ]);
    }

    public function destroy($id)
    {
        $voucher = DiskonVoucher::findOrFail($id);
        $voucher->delete();

        return response()->json([
            'success' => true,
            'message' => 'Voucher berhasil dihapus'
        ]);
    }
}
