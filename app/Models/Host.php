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
    ];

    // Define the relationship to Team
    public function teams()
    {
        return $this->hasMany(Team::class, 'Host_ID', 'Host_ID');
    }
}
