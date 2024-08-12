<?php

namespace App\Http\Controllers;

use App\Models\ManualPlayer;
use Illuminate\Http\Request;

class ManualPlayerController extends Controller
{
    
    public function index()
    {
        $manualPlayers = ManualPlayer::all();
        return response()->json($manualPlayers);
    }

    
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'ManualPlayer_Name' => 'required|string|max:255',
            'PrimaryPosition_ID' => 'required|integer|exists:PrimaryPosition,PrimaryPosition_ID',
            'SecondaryPosition_ID' => 'required|integer|exists:SecondaryPosition,SecondaryPosition_ID',
            'Session_ID' => 'required|integer|exists:SessionGame,Session_ID',
        ]);

        $manualPlayer = ManualPlayer::create($validatedData);
        return response()->json($manualPlayer, 201);
    }

    
    public function show(ManualPlayer $manualPlayer)
    {
        return response()->json($manualPlayer);
    }

    
    public function update(Request $request, ManualPlayer $manualPlayer)
    {
        $validatedData = $request->validate([
            'ManualPlayer_Name' => 'sometimes|string|max:255',
            'PrimaryPosition_ID' => 'sometimes|integer|exists:PrimaryPosition,PrimaryPosition_ID',
            'SecondaryPosition_ID' => 'sometimes|integer|exists:SecondaryPosition,SecondaryPosition_ID',
        ]);

        $manualPlayer->update($validatedData);
        return response()->json($manualPlayer);
    }

    
    public function destroy($id)
    {
        // Find the manual player by ID
        $manualPlayer = ManualPlayer::findOrFail($id);

        // Delete related home scores
        $manualPlayer->homeScores()->delete();

        // Delete related match summaries
        $manualPlayer->matchSummaries()->delete();

        // Delete related substitutions
        $manualPlayer->substitutions()->delete();

        // Delete related home assists
        $manualPlayer->homeAssists()->delete();

        // Delete the manual player record from the database
        $manualPlayer->delete();

        // Return a successful response
        return response()->json(['message' => 'Manual player and related records deleted successfully'], 200);
    }


}
