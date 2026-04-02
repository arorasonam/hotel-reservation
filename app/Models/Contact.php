<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasUuids;

    protected $guarded = [];
    protected $casts = [
        'status' => \App\Enums\ContactStatusEnum::class,
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
