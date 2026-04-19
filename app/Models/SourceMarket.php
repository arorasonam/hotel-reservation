<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SourceMarket extends Model
{
    protected $table = 'source_markets';
    protected $guarded = [];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
