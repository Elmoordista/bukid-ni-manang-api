<?php

namespace App\Http\Controllers;

use App\Models\RoomImages;
use App\Models\Rooms;
use Illuminate\Http\Request;

class RoomsController extends Controller
{
    public $model;
    public $room_images;
    public function __construct(
        Rooms $model,
        RoomImages $room_images
    )
    {
        $this->model = $model;
        $this->room_images = $room_images;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $page = isset($request->page) ? $request->page : 1;
        $page_size = isset($request->pageSize) ? $request->pageSize : 1;
        $search = isset($request->search) ? $request->search : '';
        $rooms = $this->model::query();

        if ($search) {
            $rooms->where('name', 'like', "%{$search}%");
        }

        $rooms->with('images');

        return response()->json([
            'data' => $rooms->paginate($page_size, ['*'], 'page', $page),
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
        $data = [
            'name' => $request->name,
            'description' => $request->description,
            'max_occupancy' => $request->maxGuests ? $request->maxGuests : 0,
            'number_of_beds' => $request->bedrooms ? $request->bedrooms : 0,
            'number_of_bathrooms' => $request->bathrooms ? $request->bathrooms : 0,
            'location' => $request->location,
            'amenities' => !empty($request->amenities) ? json_encode($request->amenities) : null,
            'price_per_night' => $request->price,
            'status' => $request->available ? 'available' : 'unavailable',
        ];

        $room = $this->model->create($data);
        if($room && !empty($request->images)){
            $this->room_images->where('room_id', $room->id)->delete(); //delete existing images
            foreach($request->images as $image){
                //store images
                $fileUploader = new FileUploaderController();
                $is_base_64 = strpos($image, 'data:image') === 0;
                if($is_base_64){
                    $path = $fileUploader->storeFiles($room->id, $image, 'rooms');
                }
                else{
                    $path = $image; //existing image path
                }
                if($path){
                    $this->room_images->create([
                        'room_id' => $room->id,
                        'image_path' => $path,
                    ]);
                }
            }
        }

        return response()->json([
            'message' => 'Room created successfully',
            'data' => $room,
        ], 200);
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
         $data = [
            'name' => $request->name,
            'description' => $request->description,
            'max_occupancy' => $request->maxGuests ? $request->maxGuests : 0,
            'number_of_beds' => $request->bedrooms ? $request->bedrooms : 0,
            'number_of_bathrooms' => $request->bathrooms ? $request->bathrooms : 0,
            'location' => $request->location,
            'amenities' => !empty($request->amenities) ? json_encode($request->amenities) : null,
            'price_per_night' => $request->price,
            'status' => $request->available ? 'available' : 'unavailable',
        ];

        $room = $this->model->find($id);
        if(!$room){
            return response()->json([
                'message' => 'Room not found',
            ], 404);
        }
        $room->update($data);
        if($room && !empty($request->images)){
            $this->room_images->where('room_id', $id)->delete(); //delete existing images
            foreach($request->images as $image){
                //store images
                $fileUploader = new FileUploaderController();
                $is_base_64 = strpos($image, 'data:image') === 0;
                if($is_base_64){
                    $path = $fileUploader->storeFiles($id, $image, 'rooms');
                }
                else{
                    $path = $image; //existing image path
                }
                if($path){
                    $this->room_images->create([
                        'room_id' => $room->id,
                        'image_path' => $path,
                    ]);
                }
            }
        }

        return response()->json([
            'message' => 'Room updated successfully',
            'data' => $room,
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
        $room = $this->model->find($id);
        if(!$room){
            return response()->json([
                'message' => 'Room not found',
            ], 404);
        }
        //check if room has bookings before deleting
        $bookings = $room->bookings;
        if($bookings && $bookings->count() > 0){
            return response()->json([
                'message' => 'Room cannot be deleted as it has existing bookings',
            ], 400);
        }
        $room->delete();

        return response()->json([
            'message' => 'Room deleted successfully',
        ], 200);
    }
}
