<?php

namespace Tests\Unit\Observers;

use App\Models\AuditLog;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuditObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_user_writes_audit_log()
    {
        $user = User::factory()->create(['name' => 'Alice']);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'action' => 'created',
        ]);

        $log = AuditLog::where('auditable_type', User::class)->where('auditable_id', $user->id)->latest()->first();
        $this->assertNotNull($log->new_values);
        $this->assertEquals('Alice', $log->new_values['name']);
    }
}
