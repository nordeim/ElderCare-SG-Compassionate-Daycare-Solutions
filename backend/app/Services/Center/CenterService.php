<?php

namespace App\Services\Center;

use App\Models\Center;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class CenterService
{
    /**
     * Get paginated list of centers with filters
     */
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = Center::query()
            ->withCount(['services', 'staff', 'bookings', 'testimonials'])
            ->with(['media', 'translations']);

        if (!empty($filters['city'])) {
            $query->where('city', $filters['city']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['q'])) {
            $q = $filters['q'];
            $query->where(function ($qBuilder) use ($q) {
                $qBuilder->where('name', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Get single center by slug with relationships
     */
    public function getBySlug(string $slug): Center
    {
        return Center::with([
            'services.media',
            'staff.media',
            'media',
            'translations'
        ])->where('slug', $slug)->firstOrFail();
    }

    /**
     * Create new center
     */
    public function create(array $data): Center
    {
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateUniqueSlug($data['name'] ?? 'center');
        }

        return Center::create($data);
    }

    /**
     * Update existing center
     */
    public function update(int $centerId, array $data): Center
    {
        $center = Center::findOrFail($centerId);

        if (isset($data['name']) && $data['name'] !== $center->name) {
            $data['slug'] = $this->generateUniqueSlug($data['name'], $centerId);
        }

        $center->update($data);

        // Notify if license expiry approaching
        if (!empty($center->license_expiry_date) && $center->license_expiry_date->isFuture()) {
            $days = $center->license_expiry_date->diffInDays(now());
            if ($days <= 30) {
                \Log::warning("Center {$center->name} license expires soon", [
                    'center_id' => $center->id,
                    'days_remaining' => $days,
                ]);
            }
        }

        return $center->fresh();
    }

    /**
     * Soft delete center
     */
    public function delete(int $centerId): bool
    {
        $center = Center::findOrFail($centerId);

        $activeBookings = $center->bookings()
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('booking_date', '>=', now())
            ->count();

        if ($activeBookings > 0) {
            throw new \RuntimeException("Cannot delete center with {$activeBookings} active bookings. Please cancel or complete bookings first.");
        }

        return $center->delete();
    }

    /**
     * Publish center (change status to published)
     */
    public function publish(int $centerId): Center
    {
        $center = Center::findOrFail($centerId);
        $this->validateForPublishing($center);
        $center->update(['status' => 'published']);
        return $center;
    }

    /**
     * Archive center
     */
    public function archive(int $centerId): Center
    {
        $center = Center::findOrFail($centerId);
        $center->update(['status' => 'archived']);
        return $center;
    }

    /**
     * Update center occupancy
     */
    public function updateOccupancy(int $centerId, int $newOccupancy): Center
    {
        $center = Center::findOrFail($centerId);

        if ($newOccupancy > $center->capacity) {
            throw new \RuntimeException("Occupancy ({$newOccupancy}) cannot exceed capacity ({$center->capacity})");
        }

        $center->update(['current_occupancy' => $newOccupancy]);

        if ($newOccupancy >= $center->capacity) {
            \Log::info("Center {$center->name} is at full capacity", [
                'center_id' => $center->id,
                'capacity' => $center->capacity,
            ]);
        }

        return $center;
    }

    /**
     * Check MOH license expiry for all centers and alert if expiring soon
     * @return Collection
     */
    public function checkLicenseExpiry(): Collection
    {
        $expiringCenters = Center::where('status', 'published')
            ->whereBetween('license_expiry_date', [now(), now()->addDays(30)])
            ->get();

        foreach ($expiringCenters as $center) {
            \Log::warning("Center license expiring soon", [
                'center_id' => $center->id,
                'center_name' => $center->name,
                'expiry_date' => $center->license_expiry_date->toDateString(),
                'days_remaining' => $center->license_expiry_date->diffInDays(now()),
            ]);
        }

        return $expiringCenters;
    }

    /**
     * Get center with full statistics
     */
    public function getWithStatistics(int $centerId): array
    {
        $center = Center::with(['services', 'staff', 'bookings', 'testimonials'])
            ->withCount(['services', 'staff', 'bookings', 'testimonials'])
            ->findOrFail($centerId);

        $approvedTestimonials = $center->testimonials->where('status', 'approved');

        $occupancyRate = $center->capacity > 0
            ? round(($center->current_occupancy / $center->capacity) * 100, 2)
            : 0;

        $upcomingBookings = $center->bookings()
            ->where('booking_date', '>=', now())
            ->orderBy('booking_date')
            ->limit(10)
            ->get();

        return [
            'center' => $center,
            'services_count' => $center->services_count,
            'staff_count' => $center->staff_count,
            'total_bookings' => $center->bookings_count,
            'approved_testimonials' => $approvedTestimonials->count(),
            'occupancy_rate' => $occupancyRate,
            'upcoming_bookings' => $upcomingBookings,
            'is_license_valid' => $center->license_expiry_date > now(),
            'license_expires_in_days' => $center->license_expiry_date->diffInDays(now()),
        ];
    }

    /**
     * Generate unique slug for center
     */
    protected function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $counter = 1;

        while ($this->slugExists($slug, $excludeId)) {
            $slug = $original . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = Center::where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Validate center is ready for publishing
     */
    protected function validateForPublishing(Center $center): void
    {
        $errors = [];

        if (empty($center->description)) {
            $errors[] = 'Description is required';
        }

        if (empty($center->moh_license_number)) {
            $errors[] = 'MOH license number is required';
        }

        if (!$center->license_expiry_date || $center->license_expiry_date->isPast()) {
            $errors[] = 'Valid MOH license is required';
        }

        if ($center->capacity <= 0) {
            $errors[] = 'Capacity must be greater than zero';
        }

        if ($center->services()->count() === 0) {
            $errors[] = 'Center must have at least one service';
        }

        if ($center->media()->count() === 0) {
            $errors[] = 'Center must have at least one photo';
        }

        if (!empty($errors)) {
            throw new \RuntimeException('Center cannot be published: ' . implode(', ', $errors));
        }
    }
}
