<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HostAPIController;
use App\Http\Controllers\PlayerInfoAPIController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamInvitationController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\HomeScoreController;
use App\Http\Controllers\PlayerPerformanceController;
use App\Http\Controllers\TeamPerformanceController;
use App\Http\Controllers\MyTeamMemberController;
use App\Http\Controllers\SessionGameController;
use App\Http\Controllers\ManualPlayerController;
use App\Http\Controllers\SubstitutionController;
use App\Http\Controllers\MatchSummaryController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeAssistController;
use App\Http\Controllers\PlayerNoteController;
use App\Http\Controllers\ForgotPasswordController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::middleware('auth:playerinfo')->group(function () {
    Route::get('/playersinfo', [PlayerInfoAPIController::class, 'index']);
    Route::get('/playersinfo/{id}', [PlayerInfoAPIController::class, 'show']);
});

Route::middleware('auth:host')->group(function () {
     //Route::get('/hosts', [HostAPIController::class, 'index']);
     //Route::get('/hosts/{id}', [HostAPIController::class, 'show']);
});

Route::get('/hosts', [HostAPIController::class, 'index']); 
Route::get('/hosts/{id}', [HostAPIController::class, 'show']);
Route::post('/hosts/register', [HostAPIController::class, 'store']);
Route::post('/hosts/login', [HostAPIController::class, 'login']);
Route::put('/hosts/{id}', [HostAPIController::class, 'update']);
Route::delete('/hosts/{id}', [HostAPIController::class, 'destroy']);

// Route::get('/playersinfo', [PlayerInfoAPIController::class, 'index']);
Route::get('/playersinfo/{id}', [PlayerInfoAPIController::class, 'show']);
Route::post('/playersinfo/login', [PlayerInfoAPIController::class, 'login']);
Route::post('/playersinfo/register', [PlayerInfoAPIController::class, 'register']);
Route::put('/playersinfo/{id}', [PlayerInfoAPIController::class, 'update']);
Route::delete('/playersinfo/{id}', [PlayerInfoAPIController::class, 'destroy']);

Route::get('/players', [PlayerController::class, 'index']);
Route::post('/players/register', [PlayerController::class, 'store']);
Route::get('/players/{playerInfoId}', [PlayerController::class, 'show']);
Route::put('/players/{id}', [PlayerController::class, 'update']);
Route::delete('/players/{id}', [PlayerController::class, 'destroy']);

Route::get('/teams', [TeamController::class, 'index']);
Route::get('hosts/{hostId}/teams', [TeamController::class, 'getTeamsByHost']);
Route::post('/teams', [TeamController::class, 'store']);
Route::get('/teams/{id}', [TeamController::class, 'show']);
Route::put('/teams/{id}', [TeamController::class, 'update']);
Route::delete('/teams/{id}', [TeamController::class, 'destroy']);
Route::get('/teams/{teamId}/players', [TeamController::class, 'getPlayersByTeam']);

//Date and Time of the Upcoming Session
// Route::get('/session-games/team/{teamId}/datetime', [SessionGameController::class, 'getSessionDateTimeByTeam']);


Route::get('/team-invitations', [TeamInvitationController::class, 'index']);
Route::post('/team-invitations', [TeamInvitationController::class, 'store']);
Route::get('/team-invitations/{id}', [TeamInvitationController::class, 'show']);
Route::put('/team-invitations/{id}', [TeamInvitationController::class, 'update']);
Route::delete('/team-invitations/{id}', [TeamInvitationController::class, 'destroy']);
Route::post('team-invitations/email', [TeamInvitationController::class, 'storeWithEmail']);

Route::get('/settings', [SettingController::class, 'index']);
Route::post('/settings', [SettingController::class, 'store']);
Route::get('/settings/{id}', [SettingController::class, 'show']);
Route::put('/settings/{id}', [SettingController::class, 'update']);
Route::delete('/settings/{id}', [SettingController::class, 'destroy']);

Route::get('/homescores', [HomeScoreController::class, 'index']);
Route::post('/homescores', [HomeScoreController::class, 'store']);
Route::get('/homescores/{id}', [HomeScoreController::class, 'show']);
Route::put('/homescores/{id}', [HomeScoreController::class, 'update']);
Route::delete('/homescores/{id}', [HomeScoreController::class, 'destroy']);
//Get Home Score by Session_ID
Route::get('home-scores/session/{sessionId}', [HomeScoreController::class, 'getBySession']);
Route::get('/home-scores/session/{sessionId}/assists', [HomeScoreController::class, 'getAssistsBySession']);


Route::get('/player-performances', [PlayerPerformanceController::class, 'index']);
Route::post('/player-performances', [PlayerPerformanceController::class, 'store']);
Route::get('/player-performances/{id}', [PlayerPerformanceController::class, 'show']);
Route::put('/player-performances/{id}', [PlayerPerformanceController::class, 'update']);
Route::delete('/player-performances/{id}', [PlayerPerformanceController::class, 'destroy']);

Route::get('/my-team-members', [MyTeamMemberController::class, 'index']);
Route::post('/my-team-members', [MyTeamMemberController::class, 'store']);
Route::get('/my-team-members/{id}', [MyTeamMemberController::class, 'show']);
Route::put('/my-team-members/{id}', [MyTeamMemberController::class, 'update']);
Route::delete('/my-team-members/{id}', [MyTeamMemberController::class, 'destroy']);

Route::get('/session-games', [SessionGameController::class, 'index']);
Route::post('/session-games', [SessionGameController::class, 'store']);
Route::get('/session-games/{Team_ID}', [SessionGameController::class, 'show']);
Route::put('/session-games/{id}', [SessionGameController::class, 'update']);
// Route::delete('/session-games/{id}', [SessionGameController::class, 'destroy']);
// Delete Session based on sessionid
Route::delete('/session-game/{sessionId}', [SessionGameController::class, 'destroyBySessionId']);


Route::get('/session-games/player/{playerId}', [SessionGameController::class, 'getSessionGamesByPlayer']);
Route::get('/session-games/{id}', [SessionGameController::class, 'showspecificsession']);
Route::get('/sessions/team/{teamId}', [SessionGameController::class, 'getSessionsByTeamId']);
Route::get('/session-games/{sessionId}/player/{playerId}', [SessionGameController::class, 'getSessionGameBySessionAndPlayer']);



//session Invitatiion Pending
Route::get('invitation-session/{id}', [SessionGameController::class, 'getInvitationSession']);

Route::get('/manual-players', [ManualPlayerController::class, 'index']);
Route::post('/manual-players', [ManualPlayerController::class, 'store']);
Route::get('/manual-players/{id}', [ManualPlayerController::class, 'show']);
Route::put('/manual-players/{id}', [ManualPlayerController::class, 'update']);
Route::delete('/manual-players/{id}', [ManualPlayerController::class, 'destroy']);

// Route for getting players by setting ID
Route::get('session-games/setting/{settingId}', [SessionGameController::class, 'getPlayersBySetting']);

Route::get('/substitutions', [SubstitutionController::class, 'index']);
Route::post('/substitutions', [SubstitutionController::class, 'store']);
Route::get('/substitutions/{id}', [SubstitutionController::class, 'show']);
Route::put('/substitutions/{id}', [SubstitutionController::class, 'update']);
Route::delete('/substitutions/{id}', [SubstitutionController::class, 'destroy']);
Route::get('/substitutions/session/{sessionId}', [SubstitutionController::class, 'getBySessionId']);
Route::get('/substitutions/session/{sessionId}/players', [SubstitutionController::class, 'getPlayerDetailsBySessionId']);


Route::get('/match-summaries', [MatchSummaryController::class, 'index']);
Route::post('/match-summaries', [MatchSummaryController::class, 'store']);
Route::get('/match-summaries/{id}', [MatchSummaryController::class, 'show']);
Route::put('/match-summaries/{id}', [MatchSummaryController::class, 'update']);
Route::delete('/match-summaries/{id}', [MatchSummaryController::class, 'destroy']);
// Add this route for getting match summaries by Session_ID
Route::get('/match-summaries/session/{sessionId}', [MatchSummaryController::class, 'getBySessionId']);

//Route::post('/send-invitation', [InvitationController::class, 'sendInvitation']);
Route::get('/test-email', [InvitationController::class, 'testSendInvitation']);

Route::post('/send-simple-test-email', [InvitationController::class, 'sendSimpleTestEmail']);

Route::post('/send-invitation', [InvitationController::class, 'sendSimpleTestEmail']);
// Route::get('/accept-invitation/{token}', [InvitationController::class, 'showInvitationForm']);
// Route::post('/accept-invitation/{token}', [InvitationController::class, 'submitInvitationForm']);

// Route to show a specific team invitation
//Route::get('/team-invitations/{id}', [TeamInvitationController::class, 'showSpecificInvitation']);

//Route::post('/accept-invitation/{token}', [TeamInvitationController::class, 'submitInvitationForm']);

Route::post('/google-sign-in', [AuthController::class, 'googleSignIn']);

Route::get('home-assists', [HomeAssistController::class, 'index']);
Route::post('home-assists', [HomeAssistController::class, 'store']);
Route::get('home-assists/{id}', [HomeAssistController::class, 'show']);
Route::put('home-assists/{id}', [HomeAssistController::class, 'update']);
Route::delete('home-assists/{id}', [HomeAssistController::class, 'destroy']);


Route::get('session-info-by-playerinfo/{playerInfoId}', [SessionGameController::class, 'getSessionInfoByPlayerInfoId']);

Route::get('player-notes', [PlayerNoteController::class, 'index']);
Route::post('player-notes', [PlayerNoteController::class, 'store']);
Route::get('player-notes/{id}', [PlayerNoteController::class, 'show']);
Route::put('player-notes/{id}', [PlayerNoteController::class, 'update']);
Route::delete('player-notes/{id}', [PlayerNoteController::class, 'destroy']);
Route::get('player-notes/player/{playerId}', [PlayerNoteController::class, 'getNotesByPlayer']);
Route::get('player-notes/session/{sessionId}', [PlayerNoteController::class, 'getNotesBySession']);
Route::post('/update-note', [PlayerNoteController::class, 'updateNoteBySessionAndPlayer']);
Route::get('/player-notes/{sessionId}/{playerId}', [PlayerNoteController::class, 'getNoteBySessionAndPlayer']);

// Route::get('password/forgot', [ForgotPasswordController::class, 'showForgetPasswordForm'])->name('password.request');
// Route::post('password/forgot', [ForgotPasswordController::class, 'submitForgetPasswordForm'])->name('password.email');
// Route::get('password/reset', [ForgotPasswordController::class, 'showResetPasswordForm'])->name('password.reset');
// Route::post('password/reset', [ForgotPasswordController::class, 'submitResetPasswordForm'])->name('password.update');

//OTP for forgot password
Route::post('password/forgot', [ForgotPasswordController::class, 'submitForgetPasswordForm']);
Route::post('password/reset', [ForgotPasswordController::class, 'submitResetPasswordForm']);
Route::post('host/password/forgot', [ForgotPasswordController::class, 'submitForgetHostPasswordForm']);
Route::post('host/password/reset', [ForgotPasswordController::class, 'submitResetHostPasswordForm']);

//Update PlayerImage
Route::post('/playersinfo/update/{id}', [PlayerInfoAPIController::class, 'updatePlayerImage']);

//Update Player Name and Password
Route::put('playersinfo/update-credentials/{id}', [PlayerInfoAPIController::class, 'updatePlayerCredentials']);

//Update HostImage
Route::post('hosts/update/{id}', [HostAPIController::class, 'updateHostImage']);

//Update Host Name and Password
Route::put('hosts/update-credentials/{id}', [HostAPIController::class, 'updateHostCredentials']);

////Update Response ID in Upcoming session in mobile
Route::put('sessions/{sessionId}/players/{playerInfoId}/update-response', [SessionGameController::class, 'updateInvitationResponse']);

////display the session on upcoming session list
Route::get('/session-games/team/{teamId}/datetime', [SessionGameController::class, 'getSessionsByTeamIdWithStatus2']);




