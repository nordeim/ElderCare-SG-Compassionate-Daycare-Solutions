<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'super_admin']);
    }

    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id || in_array($user->role, ['admin', 'super_admin']);
    }

    public function create(User $user): bool
    {
        return $user->role === 'super_admin';
    }

    public function update(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return true;
        }

        if ($user->role === 'admin' && $model->role !== 'super_admin') {
            return true;
        }

        if ($user->role === 'super_admin') {
            return true;
        }

        return false;
    }

    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return true;
        }

        if ($user->role === 'super_admin' && $model->role !== 'super_admin') {
            return true;
        }

        return false;
    }

    public function restore(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return true;
        }

        return $user->role === 'super_admin';
    }

    public function forceDelete(User $user, User $model): bool
    {
        return $user->role === 'super_admin' && $model->role !== 'super_admin';
    }
}
