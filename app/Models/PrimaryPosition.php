<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrimaryPosition extends Model
{
    use HasFactory;

    protected $table = 'PrimaryPosition';
    protected $primaryKey = 'PrimaryPosition_ID';

    protected $fillable = [
        'Position',
    ];

    public function players()
    {
        return $this->hasMany(Player::class, 'PrimaryPosition_ID', 'PrimaryPosition_ID');
    }

    // Define the relationship to the ManualPlayer model
    public function manualPlayers()
    {
        return $this->hasMany(ManualPlayer::class, 'PrimaryPosition_ID', 'PrimaryPosition_ID');
    }
}
