<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionHistory extends Model
{
    use HasFactory;
    protected $table = 'SessionHistory'; 
    protected $primaryKey = 'SessionHistory_ID'; 

    protected $fillable = [
     'Session_ID',
     'TeamPerformance_ID',
    ];
}
