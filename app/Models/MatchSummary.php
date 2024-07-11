<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchSummary extends Model
{
    use HasFactory;
    protected $table = 'MatchSummary'; 
    protected $primaryKey = 'MatchSummary_ID'; 

    protected $fillable = [
        'Session_ID',
        'Player_ID',
        'ManualPlayer_ID',
        'Total_Goals',
        'Total_Assists',
        'Total_Duration',
    ];

    // Define the relationship with SessionGame
    public function sessionGame()
    {
        return $this->belongsTo(SessionGame::class, 'Session_ID', 'Session_ID');
    }

    // Define the relationship with Player
    public function player()
    {
        return $this->belongsTo(Player::class, 'Player_ID', 'Player_ID');
    }

    // Define the relationship with ManualPlayer
    public function manualPlayer()
    {
        return $this->belongsTo(ManualPlayer::class, 'ManualPlayer_ID', 'ManualPlayer_ID');
    }
}
