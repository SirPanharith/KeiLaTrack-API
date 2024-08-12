<?php

namespace App\Http\Controllers;

use App\Models\PlayerNote;
use Illuminate\Http\Request;

class PlayerNoteController extends Controller
{
    // Display a listing of player notes
    public function index()
    {
        $playerNotes = PlayerNote::with(['player', 'sessionGame'])->get();
        return response()->json($playerNotes);
    }

    // Store a newly created player note
    public function store(Request $request)
    {
        $validated = $request->validate([
            'Session_ID' => 'required|exists:SessionGame,Session_ID',
            'Player_ID' => 'required|exists:Player,Player_ID',
            'PlayerNote' => 'required|string',
        ]);

        $playerNote = PlayerNote::create($validated);
        return response()->json($playerNote, 201);
    }

    // Display the specified player note
    public function show($id)
    {
        $playerNote = PlayerNote::with(['player', 'sessionGame'])->findOrFail($id);
        return response()->json($playerNote);
    }

    // Update the specified player note
    public function update(Request $request, $id)
    {
        $playerNote = PlayerNote::findOrFail($id);

        $validated = $request->validate([
            'Session_ID' => 'required|exists:SessionGame,Session_ID',
            'Player_ID' => 'required|exists:Player,Player_ID',
            'PlayerNote' => 'required|string',
        ]);

        $playerNote->update($validated);
        return response()->json($playerNote);
    }

    // Remove the specified player note
    public function destroy($id)
    {
        $playerNote = PlayerNote::findOrFail($id);
        $playerNote->delete();
        return response()->json(null, 204);
    }

    // Get player notes by player
    public function getNotesByPlayer($playerId)
    {
        $playerNotes = PlayerNote::where('Player_ID', $playerId)->with('sessionGame')->get();
        return response()->json($playerNotes);
    }

    // Get player notes by session
    public function getNotesBySession($sessionId)
    {
        $playerNotes = PlayerNote::where('Session_ID', $sessionId)->with('player')->get();
        return response()->json($playerNotes);
    }

    public function updateNoteBySessionAndPlayer(Request $request)
    {
        $validated = $request->validate([
            'Session_ID' => 'required|exists:SessionGame,Session_ID',
            'Player_ID' => 'required|exists:Player,Player_ID',
            'PlayerNote' => 'required|string',
        ]);

        $playerNote = PlayerNote::where('Session_ID', $request->Session_ID)
            ->where('Player_ID', $request->Player_ID)
            ->firstOrFail();

        $playerNote->update([
            'PlayerNote' => $request->PlayerNote,
        ]);

        return response()->json($playerNote);
    }

    public function getNoteBySessionAndPlayer($sessionId, $playerId)
{
    $playerNote = PlayerNote::where('Session_ID', $sessionId)
                            ->where('Player_ID', $playerId)
                            ->first();

    if ($playerNote) {
        return response()->json($playerNote);
    } else {
        return response()->json(['PlayerNote' => '(Add your note)']);
    }
}

}
