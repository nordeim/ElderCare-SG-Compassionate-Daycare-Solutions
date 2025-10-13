<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Center;
use App\Models\Service;
use App\Policies\UserPolicy;
use App\Policies\CenterPolicy;
use App\Policies\ServicePolicy;
use App\Policies\BookingPolicy;
use App\Policies\TestimonialPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
    User::class => UserPolicy::class,
    Center::class => CenterPolicy::class,
    Service::class => ServicePolicy::class,
    \App\Models\Booking::class => BookingPolicy::class,
    \App\Models\Testimonial::class => TestimonialPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
