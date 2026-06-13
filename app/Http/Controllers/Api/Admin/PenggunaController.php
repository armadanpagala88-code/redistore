<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class PenggunaController extends Controller
{
    public function index()
    {
        // Get all members / pelanggan
        $penggunas = User::where('role', 'Pelanggan')
                         ->orderBy('created_at', 'desc')
                         ->get(['id', 'username', 'nama_lengkap', 'email', 'no_telepon', 'poin', 'saldo', 'created_at']);
                         
        return response()->json([
            'success' => true,
            'data' => $penggunas
        ]);
    }
}
