<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PomodoroSession;
use App\Models\StudyLog;
use App\Models\UserPomodoroSetting;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PomodoroController extends Controller
{
    /**
     * Start a new pomodoro session
     */
    public function start(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'session_type' => ['required', Rule::in(['work', 'short_break', 'long_break'])],
                'planned_duration' => 'integer|min:1|max:120',
                'study_log_id' => 'nullable|exists:study_logs,id',
                'notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userId = auth()->id();

            // Check for existing active session
            $activeSession = PomodoroSession::where('user_id', $userId)
                ->whereIn('status', ['active', 'paused'])
                ->first();

            if ($activeSession) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have an active session. Please complete or cancel it first.',
                    'data' => [
                        'active_session' => $activeSession->load(['studyLog'])
                    ]
                ], 409);
            }

            // Get user settings for default duration
            $settings = auth()->user()->getOrCreatePomodoroSettings();
            $sessionType = $request->input('session_type', 'work');
            
            $defaultDuration = match($sessionType) {
                'work' => $settings->work_duration,
                'short_break' => $settings->short_break_duration,
                'long_break' => $settings->long_break_duration,
                default => 25
            };

            $plannedDuration = $request->input('planned_duration', $defaultDuration);

            // Validate study_log ownership if provided
            if ($request->study_log_id) {
                $studyLog = StudyLog::where('id', $request->study_log_id)
                    ->where('user_id', $userId)
                    ->first();
                
                if (!$studyLog) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Study log not found or not owned by user'
                    ], 404);
                }
            }

            $session = PomodoroSession::create([
                'user_id' => $userId,
                'study_log_id' => $request->study_log_id,
                'session_type' => $sessionType,
                'planned_duration' => $plannedDuration,
                'status' => 'active',
                'started_at' => Carbon::now(),
                'notes' => $request->notes
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pomodoro session started successfully',
                'data' => [
                    'session' => $session->load(['studyLog']),
                    'time_remaining' => $session->time_remaining * 60 // in seconds for frontend
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start pomodoro session',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pause current session
     */
    public function pause(string $id): JsonResponse
    {
        try {
            $session = $this->findUserSession($id);
            
            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session not found'
                ], 404);
            }

            if (!$session->canBePaused()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session cannot be paused'
                ], 400);
            }

            $session->pause();

            return response()->json([
                'success' => true,
                'message' => 'Session paused successfully',
                'data' => [
                    'session' => $session->fresh()->load(['studyLog'])
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to pause session',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resume paused session
     */
    public function resume(string $id): JsonResponse
    {
        try {
            $session = $this->findUserSession($id);
            
            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session not found'
                ], 404);
            }

            if (!$session->canBeResumed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session cannot be resumed'
                ], 400);
            }

            $session->resume();

            return response()->json([
                'success' => true,
                'message' => 'Session resumed successfully',
                'data' => [
                    'session' => $session->fresh()->load(['studyLog']),
                    'time_remaining' => $session->fresh()->time_remaining * 60
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to resume session',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete current session
     */
    public function complete(string $id): JsonResponse
    {
        try {
            $session = $this->findUserSession($id);
            
            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session not found'
                ], 404);
            }

            if (!$session->canBeCompleted()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session cannot be completed'
                ], 400);
            }

            $session->complete();

            // Auto-create StudyLog for work sessions if not linked to existing one
            $studyLog = null;
            if ($session->session_type === 'work' && !$session->study_log_id && $session->actual_duration > 0) {
                $studyLog = StudyLog::create([
                    'user_id' => $session->user_id,
                    'title' => 'Pomodoro Study Session',
                    'description' => $session->notes ?? 'Focus session completed using Pomodoro timer',
                    'category' => 'Pomodoro',
                    'duration' => $session->actual_duration,
                    'date' => $session->started_at->toDateString()
                ]);

                $session->study_log_id = $studyLog->id;
                $session->save();
            }

            // Suggest next break type
            $nextBreak = $this->suggestNextBreak($session->user_id);

            return response()->json([
                'success' => true,
                'message' => 'Session completed successfully',
                'data' => [
                    'session' => $session->fresh()->load(['studyLog']),
                    'study_log_created' => $studyLog ? true : false,
                    'next_break_suggestion' => $nextBreak
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete session',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel current session
     */
    public function cancel(string $id): JsonResponse
    {
        try {
            $session = $this->findUserSession($id);
            
            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session not found'
                ], 404);
            }

            $session->cancel();

            return response()->json([
                'success' => true,
                'message' => 'Session cancelled successfully',
                'data' => [
                    'session' => $session->fresh()->load(['studyLog'])
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel session',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current active session
     */
    public function active(): JsonResponse
    {
        try {
            $session = PomodoroSession::where('user_id', auth()->id())
                ->whereIn('status', ['active', 'paused'])
                ->with(['studyLog'])
                ->first();

            if (!$session) {
                return response()->json([
                    'success' => true,
                    'message' => 'No active session found',
                    'data' => [
                        'session' => null
                    ]
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Active session retrieved successfully',
                'data' => [
                    'session' => $session,
                    'time_remaining' => $session->time_remaining * 60,
                    'elapsed_time' => $session->elapsed_time * 60
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve active session',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get session history
     */
    public function history(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'page' => 'integer|min:1',
                'per_page' => 'integer|min:1|max:100',
                'session_type' => 'nullable|in:work,short_break,long_break',
                'status' => 'nullable|in:active,completed,cancelled,paused',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $query = PomodoroSession::where('user_id', auth()->id())
                ->with(['studyLog'])
                ->orderBy('created_at', 'desc');

            // Apply filters
            if ($request->session_type) {
                $query->where('session_type', $request->session_type);
            }

            if ($request->status) {
                $query->where('status', $request->status);
            }

            if ($request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $perPage = $request->input('per_page', 15);
            $sessions = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Session history retrieved successfully',
                'data' => $sessions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve session history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pomodoro statistics
     */
    public function statistics(): JsonResponse
    {
        try {
            $userId = auth()->id();
            $stats = auth()->user()->getPomodoroStatistics();
            
            // Additional detailed stats
            $today = Carbon::today();
            $thisWeek = [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()];
            $thisMonth = [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];

            $detailedStats = [
                'overview' => $stats,
                'today' => [
                    'sessions' => PomodoroSession::where('user_id', $userId)->today()->count(),
                    'completed' => PomodoroSession::where('user_id', $userId)->today()->completed()->count(),
                    'focus_time' => PomodoroSession::where('user_id', $userId)->today()->completed()->sum('actual_duration'),
                    'work_sessions' => PomodoroSession::where('user_id', $userId)->today()->work()->completed()->count()
                ],
                'this_week' => [
                    'sessions' => PomodoroSession::where('user_id', $userId)->thisWeek()->count(),
                    'completed' => PomodoroSession::where('user_id', $userId)->thisWeek()->completed()->count(),
                    'focus_time' => PomodoroSession::where('user_id', $userId)->thisWeek()->completed()->sum('actual_duration')
                ],
                'this_month' => [
                    'sessions' => PomodoroSession::where('user_id', $userId)->thisMonth()->count(),
                    'completed' => PomodoroSession::where('user_id', $userId)->thisMonth()->completed()->count(),
                    'focus_time' => PomodoroSession::where('user_id', $userId)->thisMonth()->completed()->sum('actual_duration')
                ]
            ];

            return response()->json([
                'success' => true,
                'message' => 'Pomodoro statistics retrieved successfully',
                'data' => $detailedStats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save user pomodoro preferences
     */
    public function saveSettings(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'work_duration' => 'integer|min:1|max:120',
                'short_break_duration' => 'integer|min:1|max:60',
                'long_break_duration' => 'integer|min:1|max:120',
                'sessions_before_long_break' => 'integer|min:2|max:10',
                'auto_start_breaks' => 'boolean',
                'notification_preferences' => 'array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $settings = auth()->user()->getOrCreatePomodoroSettings();
            $settings->update($request->only([
                'work_duration',
                'short_break_duration', 
                'long_break_duration',
                'sessions_before_long_break',
                'auto_start_breaks',
                'notification_preferences'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Settings saved successfully',
                'data' => [
                    'settings' => $settings->fresh()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user pomodoro preferences
     */
    public function getSettings(): JsonResponse
    {
        try {
            $settings = auth()->user()->getOrCreatePomodoroSettings();

            return response()->json([
                'success' => true,
                'message' => 'Settings retrieved successfully',
                'data' => [
                    'settings' => $settings
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Private helper methods
     */
    private function findUserSession(string $id): ?PomodoroSession
    {
        return PomodoroSession::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();
    }

    private function suggestNextBreak(int $userId): array
    {
        $settings = UserPomodoroSetting::where('user_id', $userId)->first();
        if (!$settings) {
            return [
                'type' => 'short_break',
                'duration' => 5
            ];
        }

        // Count completed work sessions today
        $workSessionsToday = PomodoroSession::where('user_id', $userId)
            ->today()
            ->work()
            ->completed()
            ->count();

        $nextBreakType = $settings->getNextBreakType($workSessionsToday);
        
        return [
            'type' => $nextBreakType,
            'duration' => $settings->getBreakDuration($nextBreakType),
            'sessions_completed_today' => $workSessionsToday
        ];
    }
}
