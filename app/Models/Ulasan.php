<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ulasan extends Model
{
    protected $fillable = [
        'user_id', 'produk_voucher_id', 'transaksi_id', 'rating', 'komentar', 'is_aktif'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function produkVoucher()
    {
        return $this->belongsTo(ProdukVoucher::class);
    }

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'transaksi_id', 'id');
    }
}
