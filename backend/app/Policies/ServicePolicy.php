<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ServicePolicy
{
    use HandlesAuthorization;

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Service $service): bool
    {
        if ($service->status === 'published') {
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

    public function update(User $user, Service $service): bool
    {
        return in_array($user->role, ['admin', 'super_admin']);
    }

    public function delete(User $user, Service $service): bool
    {
        return in_array($user->role, ['admin', 'super_admin']);
    }

    public function restore(User $user, Service $service): bool
    {
        return in_array($user->role, ['admin', 'super_admin']);
    }

    public function forceDelete(User $user, Service $service): bool
    {
        return $user->role === 'super_admin';
    }

    public function publish(User $user, Service $service): bool
    {
        return in_array($user->role, ['admin', 'super_admin']);
    }
}
