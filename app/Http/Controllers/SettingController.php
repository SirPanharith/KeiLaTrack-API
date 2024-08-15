<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::with(['subMode', 'divide', 'side'])->get();
        return response()->json($settings);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'SubMode_ID' => 'required|exists:SubMode,SubMode_ID', // Ensure the SubMode_ID exists
                'Session_ID' => 'required|exists:SessionGame,Session_ID', // Ensure the Session_ID exists
                'Sub_Timespace' => 'required|date_format:H:i:s',
                'Divide_ID' => 'required|exists:Divide,Divide_ID', // Ensure the Divide_ID exists
                'S_Num' => 'required|integer',
                'M_Num' => 'required|integer',
                'D_Num' => 'required|integer',
                'GK_Num' => 'required|integer',
                'Side_ID' => 'required|exists:Side,Side_ID', // Ensure the Side_ID exists
            ]);

            $setting = Setting::create($validated);
            return response()->json([
                'setting' => $setting,
                'Session_ID' => $setting->Session_ID
            ], 201);
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle query exceptions, such as unique constraints or foreign key issues
            return response()->json(['error' => 'Database operation failed', 'details' => $e->getMessage()], 500);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // The exception is already handled by Laravel, but you can customize the response if needed
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            // Handle any other exceptions
            return response()->json(['error' => 'An unexpected error occurred', 'details' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $setting = Setting::with(['subMode', 'divide', 'side'])->findOrFail($id);
        return response()->json($setting);
    }

    public function update(Request $request, $id)
    {
        // Find the setting by ID
        $setting = Setting::findOrFail($id);

        // Validate the request with all fields optional
        $validated = $request->validate([
            'SubMode_ID' => 'numeric',
            'Sub_Timespace' => 'date_format:H:i:s',
            'Divide_ID' => 'numeric',
            'S_Num' => 'numeric',
            'M_Num' => 'numeric',
            'D_Num' => 'numeric',
            'GK_Num' => 'numeric',
            'Side_ID' => 'numeric',
        ]);

        // Only update the fields that are present in the request
        foreach ($validated as $key => $value) {
            $setting->$key = $value;
        }

        // Save the changes to the database
        $setting->save();

        // Fetch the updated setting with relationships
        $updatedSetting = Setting::with(['subMode', 'divide', 'side'])->findOrFail($id);

        return response()->json($updatedSetting);
    }

    public function destroy($id)
    {
        $setting = Setting::findOrFail($id);
        $setting->delete();
        return response()->json(null, 204);
    }
}
