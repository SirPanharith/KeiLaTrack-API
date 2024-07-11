<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\Player;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    // Display a listing of the team
    public function index()
    {
        $teams = Team::with('host')->get();
        return response()->json($teams);
    }

    // Store a newly created team in storage
    public function store(Request $request)
    {
        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'Team_Name' => 'required|string|max:255',
                'Host_ID' => 'required|integer',
                'Team_Detail' => 'required|string',
                'Team_Note' => 'nullable|string',
            ]);

            // Create the new team with the validated data
            $team = Team::create($validatedData);

            // Load the related host model and return the JSON response
            return response()->json($team->load('host'), 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            // Handle general errors
            return response()->json(['error' => 'Unable to create team', 'message' => $e->getMessage()], 500);
        }
    }

    // Display the specified team
    public function show($id)
    {
        $team = Team::with('host')->findOrFail($id);
        return response()->json($team);
    }

    // Update the specified team in storage
    public function update(Request $request, $id)
    {
        $team = Team::findOrFail($id);

        $validatedData = $request->validate([
            'Team_Name' => 'string|max:255',
            'Host_ID' => 'integer|exists:PlayerInfo,PlayerInfo_ID',
            'Team_Detail' => 'nullable|string',
            'Team_Note' => 'nullable|string',
        ]);

        $team->update($validatedData);
        return response()->json($team->load('host'));
    }

    // Remove the specified team from storage
    public function destroy($id)
    {
        $team = Team::findOrFail($id);
        $team->delete();
        return response()->json(null, 204);
    }

    // Display all team names based on Host_ID
    public function getTeamsByHost($hostId)
    {
        $teams = Team::where('Host_ID', $hostId)->get(['Team_ID', 'Team_Name']);
        $response = [
            'Host_ID' => $hostId,
            'teams' => $teams
        ];
        return response()->json($response);
    }
    

    // Display all players based on Team_ID
    public function getPlayersByTeam($teamId)
    {
        $players = Player::with(['playerInfo', 'primaryPosition', 'secondaryPosition'])
                    ->where('Team_ID', $teamId)
                    ->get();
    
        $playerList = $players->map(function ($player) {
            return [
                'Player_Name' => $player->playerInfo->Player_Name,
                'Player_Email' => $player->playerInfo->Player_Email,
                'PlayerInfo_Image' => $player->playerInfo->PlayerInfo_Image,
                'Primary_Position' => $player->primaryPosition->Position,
                'Secondary_Position' => $player->secondaryPosition->Position,
            ];
        });
    
        $response = [
            'total_players' => $players->count(),
            'players' => $playerList,
        ];
    
        return response()->json($response);
    }
    

}
