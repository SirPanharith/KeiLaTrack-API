<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamPerformance extends Model
{
    use HasFactory;
    protected $table = 'TeamPerformance'; 
    protected $primaryKey = 'TeamPerformance_ID'; 

    protected $fillable = [
        'Player_ID',
        'Player_Duration',
        'Goals',
        'Assist',
    ];

    // Define a relationship to Player
    public function player()
    {
        return $this->belongsTo(Player::class, 'Player_ID', 'Player_ID');
    }
}
