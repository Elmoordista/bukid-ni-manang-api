<?php

namespace App\Http\Controllers;

use App\Models\Bookings;
use Illuminate\Http\Request;

class BookingController extends Controller
{

    protected $model;
    protected $mailController;
    public function __construct(
        Bookings $model,
        MailController $mailController
    )
    {
        $this->model = $model;
        $this->mailController = $mailController;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $bookings = $this->model::query();
        $bookings->with(['user', 'payment']);
        $bookings = $bookings->get();
        return response()->json([
            'data' => $bookings,
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
        $data = $request->only([
            'status',
            'adminNotes',
        ]);

        $booking = $this->model->find($id);
        if(!$booking){
            return response()->json([
                'message' => 'Booking not found',
            ], 404);
        }
        

        $status = $booking->update($data);
        if($status && isset($data['status']) && $data['status'] == 'confirmed'){
            //send booking confirmation email
            $booking = $this->model->with(['user', 'payment'])->find($id);
            $this->mailController->bookingConfirmation($booking);
        }
        else if ($status && isset($data['status']) && $data['status'] == 'rejected'){
            //send booking rejected email
            $booking = $this->model->with(['user', 'payment'])->find($id);
            $this->mailController->bookingRejected($booking);
        }

        return response()->json([
            'message' => 'Booking updated successfully',
            'data' => $booking,
        ], 200);
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
