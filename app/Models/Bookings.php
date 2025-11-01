<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bookings extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'room_id',
        'start_date',
        'end_date',
        'guest_count',
        'total_price',
        'status',
    ];
}
