<?php

// app/Models/HomeScore.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MatchSummary;

class HomeScore extends Model
{
    use HasFactory;
    protected $table = 'HomeScore'; 
    protected $primaryKey = 'HomeScore_ID'; 

    protected $fillable = [
        'Player_ID',
        'ManualPlayer_ID',
        'HomeAssist_ID',
        'ScoreTime',
        'Session_ID',
    ];

    public function player()
    {
        return $this->belongsTo(Player::class, 'Player_ID', 'Player_ID');
    }

    public function manualPlayer()
    {
        return $this->belongsTo(ManualPlayer::class, 'ManualPlayer_ID', 'ManualPlayer_ID');
    }

    public function homeAssist()
    {
        return $this->belongsTo(HomeAssist::class, 'HomeAssist_ID', 'HomeAssist_ID');
    }

    public function sessionGame()
    {
        return $this->belongsTo(SessionGame::class, 'Session_ID', 'Session_ID');
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($homeScore) {
            $homeScore->updateMatchSummaryGoals();
        });

        static::deleted(function ($homeScore) {
            $homeScore->updateMatchSummaryGoals();
        });
    }

    public function updateMatchSummaryGoals()
    {
        $key = $this->Player_ID ?? $this->ManualPlayer_ID;
        $isPlayer = isset($this->Player_ID);
        
        $totalGoals = HomeScore::where('Session_ID', $this->Session_ID)
            ->where(function ($query) use ($isPlayer, $key) {
                $query->where($isPlayer ? 'Player_ID' : 'ManualPlayer_ID', $key);
            })
            ->count();

        $matchSummary = MatchSummary::where('Session_ID', $this->Session_ID)
            ->where($isPlayer ? 'Player_ID' : 'ManualPlayer_ID', $key)
            ->first();

        if ($matchSummary) {
            $matchSummary->update(['Total_Goals' => $totalGoals]);
        } else {
            // If match summary does not exist, create a new one
            MatchSummary::create([
                'Session_ID' => $this->Session_ID,
                'Player_ID' => $isPlayer ? $key : null,
                'ManualPlayer_ID' => $isPlayer ? null : $key,
                'Total_Goals' => $totalGoals,
                'Total_Assists' => 0, // Default value, you can update this based on your logic
                'Total_Duration' => '00:00:00', // Default value, you can update this based on your logic
            ]);
        }
    }
}
