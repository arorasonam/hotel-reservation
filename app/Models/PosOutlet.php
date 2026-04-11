<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosOutlet extends Model
{
    protected $fillable = [
        'hotel_id',
        'name',
        'code',
        'description',
        'status'
    ];

    public function categories()
    {
        return $this->hasMany(PosCategory::class);
    }
}
