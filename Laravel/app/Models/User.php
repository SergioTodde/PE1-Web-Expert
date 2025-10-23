<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar',
        'role',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function coHostedEvents()
    {
        return $this->belongsToMany(Event::class, 'event_co_hosts')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function favorites()
    {
        return $this->belongsToMany(Event::class, 'favorites')
            ->withTimestamps();
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isCoHost(Event $event)
    {
        return $this->coHostedEvents()->where('event_id', $event->id)->exists();
    }

    public function canEditEvent(Event $event)
    {
        return $this->isAdmin() ||
            $event->user_id === $this->id ||
            $this->isCoHost($event);
    }
}
