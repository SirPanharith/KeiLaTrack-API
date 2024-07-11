<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $table = 'Team'; 
    protected $primaryKey = 'Team_ID'; 

    protected $fillable = [
        'Team_Name',
        'Host_ID',
        'Team_Detail',
        'Team_Note',
    ];

    public function teamInvitations()
    {
        return $this->hasMany(TeamInvitation::class, 'Team_ID', 'Team_ID');
    }

    public function sessionGames()
    {
        return $this->hasMany(SessionGame::class, 'Team_ID', 'Team_ID');
    }

    public function host()
    {
        return $this->belongsTo(Host::class, 'Host_ID', 'Host_ID');
    }

    public function players()
    {
        return $this->hasMany(Player::class, 'Team_ID', 'Team_ID');
    }

    public function matchSummaries()
    {
        return $this->hasMany(MatchSummary::class, 'Team_ID', 'Team_ID');
    }
}
