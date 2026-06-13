<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriGame extends Model
{
    protected $table = 'kategori_games';
    protected $guarded = ['id'];

    public function produks()
    {
        return $this->hasMany(ProdukVoucher::class, 'kategori_game_id');
    }
}
