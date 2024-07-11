<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Host;
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

        return response()->json($host);
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
}
