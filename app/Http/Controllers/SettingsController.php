<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    protected $settings;
    protected $mailController;
    public function __construct(
        Settings $settings,
        MailController $mailController
    )
    {
        $this->settings = $settings;
        $this->mailController = $mailController;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $type = $request->type;
        $settings = $this->settings->where('type', $type)->first();
        if (!$settings) {
            return response()->json([
                'message' => 'Settings not found'
            ], 404);
        }
        return response()->json([
            'message' => 'Settings retrieved successfully',
            'data' => $settings
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
        $type = $request->type;
        // Check if settings of the given type already exist
        $existingSettings = $this->settings->where('type', $type)->first();
        if ($existingSettings) {
           //update existing settings
           $existingSettings->update([
               'settings' => json_encode($request->settings),
           ]);
           return response()->json([
               'message' => 'Settings updated successfully',
               'data' => $existingSettings
           ], 200);
        }

        $settings = $this->settings->create(
            [
                'type' => $request->type,
                'settings' => json_encode($request->settings),
            ]
        );
        return response()->json([
            'message' => 'Settings created successfully',
            'data' => $settings
        ], 201);
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

    public function testEmail(Request $request)
    {   
        return $sendTestEmail = $this->mailController->sendTestEmail($request->all());
        if (!$sendTestEmail) {
            return response()->json([
                'message' => 'Failed to send test email'
            ], 500);
        }
        return response()->json([
            'message' => 'Test email sent successfully'
        ], 200);
    }
}
