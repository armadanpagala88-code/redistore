<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdukVoucher extends Model
{
    protected $table = 'produk_vouchers';
    protected $guarded = ['id'];

    public function kategori()
    {
        return $this->belongsTo(KategoriGame::class, 'kategori_game_id');
    }
}
