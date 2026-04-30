<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    protected $fillable = [
        'country_id',
        'name',
        'percentage',
        'status',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
