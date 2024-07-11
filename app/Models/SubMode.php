<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubMode extends Model
{
    use HasFactory;
    protected $table = 'SubMode'; 
    protected $primaryKey = 'SubMode_ID'; 

    protected $fillable = [
        'SubMode',
    ];

    /**
     * Get the settings associated with the submode.
     */
    public function settings()
    {
        return $this->hasMany(Setting::class, 'SubMode_ID', 'SubMode_ID');
    }
}
