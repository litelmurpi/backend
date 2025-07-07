<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function studyLogs() {
        return $this->hasMany(StudyLog::class);
    }

    public function pomodoroSessions()
    {
        return $this->hasMany(PomodoroSession::class);
    }

    public function pomodoroSettings()
    {
        return $this->hasOne(UserPomodoroSetting::class);
    }

    // Pomodoro-related methods
    public function getOrCreatePomodoroSettings()
    {
        return $this->pomodoroSettings()->firstOrCreate(
            ['user_id' => $this->id],
            UserPomodoroSetting::getDefaultSettings()
        );
    }

    public function getPomodoroStatistics()
    {
        $sessions = $this->pomodoroSessions();
        
        return [
            'total_sessions' => $sessions->count(),
            'completed_sessions' => $sessions->completed()->count(),
            'total_focus_time' => $sessions->completed()->sum('actual_duration'),
            'average_session_length' => $sessions->completed()->avg('actual_duration'),
            'sessions_today' => $sessions->today()->count(),
            'sessions_this_week' => $sessions->thisWeek()->count(),
            'sessions_this_month' => $sessions->thisMonth()->count(),
        ];
    }
}
