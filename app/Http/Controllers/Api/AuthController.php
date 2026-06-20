<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required'
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau password salah'
            ], 401);
        }

        if (!$user->is_aktif) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak aktif'
            ], 401);
        }

        $token = $user->createToken('admin_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string',
            'username' => 'required|string|unique:users',
            'no_telepon' => 'required|string',
            'password' => 'required|min:6',
            'kode_referral' => 'nullable|string|exists:users,kode_referral'
        ]);

        $referred_by = null;
        if ($request->filled('kode_referral')) {
            $referrer = User::where('kode_referral', $request->kode_referral)->first();
            if ($referrer) {
                $referred_by = $referrer->id;
            }
        }

        $user = User::create([
            'nama_lengkap' => $request->nama_lengkap,
            'username' => $request->username,
            'no_telepon' => $request->no_telepon,
            'password' => Hash::make($request->password),
            'role' => 'Pelanggan',
            'level' => 'Basic',
            'saldo' => 0,
            'kode_referral' => 'REF-' . strtoupper(substr($request->username, 0, 4)) . rand(1000, 9999),
            'referred_by' => $referred_by
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    }
}
