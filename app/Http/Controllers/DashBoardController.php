<?php

namespace App\Http\Controllers;

use App\Models\Bookings;
use App\Models\Rooms;
use Illuminate\Http\Request;

class DashBoardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $todays_booking_confirmed = Bookings::whereDate('created_at', now()->toDateString())
        ->where('status', 'confirmed')
        ->get();
        $todays_booking_pending = Bookings::whereDate('created_at', now()->toDateString())->where('status', 'pending')->get();
        $rooms_available = Rooms::where('status', 'available')
        ->whereHas('bookings', function ($query) {
            $query->where('status', 'confirmed');
        }, '=', 0)
        ->get();
        $bookings = Bookings::query()->take(5)
        ->with('user')
        ->orderBy('created_at', 'desc')
        ->get();
        $all_rooms = Rooms::count();

        return response()->json([
            'todays_booking_confirmed' => $todays_booking_confirmed,
            'todays_booking_pending' => $todays_booking_pending,
            'rooms_available' => $rooms_available,
            'all_rooms' => $all_rooms,
            'bookings' => $bookings,
        ], 200);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
