<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class PlayerInfo extends Model
{
    use HasFactory, HasApiTokens;
    protected $table = 'PlayerInfo'; 
    protected $primaryKey = 'PlayerInfo_ID'; 

    protected $fillable = [
        'Player_Name',
        'Player_Email',
        'Player_Password',
        'PlayerInfo_Image',
    ];

    public function sessionGames()
    {
        return $this->hasMany(SessionGame::class, 'PlayerInfo_ID', 'PlayerInfo_ID');
    }

    // Add the relationship to SessionInvitation
    public function sessionInvitations()
    {
        return $this->hasMany(SessionInvitation::class, 'PlayerInfo_ID', 'PlayerInfo_ID');
    }

    // Add the relationship to TeamInvitation
    public function teamInvitations()
    {
        return $this->hasMany(TeamInvitation::class, 'PlayerInfo_ID', 'PlayerInfo_ID');
    }
    
    public function player()
    {
        return $this->hasMany(Player::class, 'PlayerInfo_ID', 'PlayerInfo_ID');
    }
}
