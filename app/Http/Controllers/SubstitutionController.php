<?php

namespace App\Http\Controllers;

use App\Models\Substitution;
use App\Models\MatchSummary;
use Illuminate\Http\Request;

class SubstitutionController extends Controller
{
    // Display a listing of the substitutions
    public function index()
    {
        $substitutions = Substitution::all();
        return response()->json($substitutions);
    }

    // Show the form for creating a new substitution
    public function create()
    {
        // This would typically return a view for creating a substitution
    }

    // Store a newly created substitution in storage
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'Session_ID' => 'required|exists:SessionGame,Session_ID',
            'Player_ID' => 'nullable|exists:Player,Player_ID',
            'ManualPlayer_ID' => 'nullable|exists:ManualPlayer,ManualPlayer_ID',
            'In' => 'required|date_format:H:i:s',
            'Out' => 'required|date_format:H:i:s',
            'Duration' => 'required|date_format:H:i:s',
        ]);

        $substitution = Substitution::create($validatedData);

        return response()->json($substitution, 201);
    }

    // Display the specified substitution
    public function show($id)
    {
        $substitution = Substitution::findOrFail($id);
        return response()->json($substitution);
    }

    // Show the form for editing the specified substitution
    public function edit($id)
    {
        // This would typically return a view for editing a substitution
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'In' => 'date_format:H:i:s',
            'Out' => 'date_format:H:i:s',
            'Duration' => 'date_format:H:i:s',
        ]);

        $substitution = Substitution::findOrFail($id);
        $substitution->update($validatedData);

        return response()->json($substitution);
    }


    // Remove the specified substitution from storage
    public function destroy($id)
    {
        $substitution = Substitution::findOrFail($id);
        $substitution->delete();

        return response()->json(null, 204);
    }

    // Display the substitutions based on Session_ID
    public function getBySessionId($sessionId)
    {
        $substitutions = Substitution::where('Session_ID', $sessionId)->get();
        return response()->json($substitutions);
    }

    // Display the list of Player_ID and Player_Name based on Session_ID
    public function getPlayerDetailsBySessionId($sessionId)
    {
        $substitutions = Substitution::where('Session_ID', $sessionId)
            ->with(['player.playerInfo', 'manualPlayer'])
            ->get();
    
        $playerDetails = [];
    
        foreach ($substitutions as $substitution) {
            if ($substitution->Player_ID) {
                $playerID = $substitution->Player_ID;
                $playerName = $substitution->player->playerInfo->Player_Name ?? 'N/A';
                $idKey = 'Player_ID';
                $nameKey = 'Player_Name';
            } elseif ($substitution->ManualPlayer_ID) {
                $playerID = $substitution->ManualPlayer_ID;
                $playerName = $substitution->manualPlayer->ManualPlayer_Name ?? 'N/A';
                $idKey = 'ManualPlayer_ID';
                $nameKey = 'ManualPlayer_Name';
            } else {
                continue;
            }
    
            if (!isset($playerDetails[$playerID])) {
                $playerDetails[$playerID] = [
                    'Player_ID' => null, // Ensure both keys exist
                    'ManualPlayer_ID' => null, // Ensure both keys exist
                    $idKey => $playerID,
                    $nameKey => $playerName,
                    'Total_Duration' => 0, // Initialize total duration
                    'Substitutions' => []
                ];
            }
    
            // Convert Duration to seconds for easy summation
            $durationParts = explode(':', $substitution->Duration);
            $durationInSeconds = ($durationParts[0] * 3600) + ($durationParts[1] * 60) + $durationParts[2];
    
            // Add the duration to the total duration
            $playerDetails[$playerID]['Total_Duration'] += $durationInSeconds;
    
            // Add the substitution details
            $playerDetails[$playerID]['Substitutions'][] = [
                'Sub_ID' => $substitution->Sub_ID,
                'In' => $substitution->In,
                'Out' => $substitution->Out,
                'Duration' => $substitution->Duration,
            ];
        }
    
        // Convert total duration back to H:i:s format and update the MatchSummary
        foreach ($playerDetails as &$player) {
            $totalSeconds = $player['Total_Duration'];
            $hours = floor($totalSeconds / 3600);
            $minutes = floor(($totalSeconds % 3600) / 60);
            $seconds = $totalSeconds % 60;
            $totalDuration = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    
            $player['Total_Duration'] = $totalDuration;
    
            // Update the MatchSummary table
            MatchSummary::where('Session_ID', $sessionId)
                ->where($idKey, $player[$idKey])
                ->update(['Total_Duration' => $totalDuration]);
        }
    
        return response()->json(array_values($playerDetails));
    }
    

}
