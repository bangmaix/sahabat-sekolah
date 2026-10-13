<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Kuroragi\GeneralHelper\Traits\Blameable;

class SlaConfiguration extends Model
{
    use Blameable, SoftDeletes;

    protected $fillable = [
        'risk_level',
        'response_time_hours',
        'resolution_time_days',
    ];

    /**
     * Override currentAuthId from Blameable since this app uses session-based auth.
     */
    protected static function currentAuthId()
    {
        return session('user_id') ?: Auth::id();
    }
}
