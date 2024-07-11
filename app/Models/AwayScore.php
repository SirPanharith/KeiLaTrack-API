<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AwayScore extends Model
{
    use HasFactory;
    protected $table = 'AwayScore'; 
    protected $primaryKey = 'AwayScore_ID'; 

    protected $fillable = [
        'Player_ID',
        'AwayAssist_ID',
        'ScoreTime',
        'Session_ID',
    ];

    /**
     * Get the player that owns the away score.
     */
    public function player()
    {
        return $this->belongsTo(Player::class, 'Player_ID', 'Player_ID');
    }

    /**
     * Get the away assist associated with the away score.
     */
    public function awayAssist()
    {
        return $this->belongsTo(AwayAssist::class, 'AwayAssist_ID', 'AwayAssist_ID');
    }

    /**
     * Get the session game that the away score belongs to.
     */
    public function sessionGame()
    {
        return $this->belongsTo(SessionGame::class, 'Session_ID', 'Session_ID');
    }
}
