<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\PrimaryPosition;
use App\Models\SecondaryPosition;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PlayerController extends Controller
{
    // public function index()
    // {
    //     $players = Player::with('primaryPosition', 'secondaryPosition')->get();
    //     return response()->json($players);
    // }

    public function index()
    {
        $players = Player::with([
            'playerInfo', // Load related player information
            'team', // Load related team information
            'primaryPosition', // Load related primary position information
            'secondaryPosition', // Load related secondary position information
        ])->get();

        return response()->json($players);
    }

    public function store(Request $request)
    {
        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'PlayerInfo_ID' => 'required|exists:playerinfo,PlayerInfo_ID',
                'Team_ID' => 'required|exists:team,Team_ID',
                'PrimaryPosition_ID' => 'required|exists:primaryposition,PrimaryPosition_ID',
                'SecondaryPosition_ID' => 'required|exists:secondaryposition,SecondaryPosition_ID',
            ]);
    
            // Create the new player with the validated data
            $player = Player::create($validatedData);
    
            // Load the related models for the response
            $player->load('playerInfo', 'team', 'primaryPosition', 'secondaryPosition');
    
            // Prepare the response data
            $responseData = [
                'Player_ID' => $player->Player_ID,
                'Player_Name' => $player->playerInfo->Player_Name,
                'Team_Name' => $player->team->Team_Name,
                'Primary_Position' => $player->primaryPosition->Position,
                'Secondary_Position' => $player->secondaryPosition->Position,
            ];
    
            // Return the JSON response with the newly created player and related models
            return response()->json($responseData, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            // Handle general errors
            return response()->json(['error' => 'Unable to create player', 'message' => $e->getMessage()], 500);
        }
    }
    

    public function show($playerInfoId)
    {
        // Retrieve the player based on the PlayerInfo_ID and eager load all related data
        $player = Player::with('playerInfo', 'team', 'primaryPosition', 'secondaryPosition', 'homeScores', 'performances', 'teamPerformances')->whereHas('playerInfo', function ($query) use ($playerInfoId) {
            $query->where('PlayerInfo_ID', $playerInfoId);
        })->firstOrFail();
    
        return response()->json($player);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'PlayerInfo_ID' => 'required',
            'Team_ID' => 'required',
            'PrimaryPosition_ID' => 'required',
            'SecondaryPosition_ID' => 'required',
        ]);

        $player = Player::findOrFail($id);
        $player->update($request->all());

        return response()->json($player, 200);
    }

    public function destroy($id)
    {
        $player = Player::findOrFail($id);
        $player->delete();

        return response()->json(null, 204);
    }

    
}