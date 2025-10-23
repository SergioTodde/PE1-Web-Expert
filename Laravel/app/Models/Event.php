<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'location',
        'category',
        'latitude',
        'longitude',
        'max_attendees',
        'ticket_sale_start',
        'is_published',
        'user_id',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'ticket_sale_start' => 'datetime',
        'is_published' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function images()
    {
        return $this->hasMany(EventImage::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function favorites()
    {
        return $this->belongsToMany(User::class, 'favorites');
    }

    public function coHosts()
    {
        return $this->belongsToMany(User::class, 'event_co_hosts')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function bookings()
    {
        return $this->hasManyThrough(Booking::class, Ticket::class);
    }

    public function getPrimaryImageAttribute()
    {
        return $this->images->where('is_primary', true)->first() ?? $this->images->first();
    }

    public function getIsFavoriteAttribute()
    {
        if (!auth()->check()) return false;
        return $this->favorites->contains(auth()->id());
    }

    public function getAvailableTicketsAttribute()
    {
        return $this->tickets->where('is_active', true)
            ->where('sale_start_date', '<=', now())
            ->where('sale_end_date', '>=', now())
            ->where('quantity_available', '>', 0);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('location', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    public function scopeCategory($query, $category)
    {
        if ($category) {
            return $query->where('category', $category);
        }
        return $query;
    }

    public function scopeLocation($query, $location)
    {
        if ($location) {
            return $query->where('location', 'like', "%{$location}%");
        }
        return $query;
    }

    public function scopeDate($query, $date)
    {
        if ($date) {
            return $query->whereDate('start_date', $date);
        }
        return $query;
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}
