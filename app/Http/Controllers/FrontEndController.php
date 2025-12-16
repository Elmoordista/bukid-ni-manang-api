<?php

namespace App\Http\Controllers;

use App\Models\Bookings;
use App\Models\Payment;
use App\Models\Rooms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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

    public function getRooms(Request $request)
    {
        $minPrice = $request->input('min_price');
        $maxPrice = $request->input('max_price');
        $maxGuests = $request->input('guests');
        $maxBeds = $request->input('beds');


        $rooms = $this->roomModel::query();

        if ($minPrice) {
            $rooms->where('price_per_night', '>=', $minPrice);
        }

        if ($maxPrice) {
            $rooms->where('price_per_night', '<=', $maxPrice);
        }

        if ($maxGuests) {
            $rooms->where('max_occupancy', '>=', $maxGuests);
        }

        if ($maxBeds) {
            $rooms->where('number_of_beds', '>=', $maxBeds);
        }

        $rooms->with('images');

        $rooms = $rooms->get();
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
            ->with(['payment','user', 'room'])
            ->get()
            ->map(function($booking) {
                $today = date('Y-m-d');
                $startDate = date('Y-m-d', strtotime($booking->start_date));
                $endDate = date('Y-m-d', strtotime($booking->end_date));
                
                // Debug: Log the booking details
                Log::info('Booking Debug', [
                    'booking_id' => $booking->id,
                    'status' => $booking->status,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'today' => $today,
                    'is_confirmed' => in_array($booking->status, ['active', 'confirmed', 'pending']),
                    'today >= start' => $today >= $startDate,
                    'today <= end' => $today <= $endDate
                ]);
                
                // Determine if checkout button should show
                // Status must be 'confirmed' (admin approved)
                // Today must be between start and end dates (inclusive)
                $showCheckoutButton = $booking->status === 'confirmed'
                    && $today >= $startDate
                    && $today <= $endDate;
                
                return array_merge($booking->toArray(), [
                    'show_checkout_button' => $showCheckoutButton,
                    'is_checked_in' => $today >= $startDate,
                    'is_check_out_date' => $today === $endDate,
                    'days_until_checkout' => abs((strtotime($endDate) - strtotime($today)) / 86400)
                ]);
            });

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
    public function checkOutBooking(Request $request)
    {
        $id = $request->booking_id;
        $booking = $this->bookingModel::where('id', $id)
            ->where('user_id', Auth::id())
            ->with(['payment','user', 'room'])
            ->first();

        if (!$booking) {
            return response()->json([
                'message' => 'Booking not found.',
            ], 404);
        }

        Log::info('Checkout: Starting checkout process', ['booking_id' => $booking->id, 'user_id' => Auth::id()]);

        $booking->status = 'done';
        $booking->save();

        Log::info('Checkout: Booking status updated to done', ['booking_id' => $booking->id]);

        $emailResult = $this->mailController->bookingCheckedOut($booking->load('user'));
        
        Log::info('Checkout: Email sent', ['booking_id' => $booking->id, 'email_result' => $emailResult]);

        // Return the updated booking with done status immediately
        $today = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime($booking->start_date));
        $endDate = date('Y-m-d', strtotime($booking->end_date));
        
        $bookingData = array_merge($booking->toArray(), [
            'show_checkout_button' => false, // No checkout button for done bookings
            'is_checked_in' => $today >= $startDate,
            'is_check_out_date' => $today === $endDate,
            'days_until_checkout' => abs((strtotime($endDate) - strtotime($today)) / 86400)
        ]);

        return response()->json([
            'message' => 'Booking checked out successfully.',
            'data' => $bookingData,
        ], 200);
    }



    public function getBookingCheckoutStatus(Request $request)
    {
        $userId = auth()->id();
        
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $bookings = Bookings::with(['room', 'user'])
            ->where('user_id', $userId)
            ->get()
            ->map(function($booking) {
                $today = date('Y-m-d');
                $startDate = date('Y-m-d', strtotime($booking->start_date));
                $endDate = date('Y-m-d', strtotime($booking->end_date));
                
                // Show checkout button if:
                // 1. Status is 'confirmed' (admin approved)
                // 2. Today is on or after check-in date
                // 3. Today is on or before check-out date
                $showCheckoutButton = $booking->status === 'confirmed'
                    && $today >= $startDate
                    && $today <= $endDate;
                
                return [
                    'id' => $booking->id,
                    'booking_id' => $booking->booking_id,
                    'room_id' => $booking->room_id,
                    'room_name' => $booking->room->name ?? 'N/A',
                    'start_date' => $booking->start_date,
                    'end_date' => $booking->end_date,
                    'status' => $booking->status,
                    'show_checkout_button' => $showCheckoutButton,
                    'days_until_checkout' => abs((strtotime($endDate) - strtotime($today)) / 86400),
                    'is_checked_in' => $today >= $startDate,
                    'is_check_out_date' => $today === $endDate,
                    'total_price' => $booking->total_price
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => $bookings
        ], 200);
    }
}

