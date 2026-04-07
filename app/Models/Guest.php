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
        'nationality'
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
        return $this->hasMany(Reservation::class);
    }

    public function getLastStayDateAttribute()
    {
        return $this->reservations()
            ->latest('check_out')
            ->value('check_out');
    }
}