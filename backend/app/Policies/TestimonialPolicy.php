<?php

namespace App\Policies;

use App\Models\Testimonial;
use App\Models\User;

class TestimonialPolicy
{
    public function moderate(User $user): bool
    {
        return in_array($user->role ?? null, ['admin', 'super_admin']);
    }

    public function delete(User $user, Testimonial $testimonial): bool
    {
        // Users can delete their own pending testimonials
        if ($testimonial->status === 'pending' && $testimonial->user_id === $user->id) {
            return true;
        }

        // Admins can delete any testimonial
        return in_array($user->role ?? null, ['admin', 'super_admin']);
    }
}
