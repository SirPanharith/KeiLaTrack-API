<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountStatus extends Model
{
    use HasFactory;
    protected $table = 'AccountStatus'; 
    protected $primaryKey = 'AccountStatus_ID'; 

    protected $fillable = [
        'AccountStatus',
    ];

    public function players()
    {
        return $this->hasMany(PlayerInfo::class, 'AccountStatus_ID', 'AccountStatus_ID');
    }

    // Define the relationship to Host (one account status can have many hosts)
    public function hosts()
    {
        return $this->hasMany(Host::class, 'AccountStatus_ID', 'AccountStatus_ID');
    }
}
