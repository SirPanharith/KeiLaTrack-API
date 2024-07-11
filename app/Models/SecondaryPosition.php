<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecondaryPosition extends Model
{
    use HasFactory;

    protected $table = 'SecondaryPosition';
    protected $primaryKey = 'SecondaryPosition_ID';

    protected $fillable = [
        'Position',
    ];

    public function players()
    {
        return $this->hasMany(Player::class, 'SecondaryPosition_ID', 'SecondaryPosition_ID');
    }

    // Define the relationship to the ManualPlayer model
    public function manualPlayers()
    {
        return $this->hasMany(ManualPlayer::class, 'SecondaryPosition_ID', 'SecondaryPosition_ID');
    }
}
