<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionInvitation extends Model
{
    use HasFactory;
    protected $table = 'SessionInvitation'; 
    protected $primaryKey = 'SessionInvitation_ID'; 

    protected $fillable = [
        'Session_ID',
        'PlayerInfo_ID',
        'Response_ID',
        'token',
    ];

    protected $attributes = [
        'Response_ID' => 2, // Default value for Response_ID
    ];

    public function response()
    {
        return $this->belongsTo(Response::class, 'Response_ID', 'Response_ID');
    }

    public function playerInfo()
    {
        return $this->belongsTo(PlayerInfo::class, 'PlayerInfo_ID', 'PlayerInfo_ID');
    }

    public function sessionGame()
    {
        return $this->belongsTo(SessionGame::class, 'Session_ID', 'Session_ID');
    }

    public function player()
    {
        return $this->belongsTo(Player::class, 'PlayerInfo_ID', 'PlayerInfo_ID');
    }
}
