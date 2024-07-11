<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlayerInfo;
use Illuminate\Support\Facades\Auth;
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
        $player = PlayerInfo::find($id);

        if (!$player) {
            return response()->json(['message' => 'Player not found'], 404);
        }

        return response()->json($player);
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
}