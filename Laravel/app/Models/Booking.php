<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_reference',
        'quantity',
        'total_amount',
        'status',
        'user_id',
        'ticket_id',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            $booking->booking_reference = 'BK-' . strtoupper(uniqid());
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function confirm()
    {
        $this->update(['status' => 'confirmed']);
        $this->ticket->increment('quantity_sold', $this->quantity);
    }

    public function cancel()
    {
        if ($this->status === 'confirmed') {
            $this->ticket->decrement('quantity_sold', $this->quantity);
        }
        $this->update(['status' => 'cancelled']);
    }
}
