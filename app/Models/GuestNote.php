<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuestNote extends Model
{
    protected $fillable = [
        'guest_id',
        'note',
        'created_by',
    ];

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}