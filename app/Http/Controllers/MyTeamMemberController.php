<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\Request;

class MyTeamMemberController extends Controller
{
    // Display a listing of the team members
    public function index()
    {
        $teamMembers = Player::with(['team', 'playerInfo', 'primaryPosition', 'secondaryPosition'])->get();
        return response()->json($teamMembers);
    }

    // Store a new team member
    public function store(Request $request)
    {
        $validated = $request->validate([
            'PlayerInfo_ID' => 'required|exists:player_infos,PlayerInfo_ID',
            'Team_ID' => 'required|exists:teams,Team_ID',
            'PrimaryPosition_ID' => 'required|exists:primary_positions,PrimaryPosition_ID',
            'SecondaryPosition_ID' => 'required|exists:secondary_positions,SecondaryPosition_ID'
        ]);

        $player = Player::create($validated);
        return response()->json($player, 201);
    }

    // Display the specified team member
    public function show($id)
    {
        $player = Player::with(['team', 'playerInfo', 'primaryPosition', 'secondaryPosition'])->findOrFail($id);
        return response()->json($player);
    }

    // Update the specified team member
    public function update(Request $request, $id)
    {
        $player = Player::findOrFail($id);

        $validated = $request->validate([
            'PrimaryPosition_ID' => 'exists:primary_positions,PrimaryPosition_ID',
            'SecondaryPosition_ID' => 'exists:secondary_positions,SecondaryPosition_ID'
        ]);

        $player->update($validated);
        return response()->json($player);
    }

    // Remove the specified team member
    public function destroy($id)
    {
        $player = Player::findOrFail($id);
        $player->delete();
        return response()->json(null, 204);
    }
}
