<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerNote extends Model
{
    use HasFactory;
    protected $table = 'PlayerNote'; 
    protected $primaryKey = 'PlayerNote_ID'; 

    protected $fillable = [
        'Session_ID',
        'Player_ID',
        'PlayerNote',
    ];

    // Define the relationship with Player
    public function player()
    {
        return $this->belongsTo(Player::class, 'Player_ID', 'Player_ID');
    }

    // Define the relationship with SessionGame
    public function sessionGame()
    {
        return $this->belongsTo(SessionGame::class, 'Session_ID', 'Session_ID');
    }
}
