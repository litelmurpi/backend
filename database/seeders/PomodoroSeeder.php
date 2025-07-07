<?php

namespace Database\Seeders;

use App\Models\PomodoroSession;
use App\Models\User;
use App\Models\UserPomodoroSetting;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PomodoroSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create a demo user
        $user = User::firstOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => 'Demo User',
                'password' => bcrypt('password'),
            ]
        );

        // Create user pomodoro settings
        UserPomodoroSetting::firstOrCreate(
            ['user_id' => $user->id],
            [
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
            ]
        );

        // Create some completed pomodoro sessions for demo
        $now = Carbon::now();
        
        // Sessions from today
        PomodoroSession::create([
            'user_id' => $user->id,
            'session_type' => 'work',
            'planned_duration' => 25,
            'actual_duration' => 25,
            'status' => 'completed',
            'started_at' => $now->copy()->subHours(3),
            'completed_at' => $now->copy()->subHours(3)->addMinutes(25),
            'notes' => 'Morning focus session'
        ]);

        PomodoroSession::create([
            'user_id' => $user->id,
            'session_type' => 'short_break',
            'planned_duration' => 5,
            'actual_duration' => 5,
            'status' => 'completed',
            'started_at' => $now->copy()->subHours(3)->addMinutes(25),
            'completed_at' => $now->copy()->subHours(3)->addMinutes(30)
        ]);

        PomodoroSession::create([
            'user_id' => $user->id,
            'session_type' => 'work',
            'planned_duration' => 25,
            'actual_duration' => 30,
            'status' => 'completed',
            'started_at' => $now->copy()->subHours(2),
            'completed_at' => $now->copy()->subHours(2)->addMinutes(30),
            'notes' => 'Deep work session - went overtime'
        ]);

        // Sessions from yesterday
        $yesterday = $now->copy()->subDay();
        
        for ($i = 0; $i < 4; $i++) {
            PomodoroSession::create([
                'user_id' => $user->id,
                'session_type' => 'work',
                'planned_duration' => 25,
                'actual_duration' => 25,
                'status' => 'completed',
                'started_at' => $yesterday->copy()->addHours($i * 2),
                'completed_at' => $yesterday->copy()->addHours($i * 2)->addMinutes(25),
                'notes' => "Work session " . ($i + 1)
            ]);
        }

        // A cancelled session
        PomodoroSession::create([
            'user_id' => $user->id,
            'session_type' => 'work',
            'planned_duration' => 25,
            'actual_duration' => 10,
            'status' => 'cancelled',
            'started_at' => $now->copy()->subHour(),
            'completed_at' => $now->copy()->subHour()->addMinutes(10),
            'notes' => 'Interrupted by meeting'
        ]);

        $this->command->info('Pomodoro demo data created successfully!');
    }
}
