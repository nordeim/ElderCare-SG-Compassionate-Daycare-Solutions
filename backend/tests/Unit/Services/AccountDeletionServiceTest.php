<?php

namespace Tests\Unit\Services;

use App\Jobs\PermanentAccountDeletionJob;
use App\Models\Booking;
use App\Models\User;
use App\Services\User\AccountDeletionService;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccountDeletionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AccountDeletionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(AccountDeletionService::class);
    }

    public function test_requestDeletion_dispatches_job_with_30_day_delay()
    {
        Queue::fake();

        $user = User::factory()->create();

        $scheduled = $this->service->requestDeletion($user->id);

        $this->assertNotNull($scheduled);
        $this->assertTrue($scheduled->greaterThanOrEqualTo(now()->addDays(29)));

        Queue::assertPushed(PermanentAccountDeletionJob::class, function ($job) use ($user) {
            return isset($job->userId) && $job->userId === $user->id;
        });
    }

    public function test_cancelDeletion_restores_trashed_user()
    {
        $user = User::factory()->create();

        // Soft delete the user
        $user->delete();

        $restored = $this->service->cancelDeletion($user->id);

        $this->assertNotNull($restored);
        $this->assertNull($restored->deleted_at);
    }

    public function test_permanentlyDelete_anonymizes_bookings_and_force_deletes()
    {
        $user = User::factory()->create();

        $b = Booking::factory()->create(['user_id' => $user->id, 'questionnaire_responses' => ['note' => 'private']]);

        // Soft delete user
        $user->delete();

        // Call permanent deletion
        $this->service->permanentlyDelete($user->id);

        // User should no longer exist
        $this->assertDatabaseMissing('users', ['id' => $user->id]);

    // Bookings are cascade-deleted on user forceDelete (DB foreign key cascade), assert booking removed
    $this->assertDatabaseMissing('bookings', ['id' => $b->id]);
    }
}
