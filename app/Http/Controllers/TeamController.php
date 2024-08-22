<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\Player;
use App\Models\SessionGame;
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
        // Get teams with player and session game counts
        $teams = Team::withCount(['players', 'sessionGames'])
            ->where('Host_ID', $hostId)
            ->get(['Team_ID', 'Team_Name']);

        // Loop through each team to calculate additional details
        $formattedTeams = $teams->map(function ($team) {
            // Retrieve all session games for the team
            $sessionGames = SessionGame::where('Team_ID', $team->Team_ID)->get();

            $winCount = 0;  // Initialize win counter
            $loseCount = 0; // Initialize lose counter
            $drawCount = 0; // Initialize draw counter

            // Prepare a list of sessions with their respective Session_Total_Goals, ManualAway_Score, and Result
            $sessionDetails = $sessionGames->map(function ($sessionGame) use (&$winCount, &$loseCount, &$drawCount) {
                // Calculate total goals using an external method or logic
                $homeScoreController = new HomeScoreController();
                $sessionTotalGoals = $homeScoreController->calculateSessionTotalGoals($sessionGame->Session_ID);
                $manualAwayScore = $sessionGame->ManualAway_Score ?? 0;

                // Determine the result based on the scores
                $result = 'Draw';
                if ($sessionTotalGoals > $manualAwayScore) {
                    $result = 'Win';
                    $winCount++; // Increment win counter
                } elseif ($sessionTotalGoals < $manualAwayScore) {
                    $result = 'Lose';
                    $loseCount++; // Increment lose counter
                } else {
                    $drawCount++; // Increment draw counter
                }

                return [
                    'Session_ID' => $sessionGame->Session_ID,
                    'Session_Total_Goals' => $sessionTotalGoals,
                    'ManualAway_Score' => $manualAwayScore,
                    'Result' => $result, // Add the result field
                ];
            });

            return [
                'Team_ID' => $team->Team_ID,
                'Team_Name' => $team->Team_Name,
                'Total_Players' => $team->players_count,
                'Total_Games' => $team->session_games_count,
                'Total_Wins' => $winCount,   // Add the win count
                'Total_Loses' => $loseCount, // Add the lose count
                'Total_Draws' => $drawCount, // Add the draw count
                'Sessions' => $sessionDetails->toArray(), // List of sessions with their details
            ];
        });

        return response()->json([
            'Host_ID' => $hostId,
            'teams' => $formattedTeams,
        ]);
    }

    // Display all players based on Team_ID
    public function getPlayersByTeam($teamId)
    {
        $players = Player::with(['PlayerInfo', 'PrimaryPosition', 'SecondaryPosition'])
                    ->where('Team_ID', $teamId)
                    ->get();
    
        $playerList = $players->map(function ($player) {
            return [
                'Player_Name' => $player->PlayerInfo->Player_Name,
                'Player_Email' => $player->PlayerInfo->Player_Email,
                'PlayerInfo_Image' => $player->PlayerInfo->PlayerInfo_Image,
                'Primary_Position' => $player->PrimaryPosition->Position,
                'Secondary_Position' => $player->SecondaryPosition->Position,
            ];
        });
    
        $response = [
            'total_players' => $players->count(),
            'players' => $playerList,
        ];
    
        return response()->json($response);
    }
    

}
