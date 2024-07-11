<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Divide extends Model
{
    use HasFactory;
    protected $table = 'Divide'; 
    protected $primaryKey = 'Divide_ID'; 

    protected $fillable = [
        'Divide',
    ];

    /**
     * Get the settings associated with the divide.
     */
    public function settings()
    {
        return $this->hasMany(Setting::class, 'Divide_ID', 'Divide_ID');
    }
}
