<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicPeriod extends Model
{
    protected $fillable = [
        'semester_id',
        'name',
        'is_active',
        'is_fallback',
        'order_no',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_fallback' => 'boolean',
    ];

    /**
     * Get the effective semester ID to use when calling AConnect.
     * Returns active semester, falls back to the row marked as fallback.
     */
    public static function effectiveSemesterId(): string
    {
        $active = static::where('is_active', true)->value('semester_id');

        if ($active) {
            return $active;
        }

        $fallback = static::where('is_fallback', true)->orderBy('order_no')->value('semester_id');

        return $fallback ?? '20252';
    }

    /**
     * Get ordered list for dropdowns.
     */
    public static function orderedList()
    {
        return static::orderBy('order_no')->get();
    }
}
