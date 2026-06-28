<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->get();
        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:100|unique:users',
            'nama_lengkap' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:users',
            'password' => 'required|string|min:6',
            'role' => ['required', Rule::in(['Admin', 'Owner', 'Pelanggan'])],
            'is_aktif' => 'boolean'
        ]);

        $user = User::create([
            'username' => $request->username,
            'nama_lengkap' => $request->nama_lengkap,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_aktif' => $request->is_aktif ?? true,
            'saldo' => 0,
            'poin' => 0,
            'level' => 'Basic'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User berhasil ditambahkan',
            'data' => $user
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'username' => ['required', 'string', 'max:100', Rule::unique('users')->ignore($user->id)],
            'nama_lengkap' => 'required|string|max:150',
            'email' => ['required', 'email', 'max:150', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'role' => ['required', Rule::in(['Admin', 'Owner', 'Pelanggan'])],
            'is_aktif' => 'boolean'
        ]);

        $data = [
            'username' => $request->username,
            'nama_lengkap' => $request->nama_lengkap,
            'email' => $request->email,
            'role' => $request->role,
            'is_aktif' => $request->has('is_aktif') ? $request->is_aktif : $user->is_aktif,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'User berhasil diperbarui',
            'data' => $user
        ]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        if (auth()->id() === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak bisa menghapus akun Anda sendiri'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dihapus'
        ]);
    }
}
