<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionStatus extends Model
{
    use HasFactory;
    
    protected $table = 'SessionStatus'; 
    protected $primaryKey = 'SessionStatus_ID'; 

    protected $fillable = [
        'SessionStatus',
    ];

    // Define the relationship to the SessionGame model
    public function sessionGames()
    {
        return $this->hasMany(SessionGame::class, 'SessionStatus_ID', 'SessionStatus_ID');
    }
}
