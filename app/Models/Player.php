<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    use HasFactory;
    protected $table = 'Player';
    protected $primaryKey = 'Player_ID';

    protected $fillable = [
        'PlayerInfo_ID',
        'Team_ID',
        'TeamInvitation_ID',
        'PrimaryPosition_ID',
        'SecondaryPosition_ID',
    ];

    public function playerInfo()
    {
        return $this->hasOne(PlayerInfo::class, 'PlayerInfo_ID', 'PlayerInfo_ID');
    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'Team_ID', 'Team_ID');
    }

    public function primaryPosition()
    {
        return $this->belongsTo(PrimaryPosition::class, 'PrimaryPosition_ID', 'PrimaryPosition_ID');
    }

    public function secondaryPosition()
    {
        return $this->belongsTo(SecondaryPosition::class, 'SecondaryPosition_ID', 'SecondaryPosition_ID');
    }

    public function homeScores()
    {
        return $this->hasMany(HomeScore::class, 'Player_ID', 'Player_ID');
    }

    public function performances()
    {
        return $this->hasMany(PlayerPerformance::class, 'Player_ID', 'Player_ID');
    }

    // Define a relationship to TeamPerformance
    public function teamPerformances()
    {
        return $this->hasMany(TeamPerformance::class, 'Player_ID', 'Player_ID');
    }

    // Define the relationship to the Substitution model
    public function substitutions()
    {
        return $this->hasMany(Substitution::class, 'Player_ID', 'Player_ID');
    }

    // Define the relationship with MatchSummary
    public function matchSummaries()
    {
        return $this->hasMany(MatchSummary::class, 'Player_ID', 'Player_ID');
    }

    // Define the relationship with TeamInvitation
    public function teamInvitation()
    {
        return $this->belongsTo(TeamInvitation::class, 'TeamInvitation_ID', 'TeamInvitation_ID');
    }

    // Define the relationship to the HomeAssist model
    public function homeAssists()
    {
        return $this->hasMany(HomeAssist::class, 'Player_ID', 'Player_ID');
    }

    // Define the relationship with PlayerNote
    public function playerNotes()
    {
        return $this->hasMany(PlayerNote::class, 'Player_ID', 'Player_ID');
    }
}
