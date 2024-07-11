<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Host;
use Google_Client;
use Google_Service_Oauth2;

class AuthController extends Controller
{
    public function googleSignIn(Request $request)
    {
        $client = new Google_Client(['client_id' => 'YOUR_GOOGLE_CLIENT_ID']);
        $payload = $client->verifyIdToken($request->idToken);
        if ($payload) {
            $userid = $payload['sub'];
            $email = $payload['email'];
            $name = $payload['name'];
            $image = $payload['picture'];

            $host = Host::updateOrCreate(
                ['Host_Email' => $email],
                [
                    'Host_Name' => $name,
                    'Host_Image' => $image,
                ]
            );

            return response()->json(['message' => 'User signed in successfully', 'host' => $host], 200);
        } else {
            return response()->json(['error' => 'Invalid ID token'], 401);
        }
    }
}
