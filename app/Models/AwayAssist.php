<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AwayAssist extends Model
{
    use HasFactory;
    protected $table = 'AwayAssist'; 
    protected $primaryKey = 'AwayAssist_ID'; 

    protected $fillable = [
        'Player_ID',
        //'ScoreTime',
    ];

    /**
     * Get the away scores associated with the away assist.
     */
    public function awayScores()
    {
        return $this->hasMany(AwayScore::class, 'AwayAssist_ID', 'AwayAssist_ID');
    }
}
