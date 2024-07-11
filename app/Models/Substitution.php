<?php

// app/Models/Substitution.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MatchSummary;

class Substitution extends Model
{
    use HasFactory;
    protected $table = 'Substitution'; 
    protected $primaryKey = 'Sub_ID'; 

    protected $fillable = [
        'Session_ID',
        'Player_ID',
        'ManualPlayer_ID',
        'In',
        'Out',
        'Duration',
    ];

    // Define the relationship to the SessionGame model
    public function sessionGame()
    {
        return $this->belongsTo(SessionGame::class, 'Session_ID', 'Session_ID');
    }

    // Define the relationship to the Player model
    public function player()
    {
        return $this->belongsTo(Player::class, 'Player_ID', 'Player_ID');
    }

    // Define the relationship to the ManualPlayer model
    public function manualPlayer()
    {
        return $this->belongsTo(ManualPlayer::class, 'ManualPlayer_ID', 'ManualPlayer_ID');
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($substitution) {
            $substitution->updateMatchSummary();
        });

        static::updated(function ($substitution) {
            $substitution->updateMatchSummary();
        });
        // static::creating(function ($model) {
        //     $model->Sub_ID = Substitution::getNextPrimaryKeyValue();
        // });
    }

    public function updateMatchSummary()
    {
        // Determine whether to use Player_ID or ManualPlayer_ID
        $key = $this->Player_ID ? 'Player_ID' : 'ManualPlayer_ID';
        $id = $this->Player_ID ?? $this->ManualPlayer_ID;

        // Retrieve all substitutions for the same session and player/manual player
        $substitutions = Substitution::where('Session_ID', $this->Session_ID)
            ->where($key, $id)
            ->get();

        $totalDurationInSeconds = 0;

        foreach ($substitutions as $substitution) {
            $durationParts = explode(':', $substitution->Duration);
            $durationInSeconds = ($durationParts[0] * 3600) + ($durationParts[1] * 60) + $durationParts[2];
            $totalDurationInSeconds += $durationInSeconds;
        }

        $hours = floor($totalDurationInSeconds / 3600);
        $minutes = floor(($totalDurationInSeconds % 3600) / 60);
        $seconds = $totalDurationInSeconds % 60;
        $totalDuration = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

        // Update the MatchSummary table
        MatchSummary::where('Session_ID', $this->Session_ID)
            ->where($key, $id)
            ->update(['Total_Duration' => $totalDuration]);
    }



    // protected static function getNextPrimaryKeyValue()
    // {
    //     // Retrieve the highest existing primary key value from the database
    //     $highestPrimaryKey = Substition::max('Sub_ID');

    //     // Calculate the next value (e.g., incrementing by 1)
    //     $nextValue = (int)$highestPrimaryKey + 1;

    //     return str_pad($nextValue, strlen($highestPrimaryKey), '0', STR_PAD_LEFT);
    // }

    
}

