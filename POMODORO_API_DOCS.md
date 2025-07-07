# Pomodoro Timer API Documentation

## Overview
The Pomodoro Timer API provides comprehensive functionality for managing focus sessions using the Pomodoro Technique. Users can start, pause, resume, and complete timer sessions while tracking their productivity and integrating with the existing study log system.

## Authentication
All endpoints require authentication using Laravel Sanctum. Include the Bearer token in the Authorization header:
```
Authorization: Bearer {your-token}
```

## Base URL
```
/api/pomodoro
```

## Endpoints

### 1. Start Pomodoro Session
**POST** `/start`

Start a new pomodoro session. Only one active session is allowed per user.

**Request Body:**
```json
{
    "session_type": "work",           // Required: work, short_break, long_break
    "planned_duration": 25,           // Optional: 1-120 minutes (uses user settings if not provided)
    "study_log_id": 123,             // Optional: link to existing study log
    "notes": "Focus on feature X"    // Optional: session notes
}
```

**Response (201):**
```json
{
    "success": true,
    "message": "Pomodoro session started successfully",
    "data": {
        "session": {
            "id": 1,
            "session_type": "work",
            "planned_duration": 25,
            "status": "active",
            "started_at": "2025-07-07T20:40:09Z",
            "study_log": null
        },
        "time_remaining": 1500  // seconds
    }
}
```

**Error (409) - Active Session Exists:**
```json
{
    "success": false,
    "message": "You already have an active session. Please complete or cancel it first.",
    "data": {
        "active_session": {
            "id": 1,
            "status": "active"
        }
    }
}
```

### 2. Pause Session
**PUT** `/{id}/pause`

Pause the currently active session.

**Response (200):**
```json
{
    "success": true,
    "message": "Session paused successfully",
    "data": {
        "session": {
            "id": 1,
            "status": "paused",
            "actual_duration": 15
        }
    }
}
```

### 3. Resume Session
**PUT** `/{id}/resume`

Resume a paused session.

**Response (200):**
```json
{
    "success": true,
    "message": "Session resumed successfully",
    "data": {
        "session": {
            "id": 1,
            "status": "active"
        },
        "time_remaining": 600  // seconds
    }
}
```

### 4. Complete Session
**PUT** `/{id}/complete`

Mark a session as completed. For work sessions, this will auto-create a StudyLog entry if not linked to one.

**Response (200):**
```json
{
    "success": true,
    "message": "Session completed successfully",
    "data": {
        "session": {
            "id": 1,
            "status": "completed",
            "actual_duration": 25,
            "completed_at": "2025-07-07T21:05:09Z"
        },
        "study_log_created": true,
        "next_break_suggestion": {
            "type": "short_break",
            "duration": 5,
            "sessions_completed_today": 1
        }
    }
}
```

### 5. Cancel Session
**PUT** `/{id}/cancel`

Cancel an active or paused session.

**Response (200):**
```json
{
    "success": true,
    "message": "Session cancelled successfully",
    "data": {
        "session": {
            "id": 1,
            "status": "cancelled",
            "actual_duration": 10
        }
    }
}
```

### 6. Get Active Session
**GET** `/active`

Retrieve the current active or paused session.

**Response (200) - With Active Session:**
```json
{
    "success": true,
    "message": "Active session retrieved successfully",
    "data": {
        "session": {
            "id": 1,
            "session_type": "work",
            "status": "active",
            "planned_duration": 25,
            "started_at": "2025-07-07T20:40:09Z"
        },
        "time_remaining": 900,  // seconds
        "elapsed_time": 600     // seconds
    }
}
```

**Response (200) - No Active Session:**
```json
{
    "success": true,
    "message": "No active session found",
    "data": {
        "session": null
    }
}
```

### 7. Get Session History
**GET** `/history`

Retrieve paginated session history with optional filters.

**Query Parameters:**
- `page` (integer): Page number (default: 1)
- `per_page` (integer): Items per page (1-100, default: 15)
- `session_type` (string): Filter by work, short_break, long_break
- `status` (string): Filter by active, completed, cancelled, paused
- `date_from` (date): Filter from date (YYYY-MM-DD)
- `date_to` (date): Filter to date (YYYY-MM-DD)

**Response (200):**
```json
{
    "success": true,
    "message": "Session history retrieved successfully",
    "data": {
        "data": [
            {
                "id": 1,
                "session_type": "work",
                "status": "completed",
                "planned_duration": 25,
                "actual_duration": 25,
                "started_at": "2025-07-07T20:40:09Z",
                "study_log": {
                    "id": 1,
                    "title": "Study Session"
                }
            }
        ],
        "pagination": {
            "current_page": 1,
            "total": 50
        }
    }
}
```

### 8. Get Statistics
**GET** `/statistics`

Get comprehensive Pomodoro statistics.

**Response (200):**
```json
{
    "success": true,
    "message": "Pomodoro statistics retrieved successfully",
    "data": {
        "overview": {
            "total_sessions": 50,
            "completed_sessions": 45,
            "total_focus_time": 1125,        // minutes
            "average_session_length": 25.0,
            "sessions_today": 3,
            "sessions_this_week": 15,
            "sessions_this_month": 50
        },
        "today": {
            "sessions": 3,
            "completed": 3,
            "focus_time": 75,
            "work_sessions": 3
        },
        "this_week": {
            "sessions": 15,
            "completed": 14,
            "focus_time": 350
        },
        "this_month": {
            "sessions": 50,
            "completed": 45,
            "focus_time": 1125
        }
    }
}
```

### 9. Save Settings
**POST** `/settings`

Save user Pomodoro preferences.

**Request Body:**
```json
{
    "work_duration": 30,                    // 1-120 minutes
    "short_break_duration": 10,             // 1-60 minutes
    "long_break_duration": 20,              // 1-120 minutes
    "sessions_before_long_break": 3,        // 2-10 sessions
    "auto_start_breaks": true,              // boolean
    "notification_preferences": {
        "sound_enabled": true,
        "browser_notifications": true,
        "break_reminders": true
    }
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Settings saved successfully",
    "data": {
        "settings": {
            "id": 1,
            "work_duration": 30,
            "short_break_duration": 10,
            "long_break_duration": 20,
            "sessions_before_long_break": 3,
            "auto_start_breaks": true,
            "notification_preferences": {
                "sound_enabled": true,
                "browser_notifications": true,
                "break_reminders": true
            }
        }
    }
}
```

### 10. Get Settings
**GET** `/settings`

Retrieve user Pomodoro preferences. Creates default settings if none exist.

**Response (200):**
```json
{
    "success": true,
    "message": "Settings retrieved successfully",
    "data": {
        "settings": {
            "work_duration": 25,
            "short_break_duration": 5,
            "long_break_duration": 15,
            "sessions_before_long_break": 4,
            "auto_start_breaks": false,
            "notification_preferences": {
                "sound_enabled": true,
                "browser_notifications": true,
                "break_reminders": true
            }
        }
    }
}
```

## Error Responses

### Validation Error (422)
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "session_type": ["The session type field is required."]
    }
}
```

### Unauthorized (401)
```json
{
    "success": false,
    "message": "Unauthenticated."
}
```

### Not Found (404)
```json
{
    "success": false,
    "message": "Session not found"
}
```

### Server Error (500)
```json
{
    "success": false,
    "message": "Failed to start pomodoro session",
    "error": "Detailed error message"
}
```

## Integration with Study Logs

The Pomodoro system integrates seamlessly with the existing StudyLog system:

1. **Linking Sessions**: Start a work session linked to an existing study log using `study_log_id`
2. **Auto-Creation**: Completed work sessions automatically create StudyLog entries if not linked
3. **Statistics**: Pomodoro time is included in dashboard statistics
4. **Relationships**: StudyLogs can access their related Pomodoro sessions

## Usage Examples

### Frontend Timer Implementation
```javascript
// Start a work session
const startSession = async () => {
    const response = await fetch('/api/pomodoro/start', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({
            session_type: 'work',
            planned_duration: 25
        })
    });
    const data = await response.json();
    return data;
};

// Monitor active session
const checkActiveSession = async () => {
    const response = await fetch('/api/pomodoro/active', {
        headers: {
            'Authorization': `Bearer ${token}`
        }
    });
    const data = await response.json();
    return data.data.session;
};
```

### Break Suggestion Flow
1. Complete a work session
2. API returns `next_break_suggestion` with recommended break type and duration
3. Frontend can automatically start the suggested break or prompt user
4. After break completion, suggest next work session

This API provides a complete Pomodoro Timer implementation that can be easily integrated with any frontend framework while maintaining proper state management and user preferences.