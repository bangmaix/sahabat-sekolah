<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAchievement extends Model
{
    protected $fillable = [
        'school_npsn',
        'nisn',
        'student_name',
        'class_name',
        'semester_id',
        'category_id',
        'achievement_name',
        'achievement_level',
        'ranking',
        'organizer',
        'achievement_date',
        'description',
        'certificate_path',
        'created_by',
    ];

    protected $casts = [
        'achievement_date' => 'date',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(AchievementCategory::class, 'category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForSchool($query, string $schoolNpsn)
    {
        return $query->where('school_npsn', $schoolNpsn);
    }
}
