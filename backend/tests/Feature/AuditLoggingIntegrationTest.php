<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuditLoggingIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_and_delete_create_audit_entries()
    {
        $user = User::factory()->create(['name' => 'Bob']);

        $user->update(['name' => 'Bobby']);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'action' => 'updated',
        ]);

        $log = AuditLog::where('auditable_type', User::class)->where('auditable_id', $user->id)->byAction('updated')->latest()->first();
        $this->assertEquals('Bob', $log->old_values['name']);
        $this->assertEquals('Bobby', $log->new_values['name']);

        $user->delete();

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'action' => 'deleted',
        ]);
    }
}
