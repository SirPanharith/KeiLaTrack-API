<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;
    protected $table = 'Setting'; 
    protected $primaryKey = 'Setting_ID'; 

    protected $fillable = [
        'SubMode_ID',
        'Session_ID',
        'Sub_Timespace',
        'Divide_ID',
        'S_Num',
        'M_Num',
        'D_Num',
        'GK_Num',
        'Side_ID',
    ];

    public function subMode()
    {
        return $this->belongsTo(SubMode::class, 'SubMode_ID', 'SubMode_ID');
    }

    public function divide()
    {
        return $this->belongsTo(Divide::class, 'Divide_ID', 'Divide_ID');
    }

    public function side()
    {
        return $this->belongsTo(Side::class, 'Side_ID', 'Side_ID');
    }

    public function sessionGame()
    {
        return $this->belongsTo(SessionGame::class, 'Session_ID', 'Session_ID');
    }
}
