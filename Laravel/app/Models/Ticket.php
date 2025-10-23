<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'quantity_available',
        'quantity_sold',
        'max_per_person',
        'sale_start_date',
        'sale_end_date',
        'is_active',
        'event_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_start_date' => 'datetime',
        'sale_end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function getAvailableQuantityAttribute()
    {
        return $this->quantity_available - $this->quantity_sold;
    }

    public function getIsAvailableAttribute()
    {
        return $this->is_active &&
            $this->available_quantity > 0 &&
            $this->sale_start_date <= now() &&
            $this->sale_end_date >= now();
    }

    public function canBook($quantity)
    {
        return $this->is_available &&
            $quantity <= $this->available_quantity &&
            $quantity <= $this->max_per_person;
    }
}
