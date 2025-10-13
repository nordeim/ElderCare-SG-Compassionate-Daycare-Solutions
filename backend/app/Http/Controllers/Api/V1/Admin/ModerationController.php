<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContactSubmissionResource;
use App\Http\Resources\TestimonialResource;
use App\Http\Responses\ApiResponse;
use App\Models\ContactSubmission;
use App\Models\Testimonial;
use App\Services\Contact\ContactService;
use App\Services\Testimonial\TestimonialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModerationController extends Controller
{
    public function __construct(
        protected TestimonialService $testimonialService,
        protected ContactService $contactService
    ) {
        $this->middleware('auth:sanctum');
        $this->middleware('role:admin,super_admin');
    }

    public function pendingTestimonials(): JsonResponse
    {
        $testimonials = $this->testimonialService->getModerationQueue('pending');

        return ApiResponse::paginated(
            $testimonials,
            TestimonialResource::class,
            'Pending testimonials retrieved successfully'
        );
    }

    public function approveTestimonial(Testimonial $testimonial, Request $request): JsonResponse
    {
        try {
            $approved = $this->testimonialService->approve(
                $testimonial->id,
                $request->user()->id
            );

            return ApiResponse::success(
                new TestimonialResource($approved),
                'Testimonial approved successfully'
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        }
    }

    public function rejectTestimonial(Testimonial $testimonial, Request $request): JsonResponse
    {
        $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        try {
            $rejected = $this->testimonialService->reject(
                $testimonial->id,
                $request->user()->id,
                $request->reason
            );

            return ApiResponse::success(
                new TestimonialResource($rejected),
                'Testimonial rejected'
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        }
    }

    public function spamTestimonial(Testimonial $testimonial, Request $request): JsonResponse
    {
        $spam = $this->testimonialService->markAsSpam(
            $testimonial->id,
            $request->user()->id
        );

        return ApiResponse::success(
            new TestimonialResource($spam),
            'Testimonial marked as spam'
        );
    }

    public function contactSubmissions(Request $request): JsonResponse
    {
        $query = ContactSubmission::with(['user', 'center']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $submissions = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return ApiResponse::paginated(
            $submissions,
            ContactSubmissionResource::class,
            'Contact submissions retrieved successfully'
        );
    }

    public function updateSubmissionStatus(ContactSubmission $submission, Request $request): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'in:new,in_progress,resolved,spam'],
        ]);

        $updated = $this->contactService->updateStatus(
            $submission->id,
            $request->status
        );

        return ApiResponse::success(
            new ContactSubmissionResource($updated),
            'Submission status updated successfully'
        );
    }

    public function statistics(): JsonResponse
    {
        $stats = [
            'testimonials' => [
                'pending' => Testimonial::where('status', 'pending')->count(),
                'total_today' => Testimonial::whereDate('created_at', today())->count(),
            ],
            'contact_submissions' => [
                'new' => ContactSubmission::where('status', 'new')->count(),
                'in_progress' => ContactSubmission::where('status', 'in_progress')->count(),
                'total_today' => ContactSubmission::whereDate('created_at', today())->count(),
            ],
        ];

        return ApiResponse::success($stats, 'Moderation statistics retrieved');
    }
}
