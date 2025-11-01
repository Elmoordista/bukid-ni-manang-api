<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rooms extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'max_occupancy',
        'number_of_beds',
        'number_of_bathrooms',
        'location',
        'amenities',
        'price_per_night',
        'status',
    ];

    public function images()
    {
        return $this->hasMany(RoomImages::class, 'room_id', 'id');
    }

    public function bookings()
    {
        return $this->hasMany(Bookings::class, 'room_id', 'id');
    }
}
