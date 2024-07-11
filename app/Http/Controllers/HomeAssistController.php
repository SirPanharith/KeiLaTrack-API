<?php

namespace App\Http\Controllers;

use App\Models\HomeAssist;
use Illuminate\Http\Request;

class HomeAssistController extends Controller
{
    // Display a listing of the resource.
    public function index()
    {
        // Get all HomeAssists with associated ManualPlayer and HomeScores
        $homeAssists = HomeAssist::with(['manualPlayer', 'homeScores'])->get();
        return response()->json($homeAssists);
    }

    // Store a newly created resource in storage.
    public function store(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'Player_ID' => 'nullable|exists:player,Player_ID',
            'ManualPlayer_ID' => 'nullable|exists:ManualPlayer,ManualPlayer_ID',
        ]);

        // Create the HomeAssist record
        $homeAssist = HomeAssist::create($validated);

        // Return the created HomeAssist with a 201 Created status
        return response()->json($homeAssist, 201);
    }

    // Display the specified resource.
    public function show($id)
    {
        // Find the HomeAssist by ID with associated ManualPlayer and HomeScores
        $homeAssist = HomeAssist::with(['manualPlayer', 'homeScores'])->findOrFail($id);
        return response()->json($homeAssist);
    }

    // Update the specified resource in storage.
    public function update(Request $request, $id)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'Player_ID' => 'nullable|exists:players,Player_ID',
            'ManualPlayer_ID' => 'nullable|exists:manual_players,ManualPlayer_ID',
        ]);

        // Find the HomeAssist by ID and update it
        $homeAssist = HomeAssist::findOrFail($id);
        $homeAssist->update($validated);

        // Return the updated HomeAssist
        return response()->json($homeAssist);
    }

    // Remove the specified resource from storage.
    public function destroy($id)
    {
        // Find the HomeAssist by ID and delete it
        $homeAssist = HomeAssist::findOrFail($id);
        $homeAssist->delete();

        // Return a 204 No Content response
        return response()->json(null, 204);
    }

    // New method to get HomeAssists by Session_ID
    public function getBySession($sessionId)
{
    // Get all HomeAssists with associated Player and ManualPlayer where Player_ID or ManualPlayer_ID is not null
    $homeAssists = HomeAssist::with(['player.playerInfo', 'manualPlayer', 'homeScores'])
        ->whereHas('homeScores', function ($query) use ($sessionId) {
            $query->where('Session_ID', $sessionId);
        })
        ->where(function ($query) {
            $query->whereNotNull('Player_ID')
                  ->orWhereNotNull('ManualPlayer_ID');
        })
        ->get();

    if ($homeAssists->isEmpty()) {
        return response()->json(['message' => 'No home assists found for the given Session_ID'], 404);
    }

    $playerAssists = [];

    foreach ($homeAssists as $homeAssist) {
        $key = $homeAssist->Player_ID ?? $homeAssist->ManualPlayer_ID;
        $isPlayer = isset($homeAssist->Player_ID);
        $name = $isPlayer ? optional($homeAssist->player->playerInfo)->Player_Name : optional($homeAssist->manualPlayer)->ManualPlayer_Name;

        if (!isset($playerAssists[$key])) {
            $playerAssists[$key] = [
                'Player_ID' => $isPlayer ? $key : null,
                'ManualPlayer_ID' => $isPlayer ? null : $key,
                'Player_Name' => $isPlayer ? $name : null,
                'ManualPlayer_Name' => $isPlayer ? null : $name,
                'Total_Assists' => 0,
                'Assists' => [],
            ];
        }

        $playerAssists[$key]['Total_Assists'] += 1; // Increment assist count
        $playerAssists[$key]['Assists'][] = [
            'HomeAssist_ID' => $homeAssist->HomeAssist_ID,
        ];
    }

    return response()->json([
        'Session_ID' => $sessionId,
        'home_assists' => array_values($playerAssists),
    ], 200);
}


    
}
