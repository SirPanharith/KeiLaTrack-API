<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScoreBoard extends Model
{
    use HasFactory;
    protected $table = 'ScoreBoard'; 
    protected $primaryKey = 'ScoreBoard_ID'; 

    protected $fillable = [
        'HomeScore_ID',
        'AwayScore_ID',
    ];

    /**
     * Get the session games associated with the scoreboard.
     */
    public function sessionGames()
    {
        return $this->hasMany(SessionGame::class, 'ScoreBoard_ID', 'ScoreBoard_ID');
    }
}
