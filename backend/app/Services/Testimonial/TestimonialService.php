<?php

namespace App\Services\Testimonial;

use App\Models\Testimonial;
use App\Models\Center;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TestimonialService
{
    /**
     * Get approved testimonials for a center
     *
     * @param int $centerId
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getApprovedForCenter(int $centerId, array $filters = []): LengthAwarePaginator
    {
        $query = Testimonial::with('user')
            ->where('center_id', $centerId)
            ->where('status', 'approved');

        // Filter by rating
        if (isset($filters['min_rating'])) {
            $query->where('rating', '>=', $filters['min_rating']);
        }

        // Sort
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($filters['per_page'] ?? 10);
    }

    /**
     * Get all pending testimonials (admin)
     *
     * @return Collection
     */
    public function getPendingTestimonials(): Collection
    {
        return Testimonial::with(['user', 'center'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Submit testimonial (user)
     *
     * @param int $userId
     * @param int $centerId
     * @param array $data
     * @return Testimonial
     * @throws \RuntimeException
     */
    public function submit(int $userId, int $centerId, array $data): Testimonial
    {
        // Check if center exists
        $center = Center::findOrFail($centerId);

        // Check if user has already submitted testimonial for this center
        $existing = Testimonial::where('user_id', $userId)
            ->where('center_id', $centerId)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existing) {
            throw new \RuntimeException(
                'You have already submitted a testimonial for this center'
            );
        }

        return Testimonial::create([
            'user_id' => $userId,
            'center_id' => $centerId,
            'title' => $data['title'],
            'content' => $data['content'],
            'rating' => $data['rating'],
            'status' => 'pending', // Requires moderation
        ]);
    }

    /**
     * Approve testimonial (admin)
     *
     * @param int $testimonialId
     * @param int $moderatorId
     * @return Testimonial
     */
    public function approve(int $testimonialId, int $moderatorId): Testimonial
    {
        $testimonial = Testimonial::findOrFail($testimonialId);

        if ($testimonial->status !== 'pending') {
            throw new \RuntimeException(
                'Can only approve pending testimonials'
            );
        }

        $testimonial->update([
            'status' => 'approved',
            'moderated_by' => $moderatorId,
            'moderated_at' => now(),
        ]);

        \Log::info('Testimonial approved', [
            'testimonial_id' => $testimonialId,
            'moderator_id' => $moderatorId,
        ]);

        return $testimonial->fresh();
    }

    /**
     * Reject testimonial (admin)
     *
     * @param int $testimonialId
     * @param int $moderatorId
     * @param string $reason
     * @return Testimonial
     */
    public function reject(int $testimonialId, int $moderatorId, string $reason): Testimonial
    {
        $testimonial = Testimonial::findOrFail($testimonialId);

        if ($testimonial->status !== 'pending') {
            throw new \RuntimeException(
                'Can only reject pending testimonials'
            );
        }

        $testimonial->update([
            'status' => 'rejected',
            'moderated_by' => $moderatorId,
            'moderated_at' => now(),
            'moderation_notes' => $reason,
        ]);

        \Log::info('Testimonial rejected', [
            'testimonial_id' => $testimonialId,
            'moderator_id' => $moderatorId,
            'reason' => $reason,
        ]);

        return $testimonial->fresh();
    }

    /**
     * Mark testimonial as spam (admin)
     *
     * @param int $testimonialId
     * @param int $moderatorId
     * @return Testimonial
     */
    public function markAsSpam(int $testimonialId, int $moderatorId): Testimonial
    {
        $testimonial = Testimonial::findOrFail($testimonialId);

        $testimonial->update([
            'status' => 'spam',
            'moderated_by' => $moderatorId,
            'moderated_at' => now(),
            'moderation_notes' => 'Marked as spam',
        ]);

        \Log::warning('Testimonial marked as spam', [
            'testimonial_id' => $testimonialId,
            'user_id' => $testimonial->user_id,
            'moderator_id' => $moderatorId,
        ]);

        return $testimonial->fresh();
    }

    /**
     * Calculate average rating for center
     *
     * @param int $centerId
     * @return float|null
     */
    public function calculateAverageRating(int $centerId): ?float
    {
        $average = Testimonial::where('center_id', $centerId)
            ->where('status', 'approved')
            ->avg('rating');

        return $average ? round($average, 2) : null;
    }

    /**
     * Get rating distribution for center
     *
     * @param int $centerId
     * @return array
     */
    public function getRatingDistribution(int $centerId): array
    {
        $distribution = Testimonial::where('center_id', $centerId)
            ->where('status', 'approved')
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->orderBy('rating', 'desc')
            ->pluck('count', 'rating')
            ->toArray();

        // Ensure all ratings (1-5) are represented
        $result = [];
        for ($i = 5; $i >= 1; $i--) {
            $result[$i] = $distribution[$i] ?? 0;
        }

        return $result;
    }

    /**
     * Get testimonials for moderation queue
     *
     * @param string $status
     * @return LengthAwarePaginator
     */
    public function getModerationQueue(string $status = 'pending'): LengthAwarePaginator
    {
        return Testimonial::with(['user', 'center', 'moderatedBy'])
            ->where('status', $status)
            ->orderBy('created_at', 'asc')
            ->paginate(20);
    }

    /**
     * Delete testimonial (user can delete own pending testimonials)
     *
     * @param int $testimonialId
     * @return bool
     */
    public function delete(int $testimonialId): bool
    {
        $testimonial = Testimonial::findOrFail($testimonialId);
        return $testimonial->delete();
    }

    /**
     * Get user's testimonials
     *
     * @param int $userId
     * @return Collection
     */
    public function getUserTestimonials(int $userId): Collection
    {
        return Testimonial::with('center')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
