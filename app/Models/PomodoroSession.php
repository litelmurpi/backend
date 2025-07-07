<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PomodoroSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'study_log_id',
        'session_type',
        'planned_duration',
        'actual_duration',
        'status',
        'started_at',
        'completed_at',
        'notes'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'planned_duration' => 'integer',
        'actual_duration' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function studyLog()
    {
        return $this->belongsTo(StudyLog::class);
    }

    // Scopes
    public function scopeActive(Builder $query)
    {
        return $query->where('status', 'active');
    }

    public function scopePaused(Builder $query)
    {
        return $query->where('status', 'paused');
    }

    public function scopeCompleted(Builder $query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeWork(Builder $query)
    {
        return $query->where('session_type', 'work');
    }

    public function scopeBreak(Builder $query)
    {
        return $query->whereIn('session_type', ['short_break', 'long_break']);
    }

    public function scopeToday(Builder $query)
    {
        return $query->whereDate('created_at', Carbon::today());
    }

    public function scopeThisWeek(Builder $query)
    {
        return $query->whereBetween('created_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth(Builder $query)
    {
        return $query->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year);
    }

    // Accessors
    public function getFormattedDurationAttribute()
    {
        $duration = $this->actual_duration ?? $this->planned_duration;
        
        if ($duration < 60) {
            return "{$duration} minutes";
        }
        
        $hours = floor($duration / 60);
        $minutes = $duration % 60;
        
        return $minutes > 0 ? "{$hours}h {$minutes}m" : "{$hours}h";
    }

    public function getTimeRemainingAttribute()
    {
        if ($this->status !== 'active') {
            return 0;
        }

        $elapsed = Carbon::now()->diffInMinutes($this->started_at);
        $remaining = $this->planned_duration - $elapsed;
        
        return max(0, $remaining);
    }

    public function getElapsedTimeAttribute()
    {
        if (!$this->started_at) {
            return 0;
        }

        $end = $this->completed_at ?? Carbon::now();
        return $this->started_at->diffInMinutes($end);
    }

    // Methods
    public function calculateEfficiency()
    {
        if (!$this->actual_duration || !$this->planned_duration) {
            return null;
        }

        return round(($this->actual_duration / $this->planned_duration) * 100, 2);
    }

    public function isOvertime()
    {
        return $this->actual_duration > $this->planned_duration;
    }

    public function canBePaused()
    {
        return $this->status === 'active';
    }

    public function canBeResumed()
    {
        return $this->status === 'paused';
    }

    public function canBeCompleted()
    {
        return in_array($this->status, ['active', 'paused']);
    }

    public function pause()
    {
        if ($this->canBePaused()) {
            $this->status = 'paused';
            $this->actual_duration = $this->getElapsedTimeAttribute();
            $this->save();
        }
    }

    public function resume()
    {
        if ($this->canBeResumed()) {
            $this->status = 'active';
            // Adjust started_at to account for the time already spent
            $this->started_at = Carbon::now()->subMinutes($this->actual_duration ?? 0);
            $this->save();
        }
    }

    public function complete()
    {
        if ($this->canBeCompleted()) {
            $this->status = 'completed';
            $this->completed_at = Carbon::now();
            $this->actual_duration = $this->getElapsedTimeAttribute();
            $this->save();
        }
    }

    public function cancel()
    {
        if (in_array($this->status, ['active', 'paused'])) {
            $this->status = 'cancelled';
            $this->completed_at = Carbon::now();
            $this->actual_duration = $this->getElapsedTimeAttribute();
            $this->save();
        }
    }
}
