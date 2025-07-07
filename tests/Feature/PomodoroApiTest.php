<?php

namespace Tests\Feature;

use App\Models\PomodoroSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PomodoroApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user and authenticate them for all tests
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_can_start_pomodoro_session()
    {
        $response = $this->postJson('/api/pomodoro/start', [
            'session_type' => 'work',
            'planned_duration' => 25
        ]);

        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Pomodoro session started successfully'
                 ])
                 ->assertJsonStructure([
                     'data' => [
                         'session' => [
                             'id',
                             'session_type',
                             'planned_duration',
                             'status',
                             'started_at'
                         ],
                         'time_remaining'
                     ]
                 ]);

        $this->assertDatabaseHas('pomodoro_sessions', [
            'user_id' => $this->user->id,
            'session_type' => 'work',
            'planned_duration' => 25,
            'status' => 'active'
        ]);
    }

    public function test_cannot_start_session_when_one_is_active()
    {
        // Create an active session
        PomodoroSession::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active'
        ]);

        $response = $this->postJson('/api/pomodoro/start', [
            'session_type' => 'work',
            'planned_duration' => 25
        ]);

        $response->assertStatus(409)
                 ->assertJson([
                     'success' => false,
                     'message' => 'You already have an active session. Please complete or cancel it first.'
                 ]);
    }

    public function test_can_get_active_session()
    {
        $session = PomodoroSession::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active'
        ]);

        $response = $this->getJson('/api/pomodoro/active');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         'session' => [
                             'id' => $session->id
                         ]
                     ]
                 ]);
    }

    public function test_can_pause_session()
    {
        $session = PomodoroSession::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active'
        ]);

        $response = $this->putJson("/api/pomodoro/{$session->id}/pause");

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Session paused successfully'
                 ]);

        $this->assertDatabaseHas('pomodoro_sessions', [
            'id' => $session->id,
            'status' => 'paused'
        ]);
    }

    public function test_can_resume_session()
    {
        $session = PomodoroSession::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'paused'
        ]);

        $response = $this->putJson("/api/pomodoro/{$session->id}/resume");

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Session resumed successfully'
                 ]);

        $this->assertDatabaseHas('pomodoro_sessions', [
            'id' => $session->id,
            'status' => 'active'
        ]);
    }

    public function test_can_complete_session()
    {
        $session = PomodoroSession::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active'
        ]);

        $response = $this->putJson("/api/pomodoro/{$session->id}/complete");

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Session completed successfully'
                 ]);

        $this->assertDatabaseHas('pomodoro_sessions', [
            'id' => $session->id,
            'status' => 'completed'
        ]);
    }

    public function test_can_cancel_session()
    {
        $session = PomodoroSession::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active'
        ]);

        $response = $this->putJson("/api/pomodoro/{$session->id}/cancel");

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Session cancelled successfully'
                 ]);

        $this->assertDatabaseHas('pomodoro_sessions', [
            'id' => $session->id,
            'status' => 'cancelled'
        ]);
    }

    public function test_can_get_session_history()
    {
        PomodoroSession::factory()->count(5)->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->getJson('/api/pomodoro/history');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Session history retrieved successfully'
                 ])
                 ->assertJsonStructure([
                     'data' => [
                         'data' => [
                             '*' => [
                                 'id',
                                 'session_type',
                                 'status',
                                 'planned_duration'
                             ]
                         ]
                     ]
                 ]);
    }

    public function test_can_get_statistics()
    {
        PomodoroSession::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'status' => 'completed'
        ]);

        $response = $this->getJson('/api/pomodoro/statistics');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Pomodoro statistics retrieved successfully'
                 ])
                 ->assertJsonStructure([
                     'data' => [
                         'overview',
                         'today',
                         'this_week',
                         'this_month'
                     ]
                 ]);
    }

    public function test_can_save_and_get_settings()
    {
        $settings = [
            'work_duration' => 30,
            'short_break_duration' => 10,
            'long_break_duration' => 20,
            'sessions_before_long_break' => 3,
            'auto_start_breaks' => true
        ];

        // Save settings
        $response = $this->postJson('/api/pomodoro/settings', $settings);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Settings saved successfully'
                 ]);

        // Get settings
        $response = $this->getJson('/api/pomodoro/settings');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         'settings' => $settings
                     ]
                 ]);
    }

}