<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'birthday',
        'nationality',
        'profile_photo',
        'identity_type',
        'identity_number',
        'identity_expiry',
        'identity_document'
    ];

    protected $appends = ['name'];

    public function getNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function preferences()
    {
        return $this->hasOne(GuestPreference::class);
    }

    public function notes()
    {
        return $this->hasMany(GuestNote::class);
    }

    public function reservations()
    {
        return $this->belongsToMany(
            Reservation::class,
            'reservation_guests',
            'guest_id',
            'reservation_id'
        );
    }

    public function getLastStayDateAttribute()
    {
        return $this->reservations()
            ->latest('check_out')
            ->value('check_out');
    }
}