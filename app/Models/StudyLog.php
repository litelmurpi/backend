<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudyLog extends Model
{
    use HasFactory;

    protected $table = 'study_logs';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'category',
        'duration',
        'date'
    ];

    protected $casts = [
        'date' => 'date',
        'duration' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function pomodoroSessions()
    {
        return $this->hasMany(PomodoroSession::class);
    }

    // Methods
    public function getTotalFocusTime()
    {
        return $this->pomodoroSessions()
            ->completed()
            ->sum('actual_duration');
    }

    public function getPomodoroSessionsCount()
    {
        return $this->pomodoroSessions()->count();
    }
}
