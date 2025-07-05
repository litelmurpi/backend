<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudyLog;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function stats(): JsonResponse
    {
        try {
            $userId = auth()->id();
            
            // Get longest streak
            $longestStreak = $this->calculateLongestStreak($userId);
            
            // Get total time this month (in minutes)
            $totalTimeThisMonth = $this->getTotalTimeThisMonth($userId);
            
            // Get today's sessions count
            $sessionsToday = $this->getSessionsToday($userId);
            
            return response()->json([
                'success' => true,
                'message' => 'Dashboard stats retrieved successfully',
                'data' => [
                    'longest_streak' => $longestStreak,
                    'total_time_this_month' => [
                        'minutes' => $totalTimeThisMonth,
                        'hours' => round($totalTimeThisMonth / 60, 1),
                        'formatted' => $this->formatDuration($totalTimeThisMonth)
                    ],
                    'sessions_today' => $sessionsToday
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve dashboard stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Calculate longest streak of consecutive days
     */
    private function calculateLongestStreak(int $userId): int
    {
        $studyLogs = StudyLog::where('user_id', $userId)
            ->orderBy('log_date', 'asc')
            ->pluck('log_date')
            ->map(function ($date) {
                return Carbon::parse($date)->format('Y-m-d');
            })
            ->unique()
            ->values()
            ->toArray();
        
        if (empty($studyLogs)) {
            return 0;
        }
        
        $maxStreak = 1;
        $currentStreak = 1;
        
        for ($i = 1; $i < count($studyLogs); $i++) {
            $prevDate = Carbon::parse($studyLogs[$i - 1]);
            $currentDate = Carbon::parse($studyLogs[$i]);
            
            if ($prevDate->diffInDays($currentDate) == 1) {
                $currentStreak++;
                $maxStreak = max($maxStreak, $currentStreak);
            } else {
                $currentStreak = 1;
            }
        }
        
        return $maxStreak;
    }
    
    /**
     * Get total study time for current month
     */
    private function getTotalTimeThisMonth(int $userId): int
    {
        return StudyLog::where('user_id', $userId)
            ->whereMonth('log_date', Carbon::now()->month)
            ->whereYear('log_date', Carbon::now()->year)
            ->sum('duration_minutes');
    }
    
    /**
     * Get number of study sessions today
     */
    private function getSessionsToday(int $userId): int
    {
        return StudyLog::where('user_id', $userId)
            ->whereDate('log_date', Carbon::today())
            ->count();
    }
    
    /**
     * Format duration in minutes to human readable format
     */
    private function formatDuration(int $minutes): string
    {
        if ($minutes < 60) {
            return "{$minutes} menit";
        }
        
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        
        if ($remainingMinutes == 0) {
            return "{$hours} jam";
        }
        
        return "{$hours} jam {$remainingMinutes} menit";
    }

    public function heatmap(): JsonResponse
    {
        try {
            $userId = auth()->id();
            
            // Get data for the last 365 days
            $endDate = Carbon::today();
            $startDate = Carbon::today()->subDays(364); // 365 days including today
            
            // Get aggregated data per day
            $heatmapData = StudyLog::where('user_id', $userId)
                ->whereBetween('log_date', [$startDate, $endDate])
                ->groupBy('log_date')
                ->selectRaw('log_date as date, SUM(duration_minutes) as total_minutes, COUNT(*) as session_count')
                ->orderBy('log_date', 'asc')
                ->get()
                ->map(function ($item) {
                    return [
                        'date' => Carbon::parse($item->date)->format('Y-m-d'),
                        'total_minutes' => (int) $item->total_minutes,
                        'total_hours' => round($item->total_minutes / 60, 1),
                        'session_count' => (int) $item->session_count,
                        'intensity' => $this->calculateIntensity($item->total_minutes)
                    ];
                })
                ->keyBy('date')
                ->toArray();
            
            // Generate complete calendar data with empty days
            $completeHeatmap = $this->generateCompleteHeatmap($startDate, $endDate, $heatmapData);
            
            // Calculate statistics
            $stats = $this->calculateHeatmapStats($heatmapData);
            
            return response()->json([
                'success' => true,
                'message' => 'Heatmap data retrieved successfully',
                'data' => [
                    'heatmap' => $completeHeatmap,
                    'stats' => $stats,
                    'period' => [
                        'start' => $startDate->format('Y-m-d'),
                        'end' => $endDate->format('Y-m-d')
                    ]
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve heatmap data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Calculate intensity level based on study duration
     * 0: no activity
     * 1: 1-30 minutes (light)
     * 2: 31-60 minutes (moderate)
     * 3: 61-120 minutes (high)
     * 4: >120 minutes (very high)
     */
    private function calculateIntensity(int $minutes): int
    {
        if ($minutes == 0) return 0;
        if ($minutes <= 30) return 1;
        if ($minutes <= 60) return 2;
        if ($minutes <= 120) return 3;
        return 4;
    }
    
    /**
     * Generate complete heatmap with all dates (including empty ones)
     */
    private function generateCompleteHeatmap(Carbon $startDate, Carbon $endDate, array $heatmapData): array
    {
        $completeHeatmap = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            
            if (isset($heatmapData[$dateStr])) {
                $completeHeatmap[] = $heatmapData[$dateStr];
            } else {
                $completeHeatmap[] = [
                    'date' => $dateStr,
                    'total_minutes' => 0,
                    'total_hours' => 0,
                    'session_count' => 0,
                    'intensity' => 0
                ];
            }
            
            $currentDate->addDay();
        }
        
        return $completeHeatmap;
    }
    
    /**
     * Calculate heatmap statistics
     */
    private function calculateHeatmapStats(array $heatmapData): array
    {
        if (empty($heatmapData)) {
            return [
                'total_days_studied' => 0,
                'total_study_time' => [
                    'minutes' => 0,
                    'hours' => 0,
                    'formatted' => '0 menit'
                ],
                'average_per_day' => [
                    'minutes' => 0,
                    'formatted' => '0 menit'
                ],
                'most_productive_day' => null,
                'current_streak' => 0
            ];
        }
        
        $totalMinutes = array_sum(array_column($heatmapData, 'total_minutes'));
        $totalDays = count($heatmapData);
        $averageMinutes = $totalDays > 0 ? round($totalMinutes / $totalDays) : 0;
        
        // Find most productive day
        $mostProductiveDay = array_reduce($heatmapData, function ($carry, $item) {
            if (!$carry || $item['total_minutes'] > $carry['total_minutes']) {
                return $item;
            }
            return $carry;
        });
        
        return [
            'total_days_studied' => $totalDays,
            'total_study_time' => [
                'minutes' => $totalMinutes,
                'hours' => round($totalMinutes / 60, 1),
                'formatted' => $this->formatDuration($totalMinutes)
            ],
            'average_per_day' => [
                'minutes' => $averageMinutes,
                'formatted' => $this->formatDuration($averageMinutes)
            ],
            'most_productive_day' => $mostProductiveDay,
            'current_streak' => $this->calculateCurrentStreak(auth()->id())
        ];
    }
    
    /**
     * Calculate current active streak
     */
    private function calculateCurrentStreak(int $userId): int
    {
        $today = Carbon::today();
        $streak = 0;
        $currentDate = $today->copy();
        
        // Check backwards from today
        while (true) {
            $hasStudy = StudyLog::where('user_id', $userId)
                ->whereDate('log_date', $currentDate)
                ->exists();
            
            if ($hasStudy) {
                $streak++;
                $currentDate->subDay();
            } else {
                // If today has no study, check yesterday for active streak
                if ($streak == 0 && $currentDate->format('Y-m-d') == $today->format('Y-m-d')) {
                    $currentDate->subDay();
                    continue;
                }
                break;
            }
        }
        
        return $streak;
    }
}