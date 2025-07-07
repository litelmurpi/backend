<?php

namespace Tests\Unit;

use App\Models\PomodoroSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PomodoroSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_pomodoro_session()
    {
        $user = User::factory()->create();
        
        $session = PomodoroSession::create([
            'user_id' => $user->id,
            'session_type' => 'work',
            'planned_duration' => 25,
            'status' => 'active',
            'started_at' => Carbon::now()
        ]);

        $this->assertInstanceOf(PomodoroSession::class, $session);
        $this->assertEquals('work', $session->session_type);
        $this->assertEquals(25, $session->planned_duration);
        $this->assertEquals('active', $session->status);
    }

    public function test_session_belongs_to_user()
    {
        $user = User::factory()->create();
        $session = PomodoroSession::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $session->user);
        $this->assertEquals($user->id, $session->user->id);
    }

    public function test_can_pause_active_session()
    {
        $session = PomodoroSession::factory()->create([
            'status' => 'active',
            'started_at' => Carbon::now()->subMinutes(10)
        ]);

        $this->assertTrue($session->canBePaused());
        
        $session->pause();
        
        $this->assertEquals('paused', $session->status);
        $this->assertNotNull($session->actual_duration);
    }

    public function test_can_resume_paused_session()
    {
        $session = PomodoroSession::factory()->create([
            'status' => 'paused',
            'actual_duration' => 10
        ]);

        $this->assertTrue($session->canBeResumed());
        
        $session->resume();
        
        $this->assertEquals('active', $session->status);
    }

    public function test_can_complete_session()
    {
        $session = PomodoroSession::factory()->create([
            'status' => 'active',
            'started_at' => Carbon::now()->subMinutes(25)
        ]);

        $this->assertTrue($session->canBeCompleted());
        
        $session->complete();
        
        $this->assertEquals('completed', $session->status);
        $this->assertNotNull($session->completed_at);
        $this->assertNotNull($session->actual_duration);
    }

    public function test_formatted_duration_accessor()
    {
        $session = PomodoroSession::factory()->create([
            'planned_duration' => 25,
            'actual_duration' => null
        ]);

        $this->assertEquals('25 minutes', $session->formatted_duration);

        $session = PomodoroSession::factory()->create([
            'planned_duration' => 90,
            'actual_duration' => null
        ]);

        $this->assertEquals('1h 30m', $session->formatted_duration);

        // Test with actual duration
        $session = PomodoroSession::factory()->create([
            'planned_duration' => 25,
            'actual_duration' => 30
        ]);

        $this->assertEquals('30 minutes', $session->formatted_duration);
    }

    public function test_scopes_work_correctly()
    {
        $user = User::factory()->create();
        
        PomodoroSession::factory()->create([
            'user_id' => $user->id,
            'status' => 'active',
            'session_type' => 'work'
        ]);
        
        PomodoroSession::factory()->create([
            'user_id' => $user->id,
            'status' => 'completed',
            'session_type' => 'work'
        ]);

        $this->assertEquals(1, PomodoroSession::active()->count());
        $this->assertEquals(1, PomodoroSession::completed()->count());
        $this->assertEquals(2, PomodoroSession::work()->count());
    }
}
