<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosItem extends Model
{
    protected $fillable = [
        'pos_outlet_id',
        'pos_category_id',
        'name',
        'price',
        'tax_percentage',
        'status'
    ];

    public function outlet()
    {
        return $this->belongsTo(PosOutlet::class, 'pos_outlet_id');
    }

    public function category()
    {
        return $this->belongsTo(PosCategory::class, 'pos_category_id');
    }
}