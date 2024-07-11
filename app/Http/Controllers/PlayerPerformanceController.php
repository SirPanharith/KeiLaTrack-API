<?php

namespace App\Http\Controllers;

use App\Models\PlayerPerformance;
use Illuminate\Http\Request;

class PlayerPerformanceController extends Controller
{
    // Display a listing of performances
    public function index()
    {
        $performances = PlayerPerformance::with('player')->get();
        return response()->json($performances);
    }

    // Store a new performance
    public function store(Request $request)
    {
        $validated = $request->validate([
            'Player_ID' => 'required|exists:Player,Player_ID',
            'Player_Duration' => 'required|date_format:H:i:s',
            'Goals' => 'required|integer',
            'Assist' => 'required|integer',
        ]);

        $performance = PlayerPerformance::create($validated);
        return response()->json($performance, 201);
    }

    // Display the specified performance
    public function show($id)
    {
        $performance = PlayerPerformance::with('player')->findOrFail($id);
        return response()->json($performance);
    }

    // Update the specified performance
    public function update(Request $request, $id)
    {
        $performance = PlayerPerformance::findOrFail($id);
        $validated = $request->validate([
            'Player_Duration' => 'date_format:H:i:s',
            'Goals' => 'integer',
            'Assist' => 'integer',
        ]);

        $performance->update($validated);
        return response()->json($performance);
    }

    // Remove the specified performance
    public function destroy($id)
    {
        $performance = PlayerPerformance::findOrFail($id);
        $performance->delete();
        return response()->json(null, 204);
    }
}
