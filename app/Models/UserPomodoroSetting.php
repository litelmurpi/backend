<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPomodoroSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_duration',
        'short_break_duration',
        'long_break_duration',
        'sessions_before_long_break',
        'auto_start_breaks',
        'notification_preferences'
    ];

    protected $casts = [
        'work_duration' => 'integer',
        'short_break_duration' => 'integer',
        'long_break_duration' => 'integer',
        'sessions_before_long_break' => 'integer',
        'auto_start_breaks' => 'boolean',
        'notification_preferences' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Methods
    public static function getDefaultSettings()
    {
        return [
            'work_duration' => 25,
            'short_break_duration' => 5,
            'long_break_duration' => 15,
            'sessions_before_long_break' => 4,
            'auto_start_breaks' => false,
            'notification_preferences' => [
                'sound_enabled' => true,
                'browser_notifications' => true,
                'break_reminders' => true
            ]
        ];
    }

    public function getNextBreakType($completedWorkSessions)
    {
        if ($completedWorkSessions % $this->sessions_before_long_break === 0) {
            return 'long_break';
        }
        return 'short_break';
    }

    public function getBreakDuration($breakType)
    {
        return $breakType === 'long_break' 
            ? $this->long_break_duration 
            : $this->short_break_duration;
    }
}
