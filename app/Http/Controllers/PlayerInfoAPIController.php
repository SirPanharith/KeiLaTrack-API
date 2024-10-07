<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlayerInfo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PlayerInfoAPIController extends Controller
{

    public function index()
    {
        $players = PlayerInfo::all();
        return response()->json($players);
    }

    public function login(Request $request)
    {
        $fields = $request->validate([
            'Player_Email' => 'required|string',
            'Player_Password' => 'required|string'
        ]);
    
        // Check email
        $player = PlayerInfo::where('Player_Email', $fields['Player_Email'])->first();
    
        // Check if player exists and if their account is active
        if (!$player || !Hash::check($fields['Player_Password'], $player->Player_Password)) {
            return response(['message' => 'Invalid email or password'], 401);
        }
    
        // Check any additional conditions if needed
    
        // Generate token for the player
        $token = $player->createToken('PlayerToken')->plainTextToken;
    
        $response = [
            'player' => $player,
            'token' => $token
        ];
    
        return response($response, 201);
    }

    public function register(Request $request)
    {
        $request->validate([
            'Player_Name' => 'required',
            'Player_Email' => 'required|email|unique:PlayerInfo,Player_Email', // Correct table name
            'Player_Password' => 'required',
        ]);

        $data = $request->all();
        $data['Player_Password'] = Hash::make($request->Player_Password);

        // Create the player record
        $player = PlayerInfo::create($data);

        // Generate a token for the player
        $token = $player->createToken('PlayerToken')->plainTextToken;

        // Return the player data along with the token
        return response()->json([
            'player' => $player,
            'token' => $token
        ], 201);
    }

    public function show($id)
    {
        $playerInfo = PlayerInfo::with('player.team', 'player.primaryPosition', 'player.secondaryPosition')->find($id);

        if (!$playerInfo) {
            return response()->json(['message' => 'Player not found'], 404);
        }

        // Prepare the response data
        $playerData = [
            'PlayerInfo_ID' => $playerInfo->PlayerInfo_ID,
            'Player_Name' => $playerInfo->Player_Name,
            'Player_Email' => $playerInfo->Player_Email,
            'Player_Password' => $playerInfo->Player_Password,
            'PlayerInfo_Image' => $playerInfo->PlayerInfo_Image,
            'created_at' => $playerInfo->created_at,
            'updated_at' => $playerInfo->updated_at,
        ];

        $players = $playerInfo->player;
        $playerDetails = [];

        if ($players) {
            foreach ($players as $player) {
                $playerDetails[] = [
                    'Player_ID' => $player->Player_ID,
                    'Team' => $player->team->Team_Name ?? null,
                    'PrimaryPosition' => $player->primaryPosition->Position ?? null,
                    'SecondaryPosition' => $player->secondaryPosition->Position ?? null,
                ];
            }
        }

        $responseData = [
            'player_info' => $playerData,
            'players' => $playerDetails,
        ];

        return response()->json($responseData);
    }

  
    public function update(Request $request, $id)
    {
        $request->validate([
            'Player_Name' => 'required',
            'Player_Email' => 'required|email',
            'Player_Password' => 'required',
        ]);

        $player = PlayerInfo::find($id);

        if (!$player) {
            return response()->json(['message' => 'Player not found'], 404);
        }

        $data = $request->all();
        $data['Player_Password'] = Hash::make($request->Player_Password);

        $player->update($data);

        return response()->json($player, 200);
    }

    /**
     * Remove the specified player from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $player = PlayerInfo::find($id);

        if (!$player) {
            return response()->json(['message' => 'Player not found'], 404);
        }

        $player->delete();

        return response()->json(['message' => 'Player deleted'], 200);
    }

    // public function updatePlayerInfo(Request $request, $id)
    // {
    //     $request->validate([
    //         'Player_Name' => 'required|string|max:255',
    //         'current_password' => 'required|string',
    //         'new_password' => 'required|string|min:8',
    //         'PlayerInfo_Image' => 'nullable|url', // Validate as a URL
    //     ]);

    //     $player = PlayerInfo::find($id);

    //     if (!$player) {
    //         return response()->json(['message' => 'Player not found'], 404);
    //     }

    //     // Check if the current password matches
    //     if (!Hash::check($request->current_password, $player->Player_Password)) {
    //         return response()->json(['message' => 'Current password is incorrect'], 400);
    //     }

    //     // Update player name
    //     $player->Player_Name = $request->Player_Name;

    //     // Update player password if new password is provided
    //     if ($request->new_password) {
    //         $player->Player_Password = Hash::make($request->new_password);
    //     }

    //     // Update player image if provided as URL
    //     if ($request->PlayerInfo_Image) {
    //         $player->PlayerInfo_Image = $request->PlayerInfo_Image;
    //     }

    //     $player->save();

    //     return response()->json($player, 200);
    // }

    public function updatePlayerImage(Request $request, $id)
    {
        $request->validate([
            'PlayerInfo_Image' => 'required|file|mimes:jpeg,png,jpg,gif,svg|max:2048', // Validate as a file
        ]);

        $player = PlayerInfo::find($id);

        if (!$player) {
            return response()->json(['message' => 'Player not found'], 404);
        }

        // Handle file upload
        if ($request->hasFile('PlayerInfo_Image')) {
            $file = $request->file('PlayerInfo_Image');
            $path = $file->store('PlayerUser', 'spaces'); // Store file in DigitalOcean Spaces

            // Save the path to the database
            $player->PlayerInfo_Image = $path;
            $player->save();

            // Return the full image URL
            $fullImageUrl = 'https://keilatrack.sgp1.cdn.digitaloceanspaces.com/' . $path;

            return response()->json([
                'success' => true,
                'message' => 'Player image updated successfully',
                'image_url' => $path
            ], 200);
        }

        return response()->json(['message' => 'No image uploaded'], 400);
    }





    public function updatePlayerCredentials(Request $request, $id)
    {
        $request->validate([
            'Player_Name' => 'nullable|string|max:255',
            'current_password' => 'nullable|string',
            'new_password' => 'nullable|string|min:8|required_with:current_password',
        ]);

        $player = PlayerInfo::find($id);

        if (!$player) {
            return response()->json(['success' => false, 'message' => 'Player not found'], 404);
        }

        // Check if the current password matches
        if ($request->filled('current_password') && $request->filled('new_password')) {
            if (!Hash::check($request->current_password, $player->Player_Password)) {
                return response()->json(['success' => false, 'message' => 'Current password is incorrect'], 400);
            }
            // Update player password if new password is provided
            $player->Player_Password = Hash::make($request->new_password);
        }

        // Update player name if provided
        if ($request->filled('Player_Name')) {
            $player->Player_Name = $request->Player_Name;
        }

        $player->save();

        return response()->json([
            'success' => true,
            'message' => 'Player information updated successfully',
            'Player_Name' => $player->Player_Name,
        ], 200);
    }
    





}