<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    use HasFactory;
    protected $table = 'Response'; 
    protected $primaryKey = 'Response_ID'; 

    protected $fillable = [
        'Response',
    ];

    public function sessionInvitations()
    {
        return $this->hasMany(SessionInvitation::class, 'Response_ID', 'Response_ID');
    }

    /**
     * Get the team invitations associated with the response.
     */
    public function teamInvitations()
    {
        return $this->hasMany(TeamInvitation::class, 'Response_ID', 'Response_ID');
    }
}
