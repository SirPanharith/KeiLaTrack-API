<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeAssist extends Model
{
    use HasFactory;
    protected $table = 'HomeAssist'; 
    protected $primaryKey = 'HomeAssist_ID'; 

    protected $fillable = [
        'Player_ID',
        'ManualPlayer_ID',
        'Session_ID'
    ];

    /**
     * Get the home scores associated with the home assist.
     */
    public function homeScores()
    {
        return $this->hasMany(HomeScore::class, 'HomeAssist_ID', 'HomeAssist_ID');
    }

    // Define the relationship to the ManualPlayer model
    public function manualPlayer()
    {
        return $this->belongsTo(ManualPlayer::class, 'ManualPlayer_ID', 'ManualPlayer_ID');
    }

    // Define the relationship to the Player model
    public function player()
    {
        return $this->belongsTo(Player::class, 'Player_ID', 'Player_ID');
    }

    public function sessionGame()
    {
        return $this->belongsTo(SessionGame::class, 'Session_ID', 'Session_ID');
    }
}
