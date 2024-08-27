<?php

namespace App\Http\Controllers;

use App\Mail\SimpleTestEmail;
use App\Models\TeamInvitation;
use App\Models\PlayerInfo;
use App\Models\Player;
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
            'Team_ID' => 'required|exists:Team,Team_ID',
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
            'Team_ID' => 'exists:Team,Team_ID',
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
        'Team_ID' => 'required|exists:Team,Team_ID',
        'Player_Email' => 'required|email|exists:PlayerInfo,Player_Email',
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

    // Manually set the base URL to 0.0.0.0:8000
    $baseUrl = 'http://127.0.0.1:8000';
    // Generate the unique link
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



    public function showInvitationForm($token)
    {
        // Validate the token
        $invitation = TeamInvitation::where('token', $token)->first();

        if (!$invitation) {
            return redirect('/')->with('error', 'Invalid invitation link.');
        }

        // Fetch the player info
        $player = PlayerInfo::where('PlayerInfo_ID', $invitation->PlayerInfo_ID)->first();

        // Fetch the team details
        $team = $invitation->team;

        // Show the form
        return view('invite', [
            'email' => $player->Player_Email,
            'name' => $player->Player_Name,
            'player_id' => $player->PlayerInfo_ID,
            'team_id' => $invitation->Team_ID,
            'team_name' => $team->Team_Name ?? 'N/A', // Add the team name to the response
            'team_detail' => $team->Team_Detail ?? 'N/A', // Add the team detail to the response
            'invitation_id' => $invitation->TeamInvitation_ID, // Pass the TeamInvitation_ID to the view
            'token' => $token
        ]);
    }


    public function submitInvitationForm(Request $request, $token)
    {
        // Validate the token
        $invitation = TeamInvitation::where('token', $token)->first();

        if (!$invitation) {
            return redirect('/')->with('error', 'Invalid invitation link.');
        }

        // Validate and save the form input
        $validated = $request->validate([
            'player_id' => 'required|numeric',
            'team_id' => 'required|numeric',
            'primaryPosition' => 'required|numeric',
            'secondaryPosition' => 'nullable|numeric',
        ]);

        // Save the player's information to the database
        Player::create([
            'PlayerInfo_ID' => $validated['player_id'],
            'Team_ID' => $validated['team_id'],
            'TeamInvitation_ID' => $invitation->TeamInvitation_ID, // Assign the TeamInvitation_ID
            'PrimaryPosition_ID' => $validated['primaryPosition'],
            'SecondaryPosition_ID' => $validated['secondaryPosition'],
        ]);

        // Optionally, delete the invitation or mark it as used
        $invitation->delete();

        return redirect('/team-invitation-success')->with('success', 'You have successfully joined the team!');
    }

    public function showSpecificInvitation($id)
    {
        // Retrieve the specific team invitation by ID
        $teamInvitation = TeamInvitation::with('team', 'response', 'playerInfo')->findOrFail($id);

        // Return the team invitation data as JSON
        return response()->json($teamInvitation, 200);
    }

    
}
