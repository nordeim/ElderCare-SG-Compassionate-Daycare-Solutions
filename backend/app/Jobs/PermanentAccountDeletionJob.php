<?php

namespace App\Jobs;

use App\Services\User\AccountDeletionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PermanentAccountDeletionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function handle(AccountDeletionService $service): void
    {
        Log::info("Processing permanent account deletion for user {$this->userId}");

        $service->permanentlyDelete($this->userId);

        Log::info("User {$this->userId} permanently deleted");
    }
}
