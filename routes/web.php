<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlayerController;
//use App\Http\Controllers\InvitationController;
use App\Http\Controllers\TeamInvitationController;
use App\Http\Controllers\SessionGameController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Resource routes for PlayerController
Route::resource('/players', PlayerController::class);

// Route to view the test email in the browser
Route::get('/view-test-email', function () {
    $content = 'This is just testing';
    return view('emails.simple_test', compact('content'));
});

// // Route to show the invitation form
// Route::get('/accept-invitation/{token}', [InvitationController::class, 'showInvitationForm']);

// // Route to handle form submission
// Route::post('/accept-invitation/{token}', [InvitationController::class, 'submitInvitationForm']);

// Route to create the team invitation and send the email
Route::post('/team-invitations/email/{token}', [TeamInvitationController::class, 'storeWithEmail']);

// Route to display the invitation form when the link is clicked
Route::get('/accept-invitation/{token}', [TeamInvitationController::class, 'showInvitationForm']);
Route::post('/accept-invitation/{token}', [TeamInvitationController::class, 'submitInvitationForm']);

// Route to show a specific team invitation
Route::get('/team-invitations/{id}', [TeamInvitationController::class, 'showSpecificInvitation']);

Route::resource('session-games', SessionGameController::class);

// Route to handle session invitations
Route::get('/session-invitation/{token}', [SessionGameController::class, 'showInvitation']);
Route::post('/session-invitation/{token}/accept', [SessionGameController::class, 'acceptInvitation']);
Route::post('/session-invitation/{token}/reject', [SessionGameController::class, 'rejectInvitation']);