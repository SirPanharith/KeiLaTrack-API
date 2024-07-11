<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamInvitation extends Model
{
    use HasFactory;

    protected $table = 'TeamInvitation';
    protected $primaryKey = 'TeamInvitation_ID';

    protected $fillable = [
        'Team_ID',
        'PlayerInfo_ID',
        'token',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class, 'Team_ID', 'Team_ID');
    }

    public function response()
    {
        return $this->belongsTo(Response::class, 'Response_ID', 'Response_ID');
    }

    public function playerInfo()
    {
        return $this->belongsTo(PlayerInfo::class, 'PlayerInfo_ID', 'PlayerInfo_ID');
    }

    // Define the relationship with Player
    public function players()
    {
        return $this->hasMany(Player::class, 'TeamInvitation_ID', 'TeamInvitation_ID');
    }
}
