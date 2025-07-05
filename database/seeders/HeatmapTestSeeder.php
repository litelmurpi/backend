<?php

namespace Database\Seeders;

use App\Models\StudyLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class HeatmapTestSeeder extends Seeder
{
    public function run()
    {
        $user = User::where('email', 'test@example.com')->first();
        
        if (!$user) {
            $user = User::create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => bcrypt('password123')
            ]);
        }
        
        // Generate study logs for the past 6 months
        $startDate = Carbon::now()->subMonths(6);
        $endDate = Carbon::now();
        
        while ($startDate <= $endDate) {
            // Random chance of studying (70% chance)
            if (rand(1, 100) <= 70) {
                // Random number of sessions per day (1-3)
                $sessionsPerDay = rand(1, 3);
                
                for ($i = 0; $i < $sessionsPerDay; $i++) {
                    StudyLog::create([
                        'user_id' => $user->id,
                        'topic' => 'Study Session ' . $startDate->format('Y-m-d') . ' #' . ($i + 1),
                        'duration_minutes' => rand(15, 90),
                        'log_date' => $startDate->format('Y-m-d'),
                        'notes' => 'Generated for heatmap testing'
                    ]);
                }
            }
            
            $startDate->addDay();
        }
        
        echo "Heatmap test data seeded successfully!\n";
    }
}