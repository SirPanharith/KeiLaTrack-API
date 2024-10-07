<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FreeTrial extends Model
{
    use HasFactory;
    protected $table = 'FreeTrial'; 
    protected $primaryKey = 'FreeTrial_ID'; 

    protected $fillable = [
        'FreeTrial',
    ];

    public function players()
    {
        return $this->hasMany(PlayerInfo::class, 'FreeTrial_ID', 'FreeTrial_ID');
    }

     // Define the relationship to Host (FreeTrial can have many hosts)
     public function hosts()
     {
         return $this->hasMany(Host::class, 'FreeTrial_ID', 'FreeTrial_ID');
     }
}
