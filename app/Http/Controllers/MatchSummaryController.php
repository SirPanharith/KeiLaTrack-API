<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MatchSummary;
use App\Models\HomeAssist;

class MatchSummaryController extends Controller
{
    // Display a listing of the resource.
    public function index()
    {
        $matchSummaries = MatchSummary::all();
        return response()->json($matchSummaries);
    }

    // Store a newly created resource in storage.
    public function store(Request $request)
{
    // Validate the incoming request
    $request->validate([
        'Session_ID' => 'required|integer|exists:sessiongame,Session_ID',
        'Player_ID' => 'nullable|integer|exists:player,Player_ID',
        'ManualPlayer_ID' => 'nullable|integer|exists:ManualPlayer,ManualPlayer_ID',
        'Total_Goals' => 'required|integer',
        'Total_Assists' => 'required|integer',
        'Total_Duration' => 'required|date_format:H:i:s',
    ]);

    // Ensure that either Player_ID or ManualPlayer_ID is provided, but not both
    if (is_null($request->Player_ID) && is_null($request->ManualPlayer_ID)) {
        return response()->json(['error' => 'Either Player_ID or ManualPlayer_ID must be provided'], 400);
    }

    if (!is_null($request->Player_ID) && !is_null($request->ManualPlayer_ID)) {
        return response()->json(['error' => 'Only one of Player_ID or ManualPlayer_ID should be provided'], 400);
    }

    // Create the MatchSummary record
    $matchSummary = MatchSummary::create([
        'Session_ID' => $request->Session_ID,
        'Player_ID' => $request->Player_ID,
        'ManualPlayer_ID' => $request->ManualPlayer_ID,
        'Total_Goals' => $request->Total_Goals,
        'Total_Assists' => $request->Total_Assists,
        'Total_Duration' => $request->Total_Duration,
    ]);

    // Return a 200 OK response with a success message
    return response()->json([
        'message' => 'Match summary created successfully',
        'data' => $matchSummary
    ], 200);
}



    // Display the specified resource.
    public function show($id)
    {
        $matchSummary = MatchSummary::findOrFail($id);
        return response()->json($matchSummary);
    }

    // Update the specified resource in storage.
    public function update(Request $request, $id)
    {
        $request->validate([
            'Session_ID' => 'required|integer',
            'Team_ID' => 'required|integer',
            'Player_ID' => 'nullable|integer',
            'ManualPlayer_ID' => 'nullable|integer',
            'Total_Goals' => 'required|integer',
            'Total_Assists' => 'required|integer',
            'Total_Duration' => 'required|integer',
        ]);

        $matchSummary = MatchSummary::findOrFail($id);
        $matchSummary->update($request->all());
        return response()->json($matchSummary, 200);
    }

    // Remove the specified resource from storage.
    public function destroy($id)
    {
        $matchSummary = MatchSummary::findOrFail($id);
        $matchSummary->delete();
        return response()->json(null, 204);
    }

    // Get information based on Session_ID
    public function getBySessionId($sessionId)
    {
        $matchSummaries = MatchSummary::where('Session_ID', $sessionId)
            ->with(['player.playerInfo', 'manualPlayer', 'player.primaryPosition', 'player.secondaryPosition', 'manualPlayer.primaryPosition', 'manualPlayer.secondaryPosition', 'sessionGame.team'])
            ->get();

        if ($matchSummaries->isEmpty()) {
            return response()->json(['message' => 'No match summaries found for this session'], 404);
        }

        $teamName = $matchSummaries->first()->sessionGame->team->Team_Name ?? 'N/A';
        $sessionTotalGoals = $matchSummaries->sum('Total_Goals');

        // Calculate total assists for each player and manual player
        $totalAssists = [];

        foreach ($matchSummaries as $summary) {
            if ($summary->Player_ID) {
                $totalAssists[$summary->Player_ID] = HomeAssist::where('Player_ID', $summary->Player_ID)->count();
            } elseif ($summary->ManualPlayer_ID) {
                $totalAssists[$summary->ManualPlayer_ID] = HomeAssist::where('ManualPlayer_ID', $summary->ManualPlayer_ID)->count();
            }
        }

        // Transform the match summaries to include PrimaryPosition, SecondaryPosition, and Team_Name
        $result = $matchSummaries->map(function ($summary) use ($totalAssists) {
            $primaryPosition = 'N/A';
            $secondaryPosition = 'N/A';
            $playerName = 'N/A';
            $manualPlayerName = 'N/A';
            $playerDetails = [];

            if ($summary->Player_ID && $summary->player) {
                $primaryPosition = $summary->player->primaryPosition->Position ?? 'N/A';
                $secondaryPosition = $summary->player->secondaryPosition->Position ?? 'N/A';
                $playerName = optional($summary->player->playerInfo)->Player_Name ?? 'N/A';
                $playerDetails = [
                    'Player_ID' => $summary->Player_ID,
                    'Player_Name' => $playerName,
                    'Total_Assists' => $totalAssists[$summary->Player_ID] ?? 0,
                ];
            } elseif ($summary->ManualPlayer_ID && $summary->manualPlayer) {
                $primaryPosition = $summary->manualPlayer->primaryPosition->Position ?? 'N/A';
                $secondaryPosition = $summary->manualPlayer->secondaryPosition->Position ?? 'N/A';
                $manualPlayerName = $summary->manualPlayer->ManualPlayer_Name ?? 'N/A';
                $playerDetails = [
                    'ManualPlayer_ID' => $summary->ManualPlayer_ID,
                    'ManualPlayer_Name' => $manualPlayerName,
                    'Total_Assists' => $totalAssists[$summary->ManualPlayer_ID] ?? 0,
                ];
            }

            return array_merge($playerDetails, [
                'MatchSummary_ID' => $summary->MatchSummary_ID,
                'Session_ID' => $summary->Session_ID,
                'Total_Goals' => $summary->Total_Goals,
                'Total_Duration' => $summary->Total_Duration,
                'PrimaryPosition' => $primaryPosition,
                'SecondaryPosition' => $secondaryPosition,
            ]);
        });

        return response()->json([
            'Session_ID' => $sessionId,
            'Team_Name' => $teamName,
            'Session_Total_Goals' => $sessionTotalGoals,
            'match_summaries' => $result,
        ]);
    }

}
