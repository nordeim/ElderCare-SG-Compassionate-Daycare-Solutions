<?php

namespace App\Policies;

use App\Models\Center;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CenterPolicy
{
    use HandlesAuthorization;

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Center $center): bool
    {
        if ($center->status === 'published') {
            return true;
        }

        if ($user && in_array($user->role, ['admin', 'super_admin'])) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'super_admin']);
    }

    public function update(User $user, Center $center): bool
    {
        return in_array($user->role, ['admin', 'super_admin']);
    }

    public function delete(User $user, Center $center): bool
    {
        return $user->role === 'super_admin';
    }

    public function restore(User $user, Center $center): bool
    {
        return $user->role === 'super_admin';
    }

    public function forceDelete(User $user, Center $center): bool
    {
        return $user->role === 'super_admin';
    }

    public function publish(User $user, Center $center): bool
    {
        return in_array($user->role, ['admin', 'super_admin']);
    }

    public function manageServices(User $user, Center $center): bool
    {
        return in_array($user->role, ['admin', 'super_admin']);
    }

    public function manageStaff(User $user, Center $center): bool
    {
        return in_array($user->role, ['admin', 'super_admin']);
    }
}
