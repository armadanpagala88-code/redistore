<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rekomendasi extends Model
{
    protected $fillable = [
        'user_id', 'produk_voucher_id', 'skor', 'alasan'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function produkVoucher()
    {
        return $this->belongsTo(ProdukVoucher::class);
    }
}
