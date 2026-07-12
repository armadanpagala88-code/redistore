<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AkunGame extends Model
{
    protected $fillable = [
        'user_id',
        'kategori_game_id',
        'judul_akun',
        'deskripsi_akun',
        'harga',
        'login_via',
        'email_akun',
        'password_akun',
        'catatan_akun',
        'gambar_utama',
        'gambar_lainnya',
        'status',
    ];

    protected $casts = [
        'gambar_lainnya' => 'array',
    ];

    public function kategori()
    {
        return $this->belongsTo(KategoriGame::class, 'kategori_game_id');
    }

    public function penjual()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
