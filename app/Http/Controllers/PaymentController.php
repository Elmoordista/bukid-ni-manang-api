<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected $model;
    public function __construct(
        Payment $model
    )
    {
        $this->model = $model;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $page = isset($request->page) ? $request->page : 1;
        $page_size = isset($request->per_page) ? $request->per_page : 1;
        $search = isset($request->search) ? $request->search : '';
        $status = isset($request->status) ? $request->status : '';
        $payments = $this->model::query();
        if ($search) {
            $payments->where('reference_number', 'like', "%{$search}%");
        }

        if ($status) {
            $payments->where('status', $status);
        }

        $payments->with('booking.user');
        return response()->json([
            'data' => $payments->paginate($page_size, ['*'], 'page', $page),
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
        $payments = $this->model::find($id);
        if (!$payments) {
            return response()->json([
                'message' => 'Payment not found',
            ], 404);
        }
        $payments->update($request->all());
        return response()->json([
            'message' => 'Payment updated successfully',
            'data' => $payments,
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

    public function exportPayments(Request $request)
    {
        $payments = $this->model::query()->with('booking.user')->get();
        $pdfController = new PdfController();
        return $pdfController->export($request, $payments);
    }
}
