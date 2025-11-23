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

    public function getReport(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $bookings = $this->model::query();

        if($search){
            $bookings->whereHas('user', function($query) use ($search) {
                $query->where('first_name', 'like', '%' . $search . '%')
                      ->orWhere('last_name', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%');
            });
        }
        if($status && $status != 'all'){
            $bookings->where('status', $status);
        }

        $bookings->with(['user', 'payment']);
        $bookings = $bookings->paginate(10);
        return response()->json([
            'data' => $bookings,
        ], 200);
    }

    public function exportReports(Request $request, PdfController $pdfController)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $bookings = $this->model::query();

        if($search){
            $bookings->whereHas('user', function($query) use ($search) {
                $query->where('first_name', 'like', '%' . $search . '%')
                      ->orWhere('last_name', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%');
            });
        }
        if($status && $status != 'all'){
            $bookings->where('status', $status);
        }

        $bookings->with(['user']);
        $bookings = $bookings->get();
        return $pdfController->exportBookingsReport($bookings);
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
