<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Side extends Model
{
    use HasFactory;
    protected $table = 'Side'; 
    protected $primaryKey = 'Side_ID'; 

    protected $fillable = [
        'Side',
    ];

    /**
     * Get the settings associated with the side.
     */
    public function settings()
    {
        return $this->hasMany(Setting::class, 'Side_ID', 'Side_ID');
    }
}
