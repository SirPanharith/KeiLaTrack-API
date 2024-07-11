<?php

namespace App\Http\Controllers;

use App\Models\HomeScore;
use App\Models\MatchSummary;
use Illuminate\Http\Request;

class HomeScoreController extends Controller
{
    public function index()
    {
        return HomeScore::all();
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'Player_ID' => 'nullable|exists:player,Player_ID',
            'ManualPlayer_ID' => 'nullable|exists:ManualPlayer,ManualPlayer_ID',
            'HomeAssist_ID' => 'nullable|exists:homeassist,HomeAssist_ID',
            'ScoreTime' => 'required|date_format:H:i:s',
            'Session_ID' => 'required|exists:sessiongame,Session_ID',
        ]);

        if (is_null($request->Player_ID) && is_null($request->ManualPlayer_ID)) {
            return response()->json(['message' => 'Either Player_ID or ManualPlayer_ID must be provided'], 400);
        }

        $homeScore = HomeScore::create($validatedData);
        return response()->json($homeScore, 201);
    }

    public function show($id)
    {
        $homeScore = HomeScore::find($id);
        if (!$homeScore) {
            return response()->json(['message' => 'Not found'], 404);
        }
        return response()->json($homeScore, 200);
    }

    public function update(Request $request, $id)
    {
        $homeScore = HomeScore::find($id);
        if (!$homeScore) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $validatedData = $request->validate([
            'Player_ID' => 'nullable|exists:players,Player_ID',
            'ManualPlayer_ID' => 'nullable|exists:manual_player,ManualPlayer_ID',
            'HomeAssist_ID' => 'nullable|exists:home_assist,HomeAssist_ID',
            'ScoreTime' => 'required|date_format:H:i:s',
            'Session_ID' => 'required|exists:session_game,Session_ID',
        ]);

        if (is_null($request->Player_ID) && is_null($request->ManualPlayer_ID)) {
            return response()->json(['message' => 'Either Player_ID or ManualPlayer_ID must be provided'], 400);
        }

        $homeScore->update($validatedData);
        return response()->json($homeScore, 200);
    }

    public function destroy($id)
    {
        $homeScore = HomeScore::find($id);
        if (!$homeScore) {
            return response()->json(['message' => 'Not found'], 404);
        }
        $homeScore->delete();
        return response()->json(['message' => 'Deleted successfully'], 200);
    }

    //Home Score by Session_ID
    public function getBySession($sessionId)
    {
        $homeScores = HomeScore::with(['player.playerInfo', 'manualPlayer'])->where('Session_ID', $sessionId)->get();

        if ($homeScores->isEmpty()) {
            return response()->json(['message' => 'No records found for the given Session_ID'], 404);
        }

        $sessionTotalGoals = $homeScores->count();

        $playerScores = [];

        foreach ($homeScores as $homeScore) {
            $key = $homeScore->Player_ID ?? $homeScore->ManualPlayer_ID;
            $isPlayer = isset($homeScore->Player_ID);
            $name = $isPlayer ? optional($homeScore->player->playerInfo)->Player_Name : optional($homeScore->manualPlayer)->ManualPlayer_Name;

            if (!isset($playerScores[$key])) {
                $playerScores[$key] = [
                    $isPlayer ? 'Player_ID' : 'ManualPlayer_ID' => $key,
                    $isPlayer ? 'Player_Name' : 'ManualPlayer_Name' => $name,
                    'Total_Goals' => 0,
                    'Scores' => [],
                ];
            }

            $playerScores[$key]['Total_Goals'] += 1; // Increment goal count
            $playerScores[$key]['Scores'][] = [
                'HomeScore_ID' => $homeScore->HomeScore_ID,
                'ScoreTime' => $homeScore->ScoreTime
            ];
        }

        // Update MatchSummary table with the calculated Total_Goals
        foreach ($playerScores as $key => $scoreData) {
            $isPlayer = isset($scoreData['Player_ID']);
            $matchSummary = MatchSummary::where('Session_ID', $sessionId)
                ->where($isPlayer ? 'Player_ID' : 'ManualPlayer_ID', $key)
                ->first();

            if ($matchSummary) {
                $matchSummary->update(['Total_Goals' => $scoreData['Total_Goals']]);
            } else {
                // If match summary does not exist, create a new one
                MatchSummary::create([
                    'Session_ID' => $sessionId,
                    'Player_ID' => $isPlayer ? $key : null,
                    'ManualPlayer_ID' => $isPlayer ? null : $key,
                    'Total_Goals' => $scoreData['Total_Goals'],
                    'Total_Assists' => 0, // Default value, you can update this based on your logic
                    'Total_Duration' => '00:00:00', // Default value, you can update this based on your logic
                ]);
            }
        }

        return response()->json([
            'Session_Total_Goals' => $sessionTotalGoals,
            'Session_ID' => $sessionId,
            'home_scores' => array_values($playerScores),
        ], 200);
    }

    public function calculateSessionTotalGoals($sessionId)
    {
        return HomeScore::where('Session_ID', $sessionId)->count();
    }

    public function getAssistsBySession($sessionId)
{
    // Fetch all home scores for the given Session_ID
    $homeScores = HomeScore::where('Session_ID', $sessionId)->get();

    // Check if there are any home scores
    if ($homeScores->isEmpty()) {
        return response()->json(['message' => 'No records found for the given Session_ID'], 404);
    }

    // Prepare the response structure
    $homeAssists = [];

    foreach ($homeScores as $homeScore) {
        if ($homeScore->Player_ID) {
            $key = 'Player_ID_' . $homeScore->Player_ID;
            if (!isset($homeAssists[$key])) {
                $homeAssists[$key] = [
                    'Player_ID' => $homeScore->Player_ID,
                    'TotalAssists' => 0,
                    'HomeAssists' => []
                ];
            }
            if ($homeScore->HomeAssist_ID) {
                $homeAssists[$key]['HomeAssists'][] = $homeScore->HomeAssist_ID;
                $homeAssists[$key]['TotalAssists']++;
            }
        } elseif ($homeScore->ManualPlayer_ID) {
            $key = 'ManualPlayer_ID_' . $homeScore->ManualPlayer_ID;
            if (!isset($homeAssists[$key])) {
                $homeAssists[$key] = [
                    'ManualPlayer_ID' => $homeScore->ManualPlayer_ID,
                    'TotalAssists' => 0,
                    'HomeAssists' => []
                ];
            }
            if ($homeScore->HomeAssist_ID) {
                $homeAssists[$key]['HomeAssists'][] = $homeScore->HomeAssist_ID;
                $homeAssists[$key]['TotalAssists']++;
            }
        }
    }

    // Remove keys and reindex the array
    $homeAssists = array_values($homeAssists);

    // Return the response
    return response()->json([
        'Session_ID' => $sessionId,
        'Assists' => $homeAssists,
    ], 200);
}


}
