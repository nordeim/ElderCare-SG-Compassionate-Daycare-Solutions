<?php

namespace App\Services\Center;

use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ServiceManagementService
{
    /** Get all services for a center */
    public function getServicesForCenter(int $centerId, bool $publishedOnly = false): Collection
    {
        $query = Service::where('center_id', $centerId)
            ->with('media')
            ->orderBy('display_order');

        if ($publishedOnly) {
            $query->where('status', 'published');
        }

        return $query->get();
    }

    /** Create new service for center */
    public function create(int $centerId, array $data): Service
    {
        $data['center_id'] = $centerId;
        $data['slug'] = $this->generateUniqueSlug($centerId, $data['name']);

        if (!isset($data['display_order'])) {
            $maxOrder = Service::where('center_id', $centerId)->max('display_order') ?? 0;
            $data['display_order'] = $maxOrder + 1;
        }

        return Service::create($data);
    }

    /** Update existing service */
    public function update(int $serviceId, array $data): Service
    {
        $service = Service::findOrFail($serviceId);

        if (isset($data['name']) && $data['name'] !== $service->name) {
            $data['slug'] = $this->generateUniqueSlug($service->center_id, $data['name'], $serviceId);
        }

        $service->update($data);

        return $service->fresh();
    }

    /** Soft delete service */
    public function delete(int $serviceId): bool
    {
        $service = Service::findOrFail($serviceId);

        $activeBookings = $service->bookings()
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('booking_date', '>=', now())
            ->count();

        if ($activeBookings > 0) {
            throw new \RuntimeException("Cannot delete service with {$activeBookings} active bookings");
        }

        return $service->delete();
    }

    /** Publish service */
    public function publish(int $serviceId): Service
    {
        $service = Service::findOrFail($serviceId);
        $service->update(['status' => 'published']);
        return $service;
    }

    /** Reorder services for a center */
    public function reorder(int $centerId, array $orderArray): bool
    {
        foreach ($orderArray as $order => $serviceId) {
            Service::where('id', $serviceId)
                ->where('center_id', $centerId)
                ->update(['display_order' => $order + 1]);
        }

        return true;
    }

    protected function generateUniqueSlug(int $centerId, string $name, ?int $excludeId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($centerId, $slug, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected function slugExists(int $centerId, string $slug, ?int $excludeId = null): bool
    {
        $query = Service::where('center_id', $centerId)
            ->where('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
