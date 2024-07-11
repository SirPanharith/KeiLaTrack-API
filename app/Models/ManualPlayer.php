<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManualPlayer extends Model
{
    use HasFactory;
    protected $table = 'ManualPlayer'; 
    protected $primaryKey = 'ManualPlayer_ID'; 

    protected $fillable = [
        'ManualPlayer_Name',
        'PrimaryPosition_ID',
        'SecondaryPosition_ID',
        'Session_ID',
    ];

    // Define the relationship to the PrimaryPosition model
    public function primaryPosition()
    {
        return $this->belongsTo(PrimaryPosition::class, 'PrimaryPosition_ID', 'PrimaryPosition_ID');
    }

    // Define the relationship to the SecondaryPosition model
    public function secondaryPosition()
    {
        return $this->belongsTo(SecondaryPosition::class, 'SecondaryPosition_ID', 'SecondaryPosition_ID');
    }

    // Define the relationship to the SessionGame model
    public function sessionGame()
    {
        return $this->belongsTo(SessionGame::class, 'Session_ID', 'Session_ID');
    }

    // Define the relationship to the Substitution model
    public function substitutions()
    {
        return $this->hasMany(Substitution::class, 'ManualPlayer_ID', 'ManualPlayer_ID');
    }

    /**
     * Get the home scores associated with the manual player.
     */
    public function homeScores()
    {
        return $this->hasMany(HomeScore::class, 'ManualPlayer_ID', 'ManualPlayer_ID');
    }

    // Define the relationship with MatchSummary
    public function matchSummaries()
    {
        return $this->hasMany(MatchSummary::class, 'ManualPlayer_ID', 'ManualPlayer_ID');
    }

    // Define the relationship to the HomeAssist model
    public function homeAssists()
    {
        return $this->hasMany(HomeAssist::class, 'ManualPlayer_ID', 'ManualPlayer_ID');
    }
}
