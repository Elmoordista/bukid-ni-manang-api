<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    protected $user_model;

    public function __construct()
    {
        $this->user_model = User::class;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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

    public function register(Request $request)
    {
        //check if email already exists
        $user = $this->user_model::where('email', $request->email)->first();
        if ($user) {
            return response()->json(['message' => 'Email already exists', 'success' => false], 400);
        }
        $name = explode(' ', $request->name);
        $user = $this->user_model::create([
            'name' => $request->name,
            'first_name' => $name[0],
            'last_name' => $name[1] ?? '',
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);
        return response()->json(['message' => 'User registered successfully', 'success' => true], 201);
    }

    public function login(Request $request)
    {
        $user = $this->user_model::where('email', $request->email)->first();
        if (!$user || !\Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials', 'success' => false], 401);
        }
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json(['message' => 'User logged in successfully', 'success' => true, 'access_token' => $token, 'token_type' => 'Bearer', 'user' => $user], 200);
    }

    public function signOut(Request $request){
        auth()->user()->tokens()->delete();
        return response()->json(['message' => 'User signed out successfully', 'success' => true], 200);
    }

     public function test(Request $request)
    {
        return 'test';
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return $request;
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
