<?php

namespace App\Http\Controllers;

use App\Models\Bookings;
use App\Models\Payment;
use App\Models\Rooms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FrontEndController extends Controller
{
    protected $roomModel;
    protected $bookingModel;
    protected $paymentModel;
    protected $mailController;

    public function __construct(
        Rooms $roomModel,
        Bookings $bookingModel,
        Payment $paymentModel,
        MailController $mailController
    )
    {
        $this->roomModel = $roomModel;
        $this->bookingModel = $bookingModel;
        $this->paymentModel = $paymentModel;
        $this->mailController = $mailController;
    }

    public function getRooms()
    {
        $rooms = $this->roomModel::with('images')->get();
        return response()->json([
            'data' => $rooms,
        ], 200);
    }

    public function bookRoom(Request $request)
    { 
        $data = [
            'user_id' => Auth::id(),
            'room_id' => $request->accommodationId,
            'start_date' => $request->checkInDate,
            'end_date' => $request->checkOutDate,
            'guest_count' => $request->guestCount,
            'total_price' => $request->totalAmount,
            'status' => $request->status,
        ];

        //check if user is already booked the room for the same dates
        $existingBooking = $this->bookingModel::where('user_id', Auth::id())
            ->where('room_id', $request->accommodationId)
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_date', [$request->checkInDate, $request->checkOutDate])
                      ->orWhereBetween('end_date', [$request->checkInDate, $request->checkOutDate]);
            })
            ->first();
        if ($existingBooking) {
            return response()->json([
                'message' => 'You have already booked this room for the selected dates.',
            ], 400);
        }
        
        // check if the user is already booked this room is still pending or active
        $activeBooking = $this->bookingModel::where('user_id', Auth::id())
            ->where('room_id', $request->accommodationId)
            ->whereIn('status', ['pending', 'active'])
            ->first();
        if ($activeBooking) {
            return response()->json([
                'message' => 'You have an active or pending booking for this room.',
            ], 400);
        }

        $booking = $this->bookingModel->create($data);

        if($booking){
            //create payment record
            $this->paymentModel->create([
                'booking_id' => $booking->id,
                'amount' => $request->totalAmount,
                'payment_method' => $request->paymentMethod,
                'reference_number' => $request->paymentMethod == 'gcash' ? $request->paymentReference : null,
                'status' => 'pending',
            ]);

            if($booking){
                $this->mailController->newBooking($booking->load('user'));
            }
        }

        return response()->json([
            'message' => 'Room booked successfully!',
            'booking_details' => $request->all(),
        ], 201);
    }

    public function getMyBookings()
    {
        $bookings = $this->bookingModel::where('user_id', Auth::id())
            ->with(['payment','user'])
            ->get();

        return response()->json([
            'data' => $bookings,
        ], 200);
    }

    public function cancelBooking(Request $request)
    {
        $id = $request->booking_id;
        $booking = $this->bookingModel::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$booking) {
            return response()->json([
                'message' => 'Booking not found.',
            ], 404);
        }

        $booking->status = 'cancelled';
        $booking->save();

        $this->mailController->bookingRejected($booking->load('user'));

        return response()->json([
            'message' => 'Booking cancelled successfully.',
        ], 200);
    }
}

