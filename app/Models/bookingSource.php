<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class bookingSource extends Model
{
    protected $table = 'booking_sources';
    protected $guarded = [];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
