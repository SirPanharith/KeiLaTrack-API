<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Host extends Model
{
    use HasFactory, HasApiTokens;

    protected $table = 'Host'; 
    protected $primaryKey = 'Host_ID'; 

    protected $fillable = [
        'Host_Name',
        'Host_Email',
        'Host_Password',
        'Host_Image',
        'AccountStatus_ID',
        'FreeTrial_ID',
        'subscription_id',
    ];

    // Define the relationship to Team
    public function teams()
    {
        return $this->hasMany(Team::class, 'Host_ID', 'Host_ID');
    }
    
    // Define the relationship to AccountStatus (each host belongs to one account status)
    public function accountStatus()
    {
        return $this->belongsTo(AccountStatus::class, 'AccountStatus_ID', 'AccountStatus_ID');
    }

    // Define the relationship to FreeTrial (each host belongs to one free trial)
    public function freeTrial()
    {
        return $this->belongsTo(FreeTrial::class, 'FreeTrial_ID', 'FreeTrial_ID');
    }

}
