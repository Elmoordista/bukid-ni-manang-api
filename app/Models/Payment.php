<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'amount',
        'payment_method',
        'reference_number',
        'status',
    ];

    public function booking()
    {
        return $this->belongsTo(Bookings::class, 'booking_id');
    }
}
