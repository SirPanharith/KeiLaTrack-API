<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionGame extends Model
{
    use HasFactory;
    
    protected $table = 'SessionGame'; 
    protected $primaryKey = 'Session_ID'; 

    protected $fillable = [
        'Session_Date',
        'Session_Duration',
        'Session_Time',
        'Session_Location',
        'Session_Note',
        'Team_ID',
        'SessionStatus_ID', // Ensure this is included in the fillable array
        'ManualAway_Name',
        'ManualAway_Score',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class, 'Team_ID', 'Team_ID');
    }

    public function settings()
    {
        return $this->hasMany(Setting::class, 'Session_ID', 'Session_ID');
    }

    public function scoreBoard()
    {
        return $this->belongsTo(ScoreBoard::class, 'ScoreBoard_ID', 'ScoreBoard_ID');
    }

    public function players()
    {
        return $this->hasMany(Player::class, 'Team_ID', 'Team_ID');
    }

    public function sessionInvitations()
    {
        return $this->hasMany(SessionInvitation::class, 'Session_ID', 'Session_ID');
    }

    public function manualPlayers()
    {
        return $this->hasMany(ManualPlayer::class, 'Session_ID', 'Session_ID');
    }

    public function awayScores()
    {
        return $this->hasMany(AwayScore::class, 'Session_ID', 'Session_ID');
    }

    public function homeScores()
    {
        return $this->hasMany(HomeScore::class, 'Session_ID', 'Session_ID');
    }

    public function substitutions()
    {
        return $this->hasMany(Substitution::class, 'Session_ID', 'Session_ID');
    }

    public function matchSummaries()
    {
        return $this->hasMany(MatchSummary::class, 'Session_ID', 'Session_ID');
    }

    public function playerNotes()
    {
        return $this->hasMany(PlayerNote::class, 'Session_ID', 'Session_ID');
    }

    // Define the relationship to the SessionStatus model
    public function sessionStatus()
    {
        return $this->belongsTo(SessionStatus::class, 'SessionStatus_ID', 'SessionStatus_ID');
    }

    public function homeAssists()
    {
        return $this->hasMany(HomeAssist::class, 'Session_ID', 'Session_ID');
    }
}
