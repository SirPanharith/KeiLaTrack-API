<?php

namespace App\Http\Controllers;

use App\Mail\SimpleTestEmail;
use App\Models\TeamInvitation;
use App\Models\PlayerInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Mail;

class TeamInvitationController extends Controller
{
    public function index()
    {
        $teamInvitations = TeamInvitation::with('team', 'response')->get();
        return response()->json($teamInvitations);
    }

    public function store(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'Team_ID' => 'required|exists:teams,Team_ID',
            'PlayerInfo_ID' => 'required|exists:playerinfo,PlayerInfo_ID',
        ]);

        // Create the team invitation
        $teamInvitation = TeamInvitation::create($validated);

        // Return the created team invitation with a 201 Created status
        return response()->json($teamInvitation, 201);
    }

    public function show($id)
    {
        $teamInvitation = TeamInvitation::with('team', 'response')->findOrFail($id);
        return response()->json($teamInvitation);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'Team_ID' => 'exists:teams,Team_ID',
            'Invitation_Info' => 'string',
            'Player_ID' => 'exists:players,Player_ID',
            'Response_ID' => 'exists:responses,Response_ID'
        ]);

        $teamInvitation = TeamInvitation::findOrFail($id);
        $teamInvitation->update($validated);
        return response()->json($teamInvitation);
    }

    public function destroy($id)
    {
        $teamInvitation = TeamInvitation::findOrFail($id);
        $teamInvitation->delete();
        return response()->json(null, 204);
    }

    public function storeWithEmail(Request $request)
{
    // Validate the incoming request
    $validated = $request->validate([
        'Team_ID' => 'required|exists:team,Team_ID',
        'Player_Email' => 'required|email|exists:playerinfo,Player_Email',
    ]);

    // Find the PlayerInfo_ID using the Player_Email
    $playerInfo = PlayerInfo::where('Player_Email', $validated['Player_Email'])->firstOrFail();

    // Generate a unique token for the invitation
    $invitationToken = Str::random(32);

    // Create the team invitation using the found PlayerInfo_ID and generated token
    $teamInvitation = TeamInvitation::create([
        'Team_ID' => $validated['Team_ID'],
        'PlayerInfo_ID' => $playerInfo->PlayerInfo_ID,
        'token' => $invitationToken,
    ]);

    // Generate the unique link with the base URL set to 0.0.0.0:8000
    $baseUrl = 'http://0.0.0.0:8000';
    $link = $baseUrl . '/accept-invitation/' . $invitationToken;

    // Send the email
    Mail::to($playerInfo->Player_Email)->send(new SimpleTestEmail($link));

    // Return the created team invitation with Player_Email and a 201 Created status
    return response()->json([
        'TeamInvitation_ID' => $teamInvitation->TeamInvitation_ID,
        'Team_ID' => $teamInvitation->Team_ID,
        'PlayerInfo_ID' => $teamInvitation->PlayerInfo_ID,
        'Player_Email' => $playerInfo->Player_Email,
        'token' => $invitationToken,
        'created_at' => $teamInvitation->created_at,
        'updated_at' => $teamInvitation->updated_at,
    ], 201);
}

}
