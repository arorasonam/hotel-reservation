<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosCategory extends Model
{
    protected $fillable = [
        'pos_outlet_id',
        'name',
        'tax_id',
        'status',
    ];

    public function outlet()
    {
        return $this->belongsTo(PosOutlet::class, 'pos_outlet_id');
    }

    public function items()
    {
        return $this->hasMany(PosItem::class);
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }
}
