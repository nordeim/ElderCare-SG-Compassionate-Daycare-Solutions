<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Booking;
use App\Models\Center;
use App\Models\ContactSubmission;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:admin,super_admin');
    }

    public function index(Request $request): JsonResponse
    {
        $startDate = $request->has('start_date') 
            ? \Carbon\Carbon::parse($request->start_date)
            : now()->subDays(30);
        
        $endDate = $request->has('end_date')
            ? \Carbon\Carbon::parse($request->end_date)
            : now();

        $statistics = [
            'users' => $this->getUserStatistics($startDate, $endDate),
            'centers' => $this->getCenterStatistics(),
            'bookings' => $this->getBookingStatistics($startDate, $endDate),
            'testimonials' => $this->getTestimonialStatistics(),
            'contact_submissions' => $this->getContactStatistics($startDate, $endDate),
            'overview' => $this->getOverviewStatistics($startDate, $endDate),
        ];

        return ApiResponse::success($statistics, 'Dashboard statistics retrieved successfully');
    }

    protected function getUserStatistics($startDate, $endDate): array
    {
        return [
            'total' => User::count(),
            'new' => User::whereBetween('created_at', [$startDate, $endDate])->count(),
            'verified' => User::whereNotNull('email_verified_at')->count(),
            'unverified' => User::whereNull('email_verified_at')->count(),
            'by_role' => User::selectRaw('role, COUNT(*) as count')
                ->groupBy('role')
                ->pluck('count', 'role')
                ->toArray(),
        ];
    }

    protected function getCenterStatistics(): array
    {
        $centers = Center::select('id', 'capacity', 'current_occupancy', 'status')
            ->get();

        return [
            'total' => $centers->count(),
            'published' => $centers->where('status', 'published')->count(),
            'draft' => $centers->where('status', 'draft')->count(),
            'total_capacity' => $centers->sum('capacity'),
            'total_occupancy' => $centers->sum('current_occupancy'),
            'average_occupancy_rate' => $centers->count() > 0
                ? round(($centers->sum('current_occupancy') / $centers->sum('capacity')) * 100, 2)
                : 0,
        ];
    }

    protected function getBookingStatistics($startDate, $endDate): array
    {
        $bookings = Booking::whereBetween('created_at', [$startDate, $endDate])->get();
        $upcomingBookings = Booking::where('booking_date', '>=', now())
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();

        return [
            'total' => Booking::count(),
            'new' => $bookings->count(),
            'upcoming' => $upcomingBookings,
            'by_status' => Booking::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
            'by_type' => Booking::selectRaw('booking_type, COUNT(*) as count')
                ->groupBy('booking_type')
                ->pluck('count', 'booking_type')
                ->toArray(),
            'completion_rate' => $this->calculateCompletionRate(),
        ];
    }

    protected function getTestimonialStatistics(): array
    {
        $approved = Testimonial::where('status', 'approved')->get();

        return [
            'total' => Testimonial::count(),
            'pending' => Testimonial::where('status', 'pending')->count(),
            'approved' => $approved->count(),
            'rejected' => Testimonial::where('status', 'rejected')->count(),
            'spam' => Testimonial::where('status', 'spam')->count(),
            'average_rating' => $approved->count() > 0 
                ? round($approved->avg('rating'), 2)
                : null,
            'rating_distribution' => Testimonial::where('status', 'approved')
                ->selectRaw('rating, COUNT(*) as count')
                ->groupBy('rating')
                ->pluck('count', 'rating')
                ->toArray(),
        ];
    }

    protected function getContactStatistics($startDate, $endDate): array
    {
        return [
            'total' => ContactSubmission::count(),
            'new' => ContactSubmission::whereBetween('created_at', [$startDate, $endDate])->count(),
            'by_status' => ContactSubmission::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
        ];
    }

    protected function getOverviewStatistics($startDate, $endDate): array
    {
        return [
            'date_range' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
                'days' => $startDate->diffInDays($endDate),
            ],
            'recent_activity' => [
                'new_users' => User::whereBetween('created_at', [$startDate, $endDate])->count(),
                'new_bookings' => Booking::whereBetween('created_at', [$startDate, $endDate])->count(),
                'new_testimonials' => Testimonial::whereBetween('created_at', [$startDate, $endDate])->count(),
                'new_contacts' => ContactSubmission::whereBetween('created_at', [$startDate, $endDate])->count(),
            ],
        ];
    }

    protected function calculateCompletionRate(): float
    {
        $totalBookings = Booking::whereIn('status', ['completed', 'cancelled', 'no_show'])->count();
        
        if ($totalBookings === 0) {
            return 0;
        }

        $completedBookings = Booking::where('status', 'completed')->count();

        return round(($completedBookings / $totalBookings) * 100, 2);
    }
}
