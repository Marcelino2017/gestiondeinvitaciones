<?php

namespace App\Models;

use App\Enums\InvitationStatus;
use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{

    protected $fillable = [
        'user_id',
        'organization_id',
        'token',
        'status',
    ];

    protected $casts = [
        'status' => InvitationStatus::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
