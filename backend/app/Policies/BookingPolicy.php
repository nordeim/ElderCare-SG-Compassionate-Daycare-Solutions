<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BookingPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'super_admin']);
    }

    public function view(User $user, Booking $booking): bool
    {
        if ($user->id === $booking->user_id) {
            return true;
        }

        if (in_array($user->role, ['admin', 'super_admin'])) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Booking $booking): bool
    {
        if ($user->id === $booking->user_id && in_array($booking->status, ['pending', 'confirmed'])) {
            return true;
        }

        if (in_array($user->role, ['admin', 'super_admin'])) {
            return true;
        }

        return false;
    }

    public function delete(User $user, Booking $booking): bool
    {
        if ($user->id === $booking->user_id && !in_array($booking->status, ['cancelled', 'completed', 'no_show'])) {
            return true;
        }

        if (in_array($user->role, ['admin', 'super_admin'])) {
            return true;
        }

        return false;
    }

    public function restore(User $user, Booking $booking): bool
    {
        return in_array($user->role, ['admin', 'super_admin']);
    }

    public function forceDelete(User $user, Booking $booking): bool
    {
        return $user->role === 'super_admin';
    }

    public function confirm(User $user, Booking $booking): bool
    {
        return in_array($user->role, ['admin', 'super_admin']);
    }

    public function complete(User $user, Booking $booking): bool
    {
        return in_array($user->role, ['admin', 'super_admin']);
    }
}
