<?php

namespace App\Services\User;

use App\Models\User;
use App\Jobs\PermanentAccountDeletionJob;
use Carbon\Carbon;

class AccountDeletionService
{
    public function requestDeletion(int $userId): Carbon
    {
        $user = User::findOrFail($userId);

        $user->delete();

        $scheduledDate = now()->addDays(30);
        PermanentAccountDeletionJob::dispatch($userId)->delay($scheduledDate);

        return $scheduledDate;
    }

    public function cancelDeletion(int $userId): User
    {
        $user = User::onlyTrashed()->findOrFail($userId);
        $user->restore();

        return $user;
    }

    public function permanentlyDelete(int $userId): void
    {
        $user = User::onlyTrashed()->find($userId);

        if (! $user) {
            return;
        }

        $this->anonymizeUserData($user);

        $user->forceDelete();
    }

    protected function anonymizeUserData(User $user): void
    {
        $user->bookings()->update(['questionnaire_responses' => null]);
    }
}
