<?php

namespace App\Http\Controllers;

use App\Models\SessionGame;
use App\Models\Setting;
use App\Models\Player;
use App\Models\HomeScore;
use App\Models\SessionInvitation;
use App\Models\ManualPlayer;
use App\Models\PlayerInfo;
use App\Models\MatchSummary;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Mail;


class SessionGameController extends Controller
{
    // Display a listing of session games
    public function index()
    {
        $sessionGames = SessionGame::with(['team', 'settings', 'scoreBoard'])->get();
        return response()->json($sessionGames);
    }

    // Store a new session game
    public function store(Request $request)
    {
        $validated = $request->validate([
            'Session_Date' => 'required|date',
            'Session_Duration' => 'required',
            'Session_Time' => 'required',
            'Session_Location' => 'required|string',
            'Session_Note' => 'required|string',
            'Team_ID' => 'required',
            'ManualAway_Name' => 'nullable|string',
            'ManualAway_Score' => 'nullable|integer',
        ]);

        $sessionGame = SessionGame::create($validated);

        // Send invitations to all players in the team
        $players = Player::where('Team_ID', $validated['Team_ID'])->get();
        foreach ($players as $player) {
            $token = Str::random(32);
            $invitation = SessionInvitation::create([
                'Session_ID' => $sessionGame->Session_ID,
                'PlayerInfo_ID' => $player->PlayerInfo_ID,
                'token' => $token,
            ]);

            $link = url('/session-invitation/' . $token);
            $email = $player->playerInfo->Player_Email;

            Mail::to($email)->send(new \App\Mail\SessionInvitationEmail($link));
        }

        return response()->json($sessionGame, 201);
    }

    // Show the invitation details
    public function showInvitation($token)
    {
        $invitation = SessionInvitation::where('token', $token)->firstOrFail();
        $sessionGame = $invitation->sessionGame;
        $team = $sessionGame->team;

        return view('sessioninvitation', [
            'token' => $token,
            'email' => $invitation->playerInfo->Player_Email,
            'date' => $sessionGame->Session_Date,
            'time' => $sessionGame->Session_Time,
            'duration' => $sessionGame->Session_Duration,
            'location' => $sessionGame->Session_Location,
            'note' => $sessionGame->Session_Note,
            'mode' => '5 vs 5', // Adjust as necessary
            'team_name' => $team->Team_Name ?? 'N/A', // Add the team name to the response
        ]);
    }

    // Accept the invitation
    public function acceptInvitation(Request $request, $token)
    {
        $invitation = SessionInvitation::where('token', $token)->firstOrFail();
        $invitation->update(['Response_ID' => 1]); // 1 means accepted
        return redirect('/session-invitation-success')->with('success', 'You have successfully accepted the invitation.');
    }

    // Reject the invitation
    public function rejectInvitation(Request $request, $token)
    {
        $invitation = SessionInvitation::where('token', $token)->firstOrFail();
        $invitation->update(['Response_ID' => 2]); // 2 means rejected
        return redirect('/')->with('success', 'You have successfully rejected the invitation.');
    }

    // Display the specified session game
    public function show($id)
    {
        $sessionGame = SessionGame::with(['team', 'settings', 'scoreBoard', 'players.primaryPosition', 'players.secondaryPosition'])->findOrFail($id);
        
        $settings = $sessionGame->settings->map(function ($setting) {
            $S_Num = $setting->S_Num ?? 0;
            $M_Num = $setting->M_Num ?? 0;
            $D_Num = $setting->D_Num ?? 0;
            $Gk_Num = $setting->Gk_Num ?? 0;
            $TotalPlayerPerSide = $S_Num + $M_Num + $D_Num + $Gk_Num;
            
            return [
                'Setting_ID' => $setting->Setting_ID,
                'SubMode_ID' => $setting->SubMode_ID,
                'Session_ID' => $setting->Session_ID,
                'Sub_Timespace' => $setting->Sub_Timespace,
                'Divide_ID' => $setting->Divide_ID,
                'S_Num' => $S_Num,
                'M_Num' => $M_Num,
                'D_Num' => $D_Num,
                'Gk_Num' => $Gk_Num,
                'Side_ID' => $setting->Side_ID,
                'created_at' => $setting->created_at,
                'updated_at' => $setting->updated_at,
                'TotalPlayerPerSide' => $TotalPlayerPerSide,
            ];
        });

        $players = $sessionGame->players->map(function ($player) {
            return [
                'Player_ID' => $player->Player_ID,
                'Player_Name' => $player->playerInfo->Player_Name ?? 'N/A',
                'PrimaryPosition_ID' => $player->PrimaryPosition_ID,
                'SecondaryPosition_ID' => $player->SecondaryPosition_ID,
                'PrimaryPosition' => $player->primaryPosition->Position ?? 'N/A',
                'SecondaryPosition' => $player->secondaryPosition->Position ?? 'N/A',
            ];
        });

        return response()->json([
            'Session_ID' => $sessionGame->Session_ID,
            'Session_Date' => $sessionGame->Session_Date,
            'Session_Duration' => $sessionGame->Session_Duration,
            'Session_Time' => $sessionGame->Session_Time,
            'Session_Location' => $sessionGame->Session_Location,
            'Session_Note' => $sessionGame->Session_Note,
            'Team_ID' => $sessionGame->Team_ID,
            'ManualAway_Name' => $sessionGame->ManualAway_Name,
            'ManualAway_Score' => $sessionGame->ManualAway_Score,
            'created_at' => $sessionGame->created_at,
            'updated_at' => $sessionGame->updated_at,
            'team' => $sessionGame->team,
            'settings' => $settings,
            'players' => $players,
            'score_board' => $sessionGame->scoreBoard,
        ]);
    }

    // Update the specified session game
    public function update(Request $request, $id)
    {
        // Validate the request to only allow updating specific fields
        $validated = $request->validate([
            'ManualAway_Name' => 'nullable|string|max:255',
            'ManualAway_Score' => 'nullable|integer|min:0',
            'SessionStatus_ID' => 'nullable|integer|exists:SessionStatus,SessionStatus_ID',
        ]);

        // Find the session game by its ID
        $sessionGame = SessionGame::findOrFail($id);

        // Update the fields
        $sessionGame->update([
            'ManualAway_Name' => $validated['ManualAway_Name'] ?? $sessionGame->ManualAway_Name, // Update only if provided
            'ManualAway_Score' => $validated['ManualAway_Score'] ?? $sessionGame->ManualAway_Score, // Update only if provided
            'SessionStatus_ID' => $validated['SessionStatus_ID'], // Update required field
        ]);

        return response()->json($sessionGame);
    }


    // Remove the specified session game
    public function destroy($id)
    {
        try {
            // Find the session game
            $sessionGame = SessionGame::findOrFail($id);

            // Delete related records in the associated tables
            $sessionGame->settings()->delete();
            $sessionGame->scoreBoard()->delete();
            $sessionGame->sessionInvitations()->delete();
            $sessionGame->manualPlayers()->delete();
            $sessionGame->homeScores()->delete();
            $sessionGame->awayScores()->delete();
            $sessionGame->substitutions()->delete();
            $sessionGame->matchSummaries()->delete();
            $sessionGame->playerNotes()->delete();

            // Finally, delete the session game
            $sessionGame->delete();

            return response()->json(['message' => 'Session game deleted successfully'], 204);
        } catch (\Exception $e) {
            // Handle the exception and return an error response
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function getSessionGamesByPlayer($playerId)
{
    $sessionGames = SessionGame::with(['team', 'settings', 'scoreBoard', 'players.primary_position', 'players.secondary_position'])
        ->whereHas('players', function ($query) use ($playerId) {
            $query->where('Player_ID', $playerId);
        })
        ->get();
    
    $result = $sessionGames->map(function ($sessionGame) {
        $settings = $sessionGame->settings->map(function ($setting) {
            $S_Num = $setting->S_Num ?? 0;
            $M_Num = $setting->M_Num ?? 0;
            $D_Num = $setting->D_Num ?? 0;
            $Gk_Num = $setting->Gk_Num ?? 0;
            $TotalPlayerPerSide = $S_Num + $M_Num + $D_Num + $Gk_Num;
            
            return [
                'Setting_ID' => $setting->Setting_ID,
                'SubMode_ID' => $setting->SubMode_ID,
                'Session_ID' => $setting->Session_ID,
                'Sub_Timespace' => $setting->Sub_Timespace,
                'Divide_ID' => $setting->Divide_ID,
                'S_Num' => $S_Num,
                'M_Num' => $M_Num,
                'D_Num' => $D_Num,
                'Gk_Num' => $Gk_Num,
                'Side_ID' => $setting->Side_ID,
                'created_at' => $setting->created_at,
                'updated_at' => $setting->updated_at,
                'TotalPlayerPerSide' => $TotalPlayerPerSide,
            ];
        });

        $players = $sessionGame->players->map(function ($player) {
            return [
                'Player_ID' => $player->Player_ID,
                'Player_Name' => $player->player_info->Player_Name ?? 'N/A',
                'PrimaryPosition_ID' => $player->PrimaryPosition_ID,
                'SecondaryPosition_ID' => $player->SecondaryPosition_ID,
                'PrimaryPosition' => $player->primary_position->Position ?? 'N/A',
                'SecondaryPosition' => $player->secondary_position->Position ?? 'N/A',
            ];
        });

        return [
            'Session_ID' => $sessionGame->Session_ID,
            'Session_Date' => $sessionGame->Session_Date,
            'Session_Duration' => $sessionGame->Session_Duration,
            'Session_Time' => $sessionGame->Session_Time,
            'Session_Location' => $sessionGame->Session_Location,
            'Session_Note' => $sessionGame->Session_Note,
            'Team_ID' => $sessionGame->team->Team_ID ?? 'N/A',
            'Team_Name' => $sessionGame->team->Team_Name ?? 'N/A',
            'Settings' => $settings,
            'ScoreBoard' => $sessionGame->scoreBoard->map(function ($scoreBoard) {
                return [
                    'ScoreBoard_ID' => $scoreBoard->ScoreBoard_ID,
                    'Session_ID' => $scoreBoard->Session_ID,
                    'Team1_Score' => $scoreBoard->Team1_Score,
                    'Team2_Score' => $scoreBoard->Team2_Score,
                    'created_at' => $scoreBoard->created_at,
                    'updated_at' => $scoreBoard->updated_at,
                ];
            }),
            'Players' => $players,
        ];
    });

    return response()->json($result);
}


    public function showspecificsession($id)
    {
        $sessionGame = SessionGame::with(['team', 'settings', 'scoreBoard', 'players.playerInfo', 'players.primaryPosition', 'players.secondaryPosition'])
                        ->where('Session_ID', $id)
                        ->firstOrFail();
    
        return response()->json($sessionGame);
    }

    public function getSessionDateTimeByTeam($teamId)
    {
        try {
            $sessionGames = SessionGame::where('Team_ID', $teamId)
                ->get(['Session_ID', 'Team_ID', 'Session_Date', 'Session_Time']);
        } catch (\Exception $e) {
            // Handle or log the exception
            return response()->json(['error' => $e->getMessage()], 500);
        }
    
        return response()->json($sessionGames);
    }
    
    // New method to get session details by Session_ID
    public function getInvitationSession($sessionId)
    {
        try {
            // Get session game by session ID
            $sessionGame = SessionGame::with([
                'team',
                'players.playerInfo',
                'players.primaryPosition',
                'players.secondaryPosition'
            ])->where('Session_ID', $sessionId)->first();
    
            // Check if session game exists
            if (!$sessionGame) {
                return response()->json(['error' => 'Session game not found'], 404);
            }
    
            // Get the team associated with the session game
            $team = $sessionGame->team;
    
            // Check if team exists
            if (!$team) {
                return response()->json(['error' => 'Team not found'], 404);
            }
    
            // Get all players associated with the team
            $playersList = $team->players()->with(['playerInfo', 'primaryPosition', 'secondaryPosition'])->get()->map(function ($player) use ($sessionId) {
                // Get the SessionInvitation for this player and session
                $sessionInvitation = SessionInvitation::where('Session_ID', $sessionId)
                    ->where('PlayerInfo_ID', $player->PlayerInfo_ID)
                    ->with('response')
                    ->first();
    
                // Only include players with Response_ID = 1
                if ($sessionInvitation && $sessionInvitation->Response_ID == 1) {
                    return [
                        'Player_ID' => $player->Player_ID,
                        'PlayerInfo_ID' => $player->PlayerInfo->PlayerInfo_ID,
                        'Player_Name' => $player->PlayerInfo->Player_Name ?? 'N/A',
                        'Player_Email' => $player->PlayerInfo->Player_Email ?? 'N/A',
                        'Player_Image' => $player->PlayerInfo->PlayerInfo_Image ?? 'N/A',
                        'PrimaryPosition_ID' => $player->PrimaryPosition_ID,
                        'PrimaryPosition_Name' => $player->PrimaryPosition->Position ?? 'N/A',
                        'SecondaryPosition_ID' => $player->SecondaryPosition_ID,
                        'SecondaryPosition_Name' => $player->SecondaryPosition->Position ?? 'N/A',
                        'Response_ID' => $sessionInvitation->Response_ID,
                        'Response' => $sessionInvitation->Response->Response,
                    ];
                }
    
                return null; // Exclude players without the correct Response_ID
            })->filter(); // Remove null values
    
            // Get all manual players associated with the session
            $manualPlayersList = ManualPlayer::where('Session_ID', $sessionId)
                ->with(['primaryPosition', 'secondaryPosition'])
                ->get()->map(function ($manualPlayer) {
                    return [
                        'ManualPlayer_ID' => $manualPlayer->ManualPlayer_ID,
                        'ManualPlayer_Name' => $manualPlayer->ManualPlayer_Name,
                        'PrimaryPosition_ID' => $manualPlayer->PrimaryPosition_ID,
                        'PrimaryPosition_Name' => $manualPlayer->primaryPosition->Position ?? 'N/A',
                        'SecondaryPosition_ID' => $manualPlayer->SecondaryPosition_ID,
                        'SecondaryPosition_Name' => $manualPlayer->secondaryPosition->Position ?? 'N/A',
                    ];
                });
    
            // Count the total number of manual players
            $totalManualPlayers = $manualPlayersList->count();
    
            // Count the number of players with the response "Accepted"
            $acceptedCount = $playersList->filter(function ($player) {
                return $player['Response'] === 'Accepted';
            })->count();
    
            // Calculate the total number of players
            $totalPlayers = $totalManualPlayers + $acceptedCount;
    
            $response = [
                'Session_ID' => $sessionGame->Session_ID,
                'Session_Date' => $sessionGame->Session_Date,
                'Session_Time' => $sessionGame->Session_Time,
                'Session_Location' => $sessionGame->Session_Location,
                'Team_Name' => $team->Team_Name ?? 'N/A',
                'ManualAway_Name' => $sessionGame->ManualAway_Name,
                'ManualAway_Score' => $sessionGame->ManualAway_Score,
                'Players' => $playersList->values()->all(), // Re-index the array
                'ManualPlayers' => $manualPlayersList->values()->all(), // Include manual players
                'Accepted_Count' => $acceptedCount,
                'Total_Manual_Players' => $totalManualPlayers, // Include the total number of manual players
                'Total_Players' => $totalPlayers, // Include the total number of players
            ];
    
        } catch (\Exception $e) {
            // Handle or log the exception
            return response()->json(['error' => $e->getMessage()], 500);
        }
    
        return response()->json($response);
    }

    // One Side Game Play
    public function getPlayersBySetting($settingId)
    {
        try {
            // Find the setting by ID
            $setting = Setting::with(['subMode', 'divide', 'side'])->findOrFail($settingId);

            // Debugging: Print the Gk_Num value
            error_log('Gk_Num from database: ' . $setting->Gk_Num);

            // Get the session game associated with the setting
            $sessionGame = $setting->sessionGame;

            if (!$sessionGame) {
                return response()->json(['error' => 'Session game not found'], 404);
            }

            // Get the team name associated with the session
            $teamName = $sessionGame->team->Team_Name ?? 'N/A';

            // Get players associated with the session
            $playersList = $sessionGame->sessionInvitations()->where('Response_ID', 1)->with(['player.playerInfo', 'player.primaryPosition', 'player.secondaryPosition'])->get()->map(function ($invitation) {
                $player = $invitation->player;
                return [
                    'Player_ID' => $player->Player_ID, // Include Player_ID
                    'Player_Name' => $player->playerInfo->Player_Name ?? 'N/A',
                    'PrimaryPosition_ID' => $player->PrimaryPosition_ID,
                    'SecondaryPosition_ID' => $player->SecondaryPosition_ID,
                ];
            });

            // Get manual players associated with the session
            $manualPlayersList = $sessionGame->manualPlayers()->with(['primaryPosition', 'secondaryPosition'])->get()->map(function ($manualPlayer) {
                return [
                    'ManualPlayer_ID' => $manualPlayer->ManualPlayer_ID, // Include ManualPlayer_ID
                    'ManualPlayer_Name' => $manualPlayer->ManualPlayer_Name,
                    'PrimaryPosition_ID' => $manualPlayer->PrimaryPosition_ID,
                    'SecondaryPosition_ID' => $manualPlayer->SecondaryPosition_ID,
                ];
            });

            // Ensure numeric values are not null
            $S_Num = $setting->S_Num ?? 0;
            $M_Num = $setting->M_Num ?? 0;
            $D_Num = $setting->D_Num ?? 0;
            $Gk_Num = $setting->Gk_Num ?? 0;

            // Debugging: Print the Gk_Num value after assignment
            error_log('Gk_Num after assignment: ' . $Gk_Num);

            // Calculate total selected players
            $totalSelectedPlayers = $S_Num + $M_Num + $D_Num + $Gk_Num;

            // Calculate the total number of players in the session
            $totalPlayers = $playersList->count() + $manualPlayersList->count();

            $response = [
                'Session_ID' => $sessionGame->Session_ID,
                'Setting_ID' => $setting->Setting_ID,
                'Team_Name' => $teamName, // Add the team name to the response
                'ManualAway_Name' => $sessionGame->ManualAway_Name,
                'ManualAway_Score' => $sessionGame->ManualAway_Score,
                'SubMode' => $setting->subMode->SubMode ?? 'N/A',
                'Sub_Timespace' => $setting->Sub_Timespace,
                'Divide_ID' => $setting->Divide_ID,
                'Divide' => $setting->divide->Divide ?? 'N/A',
                'Side' => $setting->side->Side ?? 'N/A',
                'S_Num' => $S_Num,
                'M_Num' => $M_Num,
                'D_Num' => $D_Num,
                'Gk_Num' => $Gk_Num,
                'Total_Selected_Players' => $totalSelectedPlayers,
                'Total_Players' => $totalPlayers, // Add the total number of players in the session
                'Players' => $playersList->values()->all(), // Ensure the array is re-indexed
                'ManualPlayers' => $manualPlayersList->values()->all(), // Ensure the array is re-indexed
            ];

        } catch (\Exception $e) {
            // Handle or log the exception
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json($response);
    }

    public function getSessionsByTeamId($teamId)
    {
        try {
            // Get all session games for the given Team_ID and SessionStatus_ID = 1
            $sessionGames = SessionGame::with(['team', 'settings', 'scoreBoard'])
                ->where('Team_ID', $teamId)
                ->where('SessionStatus_ID', 1) // Filter by SessionStatus_ID = 1
                ->get();

            // Check if there are any session games
            if ($sessionGames->isEmpty()) {
                return response()->json(['message' => 'No sessions found for this team with status 1'], 404);
            }

            // Create an instance of HomeScoreController to use the calculateSessionTotalGoals method
            $homeScoreController = new HomeScoreController();

            // Format the session games data
            $result = $sessionGames->map(function ($sessionGame) use ($homeScoreController) {
                $sessionTotalGoals = $homeScoreController->calculateSessionTotalGoals($sessionGame->Session_ID);

                return [
                    'Session_ID' => $sessionGame->Session_ID,
                    'Session_Date' => $sessionGame->Session_Date,
                    'Session_Duration' => $sessionGame->Session_Duration,
                    'Session_Time' => $sessionGame->Session_Time,
                    'Session_Location' => $sessionGame->Session_Location,
                    'Session_Note' => $sessionGame->Session_Note,
                    'Team_ID' => $sessionGame->team->Team_ID,
                    'Team_Name' => $sessionGame->team->Team_Name ?? 'N/A',
                    'SessionStatus_ID' => $sessionGame->SessionStatus_ID,
                    'Session_Total_Goals' => $sessionTotalGoals,
                    'ManualAway_Name' => $sessionGame->ManualAway_Name,
                    'ManualAway_Score' => $sessionGame->ManualAway_Score,
                    'Settings' => $sessionGame->settings,
                    'ScoreBoard' => $sessionGame->scoreBoard,
                ];
            });

            return response()->json($result, 200);
        } catch (\Exception $e) {
            // Handle or log the exception
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getSessionsByTeamIdWithStatus2($teamId)
    {
        try {
            // Get all session games for the given Team_ID and SessionStatus_ID = 2
            $sessionGames = SessionGame::with(['team', 'settings', 'scoreBoard'])
                ->where('Team_ID', $teamId)
                ->where('SessionStatus_ID', 2) // Filter by SessionStatus_ID = 2
                ->get();

            // Check if there are any session games
            if ($sessionGames->isEmpty()) {
                return response()->json(['message' => 'No sessions found for this team with status 2'], 404);
            }

            // Create an instance of HomeScoreController to use the calculateSessionTotalGoals method
            $homeScoreController = new HomeScoreController();

            // Format the session games data
            $result = $sessionGames->map(function ($sessionGame) use ($homeScoreController) {
                $sessionTotalGoals = $homeScoreController->calculateSessionTotalGoals($sessionGame->Session_ID);

                return [
                    'Session_ID' => $sessionGame->Session_ID,
                    'Session_Date' => $sessionGame->Session_Date,
                    'Session_Duration' => $sessionGame->Session_Duration,
                    'Session_Time' => $sessionGame->Session_Time,
                    'Session_Location' => $sessionGame->Session_Location,
                    'Session_Note' => $sessionGame->Session_Note,
                    'Team_ID' => $sessionGame->team->Team_ID,
                    'Team_Name' => $sessionGame->team->Team_Name ?? 'N/A',
                    'SessionStatus_ID' => $sessionGame->SessionStatus_ID,
                    'Session_Total_Goals' => $sessionTotalGoals,
                    'ManualAway_Name' => $sessionGame->ManualAway_Name,
                    'ManualAway_Score' => $sessionGame->ManualAway_Score,
                    'Settings' => $sessionGame->settings,
                    'ScoreBoard' => $sessionGame->scoreBoard,
                ];
            });

            return response()->json($result, 200);
        } catch (\Exception $e) {
            // Handle or log the exception
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }



    public function getSessionGameBySessionAndPlayer($sessionId, $playerId)
{
    try {
        // Fetch the current session game by Session_ID
        $sessionGame = SessionGame::with(['settings', 'players.primaryPosition', 'players.secondaryPosition', 'team'])
                                    ->where('Session_ID', $sessionId)
                                    ->firstOrFail();

        // Filter players to match the specified Player_ID
        $player = $sessionGame->players->filter(function ($player) use ($playerId) {
            return $player->Player_ID == $playerId;
        })->map(function ($player) {
            return [
                'Player_ID' => $player->Player_ID,
                'Player_Name' => $player->playerInfo->Player_Name ?? 'N/A',
                'PrimaryPosition' => $player->primaryPosition->Position ?? 'N/A',
                'SecondaryPosition' => $player->secondaryPosition->Position ?? 'N/A',
            ];
        })->first();

        if (!$player) {
            return response()->json(['message' => 'Player not found in this session'], 404);
        }

        // Get the list of assists for this player in this session
        $assists = HomeAssist::where('Session_ID', $sessionId)
            ->where('Player_ID', $playerId)
            ->get(['HomeAssist_ID', 'Session_ID', 'Player_ID', 'ManualPlayer_ID']);

        // Get all sessions the player has participated in and gather details for each session
        $allSessions = SessionGame::whereHas('players', function ($query) use ($playerId) {
            $query->where('Player_ID', $playerId);
        })->get()->map(function ($session) use ($playerId) {
            // Calculate total goals for this session
            $totalGoals = HomeScore::where('Session_ID', $session->Session_ID)
                ->where('Player_ID', $playerId)
                ->count();

            // Calculate total assists for this session
            $totalAssists = HomeAssist::where('Session_ID', $session->Session_ID)
                ->where('Player_ID', $playerId)
                ->count();

            // Get total duration for the player in this session
            $totalDuration = MatchSummary::where('Session_ID', $session->Session_ID)
                ->where('Player_ID', $playerId)
                ->value('Total_Duration') ?? '00:00:00';

            return [
                'Session_ID' => $session->Session_ID,
                'Session_Date' => $session->Session_Date,
                'Session_Time' => $session->Session_Time,
                'Total_Goals' => $totalGoals,
                'Total_Assists' => $totalAssists,
                'Total_Duration' => $totalDuration,
            ];
        });

        // Filter sessions to only include those before the current session
        $priorSessions = $allSessions->filter(function ($session) use ($sessionGame) {
            return $session['Session_Date'] < $sessionGame->Session_Date ||
                   ($session['Session_Date'] == $sessionGame->Session_Date && $session['Session_Time'] < $sessionGame->Session_Time);
        });

        // Sort the prior sessions by date and time, most recent on top
        $sortedPriorSessions = $priorSessions->sortByDesc(function ($session) {
            return $session['Session_Date'] . ' ' . $session['Session_Time'];
        })->values(); // Re-index the array

        // Get the last 3 prior sessions
        $threePriorSessions = $sortedPriorSessions->take(3);

        // If there are less than 3 sessions, fill the remaining slots with placeholder data
        while ($threePriorSessions->count() < 3) {
            $threePriorSessions->push([
                'Session_ID' => 'N/A',
                'Session_Date' => 'N/A',
                'Session_Time' => 'N/A',
                'Total_Goals' => 'N/A',
                'Total_Assists' => 'N/A',
                'Total_Duration' => 'N/A',
            ]);
        }

        // Structure the response as 1_Prior_Session, 2_Prior_Session, and 3_Prior_Session
        $responseSessions = [
            '1_Prior_Session' => $threePriorSessions->get(0), // Most recent prior session
            '2_Prior_Session' => $threePriorSessions->get(1),
            '3_Prior_Session' => $threePriorSessions->get(2),
        ];

        // Get the first setting
        $setting = $sessionGame->settings->first();
        $TotalPlayerPerSide = $setting ? ($setting->S_Num + $setting->M_Num + $setting->D_Num + $setting->Gk_Num) : 0;

        // Get total goals for the player in the session
        $totalGoals = HomeScore::where('Session_ID', $sessionId)
            ->where('Player_ID', $playerId)
            ->count();

        // Get session total goals
        $homeScoreController = new HomeScoreController();
        $sessionTotalGoals = $homeScoreController->calculateSessionTotalGoals($sessionId);

        // Get the team name
        $teamName = $sessionGame->team->Team_Name ?? 'N/A';

        // Get total duration for the player in the session
        $totalDuration = MatchSummary::where('Session_ID', $sessionId)
            ->where('Player_ID', $playerId)
            ->value('Total_Duration') ?? '00:00:00';

        // Return the response
        return response()->json([
            'Session_ID' => $sessionGame->Session_ID,
            'Session_Date' => $sessionGame->Session_Date,
            'Session_Time' => $sessionGame->Session_Time,
            'Side_ID' => $setting->Side_ID ?? 'N/A',
            'TotalPlayerPerSide' => $TotalPlayerPerSide,
            'Player_ID' => $player['Player_ID'],
            'Player_Name' => $player['Player_Name'],
            'PrimaryPosition' => $player['PrimaryPosition'],
            'SecondaryPosition' => $player['SecondaryPosition'],
            'Total_Goals' => $totalGoals,
            'Total_Assists' => $assists->count(), // Total assists count
            'Assists_List' => $assists, // The list of assists
            'Session_Total_Goals' => $sessionTotalGoals,
            'ManualAway_Name' => $sessionGame->ManualAway_Name ?? 'Away Team',
            'ManualAway_Score' => $sessionGame->ManualAway_Score ?? 0,
            'Team_Name' => $teamName,
            'Session_Location' => $sessionGame->Session_Location,
            'Total_Duration' => $totalDuration, // Include the total duration
            '1_Prior_Session' => $responseSessions['1_Prior_Session'], // Most recent prior session
            '2_Prior_Session' => $responseSessions['2_Prior_Session'],
            '3_Prior_Session' => $responseSessions['3_Prior_Session'],
        ]);
    } catch (\Exception $e) {
        // Handle or log the exception
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


    




    public function getSessionInfoByPlayerInfoId($playerInfoId)
    {
        $players = Player::where('PlayerInfo_ID', $playerInfoId)->with('playerInfo', 'team')->get();

        if ($players->isEmpty()) {
            $playerInfo = PlayerInfo::find($playerInfoId);

            return response()->json([
                'Player_Name' => $playerInfo->Player_Name,
                'PlayerInfo_ID' => $playerInfo->PlayerInfo_ID,
                // 'Player_Name' => $playerInfo['Player_Name'],
                'Data' => [],
            ]);
        }

        $playerInfo = [
            'PlayerInfo_ID' => $playerInfoId,
            'Player_Name' => $players->first()->playerInfo->Player_Name ?? 'N/A',
        ];

        

        $sessions = [];

        foreach ($players as $player) {
            // Fetch session games where there are accepted invitations
            $sessionGames = SessionGame::where('Team_ID', $player->Team_ID)
                ->whereHas('sessionInvitations', function ($query) use ($playerInfoId) {
                    $query->where('PlayerInfo_ID', $playerInfoId)
                        ->where('Response_ID', 1);
                })
                ->get();

            $groupedSessions = [];

            foreach ($sessionGames as $sessionGame) {
                $groupedSessions[] = [
                    'Session_ID' => $sessionGame->Session_ID,
                    'Session_Date' => $sessionGame->Session_Date,
                ];
            }

            if (!empty($groupedSessions)) {
                $sessions[] = [
                    'Player_ID' => $player->Player_ID,
                    'Team_Name' => $player->team->Team_Name ?? 'N/A',
                    'Sessions' => $groupedSessions,
                ];
            }
        }

        return response()->json([
            'PlayerInfo_ID' => $playerInfo['PlayerInfo_ID'],
            'Player_Name' => $playerInfo['Player_Name'],
            'Data' => $sessions,
        ]);
    }

    //Update Response ID in Upcoming session in mobile
    public function updateInvitationResponse(Request $request, $sessionId, $playerInfoId)
    {
        // Validate the request
        $request->validate([
            'Response_ID' => 'required|integer|in:1,2', // Assuming 1 is for accept and 2 is for reject
        ]);

        // Find the session invitation
        $invitation = SessionInvitation::where('Session_ID', $sessionId)
                        ->where('PlayerInfo_ID', $playerInfoId)
                        ->firstOrFail();

        // Update the Response_ID
        $invitation->update(['Response_ID' => $request->Response_ID]);

        return response()->json([
            'success' => true,
            'message' => 'Invitation response updated successfully',
            'SessionInvitation_ID' => $invitation->SessionInvitation_ID,
            'Response_ID' => $invitation->Response_ID,
        ], 200);
    }

    public function destroyBySessionId($sessionId)
    {
        try {
            // Find the session game by Session_ID
            $sessionGame = SessionGame::where('Session_ID', $sessionId)->firstOrFail();

            // Delete related records in the associated tables
            $sessionGame->settings()->delete();
            $sessionGame->scoreBoard()->delete();
            $sessionGame->sessionInvitations()->delete();
            $sessionGame->manualPlayers()->delete();
            $sessionGame->homeScores()->delete();
            $sessionGame->awayScores()->delete();
            $sessionGame->substitutions()->delete();
            $sessionGame->matchSummaries()->delete();
            $sessionGame->playerNotes()->delete();

            // Finally, delete the session game
            $sessionGame->delete();

            return response()->json(['message' => 'Session game deleted successfully'], 204);
        } catch (\Exception $e) {
            // Handle the exception and return an error response
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }



}
