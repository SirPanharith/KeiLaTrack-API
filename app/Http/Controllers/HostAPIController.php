<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Host;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class HostAPIController extends Controller
{
    public function index()
    {
        $hosts = Host::all();
        return response()->json($hosts);
    }

    public function store(Request $request)
    {
        $request->validate([
            'Host_Name' => 'required',
            'Host_Email' => 'required|email|unique:Host,Host_Email',
            'Host_Password' => 'required',
        ]);

        $data = $request->all();
        $data['Host_Password'] = Hash::make($request->Host_Password);

        $host = Host::create($data);
        $token = $host->createToken('HostToken')->plainTextToken;

        return response()->json([
            'host' => $host,
            'token' => $token
        ], 201);
    }

    public function login(Request $request)
    {
        // Validate the request
        $fields = $request->validate([
            'Host_Email' => 'required|string',
            'Host_Password' => 'required|string'
        ]);
    
        \Log::info('Login request received', $fields);
    
        // Check email
        $host = Host::where('Host_Email', $fields['Host_Email'])->first();
    
        \Log::info('Host found', ['host' => $host]);
    
        // Check if host exists and if their account is active
        if (!$host || !Hash::check($fields['Host_Password'], $host->Host_Password)) {
            \Log::warning('Login failed for email: ' . $fields['Host_Email']);
            return response(['message' => 'Invalid email or password'], 401);
        }
    
        // Generate token for the host
        $token = $host->createToken('HostToken')->plainTextToken;
    
        \Log::info('Token generated', ['token' => $token]);
    
        $response = [
            'host' => [
                'Host_ID' => $host->Host_ID,
                'Host_Name' => $host->Host_Name,
                'Host_Email' => $host->Host_Email,
                'Host_Image' => $host->Host_Image,
                'created_at' => $host->created_at,
                'updated_at' => $host->updated_at
            ],
            'token' => $token
        ];
    
        \Log::info('Response prepared', $response);
    
        return response()->json($response, 201);
    }
    
    

    public function show($id)
    {
        $host = Host::find($id);

        if (!$host) {
            return response()->json(['message' => 'Host not found'], 404);
        }

        // Add the prefix to the Host_Image URL
        $fullImageUrl = $host->Host_Image ? 'https://keilatrack.sgp1.cdn.digitaloceanspaces.com/' . $host->Host_Image : null;

        return response()->json([
            'Host_ID' => $host->Host_ID,
            'Host_Name' => $host->Host_Name,
            'Host_Email' => $host->Host_Email,
            'Host_Image' => $fullImageUrl,
            'AccountStatus_ID' => $host->AccountStatus_ID,
            'created_at' => $host->created_at,
            'updated_at' => $host->updated_at,
        ]);
    }


    public function update(Request $request, $id)
{
    $request->validate([
        'Host_Name' => 'required',
        'Host_Email' => 'required|email',
        'Host_Password' => 'required',
    ]);

    $host = Host::find($id);

    if (!$host) {
        return response()->json(['message' => 'Host not found'], 404);
    }

    // Update only the fields provided in the request
    $data = $request->all();

    if ($request->has('Host_Password')) {
        $data['Host_Password'] = Hash::make($request->Host_Password);
    }

    $host->update($data);

    return response()->json($host, 200);
}


    public function destroy($id)
    {
        $host = Host::find($id);

        if (!$host) {
            return response()->json(['message' => 'Host not found'], 404);
        }

        $host->delete();

        return response()->json(['message' => 'Host deleted'], 200);
    }

    public function updateHostImage(Request $request, $id)
    {
        // Validate the request
        $request->validate([
            'Host_Image' => 'required|file|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validate as a file
        ]);

        // Find the host by ID
        $host = Host::find($id);

        // Check if the host exists
        if (!$host) {
            return response()->json(['message' => 'Host not found'], 404);
        }

        // Handle file upload
        if ($request->hasFile('Host_Image')) {
            $file = $request->file('Host_Image');
            $path = $file->store('HostUser', 'spaces'); // Store file in DigitalOcean Spaces

            // Save the path to the database
            $host->Host_Image = $path;
            $host->save();

            // Return the full image URL
            $fullImageUrl = 'https://keilatrack.sgp1.cdn.digitaloceanspaces.com/' . $path;

            return response()->json([
                'success' => true,
                'message' => 'Host image updated successfully',
                'image_url' => $fullImageUrl
            ], 200);
        }

        return response()->json(['message' => 'No image uploaded'], 400);
    }

    public function updateHostCredentials(Request $request, $id)
    {
        // Validate the request data
        $request->validate([
            'Host_Name' => 'nullable|string|max:255',
            'current_password' => 'nullable|string',
            'new_password' => 'nullable|string|min:8|required_with:current_password',
        ]);

        // Find the host by ID
        $host = Host::find($id);

        // Check if the host exists
        if (!$host) {
            return response()->json(['success' => false, 'message' => 'Host not found'], 404);
        }

        // Check if the current password matches
        if ($request->filled('current_password') && $request->filled('new_password')) {
            if (!Hash::check($request->current_password, $host->Host_Password)) {
                return response()->json(['success' => false, 'message' => 'Current password is incorrect'], 400);
            }
            // Update host password if new password is provided
            $host->Host_Password = Hash::make($request->new_password);
        }

        // Update host name if provided
        if ($request->filled('Host_Name')) {
            $host->Host_Name = $request->Host_Name;
        }

        // Save the changes
        $host->save();

        // Return a success response
        return response()->json([
            'success' => true,
            'message' => 'Host information updated successfully',
            'Host_Name' => $host->Host_Name,
        ], 200);
    }


}
