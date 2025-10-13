# Day 3: Core Business Services — Complete Implementation (28 Files)

**Branch**: `feature/phase3-core-business`

**Objective**: Implement center management, services, staff, FAQs, contact forms, and newsletter subscriptions with MOH compliance and Mailchimp integration.

---

## Table of Contents

1. [Services (6 files)](#services-6-files)
2. [Controllers (5 files)](#controllers-5-files)
3. [Request Validators (8 files)](#request-validators-8-files)
4. [API Resources (6 files)](#api-resources-6-files)
5. [Jobs (1 file)](#jobs-1-file)
6. [Policies (2 files)](#policies-2-files)
7. [Unit Tests (3 files)](#unit-tests-3-files)
8. [Feature Tests (3 files)](#feature-tests-3-files)
9. [Configuration & Validation](#configuration--validation)

---

## Services (6 files)

### 1. `backend/app/Services/Center/CenterService.php`

```php
<?php

namespace App\Services\Center;

use App\Models\Center;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class CenterService
{
    /**
     * Get paginated list of centers with filters
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Center::query()
            ->with(['services', 'staff', 'media'])
            ->withCount(['services', 'staff', 'bookings', 'testimonials']);

        // Status filter (default: published only for public)
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        } else {
            $query->where('status', 'published');
        }

        // City filter
        if (isset($filters['city'])) {
            $query->where('city', $filters['city']);
        }

        // Search by name/description
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('description', 'like', "%{$filters['search']}%");
            });
        }

        // MOH license status filter
        if (isset($filters['license_valid']) && $filters['license_valid']) {
            $query->where('license_expiry_date', '>', now());
        }

        // Capacity filter (min/max)
        if (isset($filters['min_capacity'])) {
            $query->where('capacity', '>=', $filters['min_capacity']);
        }

        if (isset($filters['max_capacity'])) {
            $query->where('capacity', '<=', $filters['max_capacity']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'name';
        $sortOrder = $filters['sort_order'] ?? 'asc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Get single center by slug with relationships
     *
     * @param string $slug
     * @return Center
     */
    public function getBySlug(string $slug): Center
    {
        return Center::with([
            'services' => fn($q) => $q->where('status', 'published')->orderBy('display_order'),
            'staff' => fn($q) => $q->where('status', 'active')->orderBy('display_order'),
            'media' => fn($q) => $q->orderBy('display_order'),
            'testimonials' => fn($q) => $q->where('status', 'approved')->latest()->limit(10),
        ])
        ->withCount(['services', 'staff'])
        ->where('slug', $slug)
        ->firstOrFail();
    }

    /**
     * Create new center
     *
     * @param array $data
     * @return Center
     */
    public function create(array $data): Center
    {
        // Auto-generate slug from name
        $data['slug'] = $this->generateUniqueSlug($data['name']);

        // Set meta fields if not provided
        $data['meta_title'] = $data['meta_title'] ?? Str::limit($data['name'], 60);
        $data['meta_description'] = $data['meta_description'] ?? Str::limit($data['short_description'] ?? $data['description'], 160);

        // Validate MOH license expiry
        if (Carbon::parse($data['license_expiry_date'])->isPast()) {
            throw new \InvalidArgumentException('MOH license expiry date must be in the future');
        }

        return Center::create($data);
    }

    /**
     * Update existing center
     *
     * @param int $centerId
     * @param array $data
     * @return Center
     */
    public function update(int $centerId, array $data): Center
    {
        $center = Center::findOrFail($centerId);

        // Update slug if name changed
        if (isset($data['name']) && $data['name'] !== $center->name) {
            $data['slug'] = $this->generateUniqueSlug($data['name'], $centerId);
        }

        // Validate license expiry if changed
        if (isset($data['license_expiry_date'])) {
            $expiryDate = Carbon::parse($data['license_expiry_date']);
            if ($expiryDate->isPast()) {
                throw new \InvalidArgumentException('MOH license expiry date must be in the future');
            }

            // Update accreditation status if expired
            if ($expiryDate->diffInDays(now()) <= 30) {
                \Log::warning("Center {$center->name} license expires soon", [
                    'center_id' => $center->id,
                    'expiry_date' => $expiryDate->toDateString(),
                ]);
            }
        }

        $center->update($data);

        return $center->fresh();
    }

    /**
     * Soft delete center
     *
     * @param int $centerId
     * @return bool
     */
    public function delete(int $centerId): bool
    {
        $center = Center::findOrFail($centerId);

        // Check if center has active bookings
        $activeBookings = $center->bookings()
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('booking_date', '>=', now())
            ->count();

        if ($activeBookings > 0) {
            throw new \RuntimeException(
                "Cannot delete center with {$activeBookings} active bookings. Please cancel or complete bookings first."
            );
        }

        return $center->delete();
    }

    /**
     * Publish center (change status to published)
     *
     * @param int $centerId
     * @return Center
     */
    public function publish(int $centerId): Center
    {
        $center = Center::findOrFail($centerId);

        // Validate required fields before publishing
        $this->validateForPublishing($center);

        $center->update(['status' => 'published']);

        return $center;
    }

    /**
     * Archive center
     *
     * @param int $centerId
     * @return Center
     */
    public function archive(int $centerId): Center
    {
        $center = Center::findOrFail($centerId);
        $center->update(['status' => 'archived']);

        return $center;
    }

    /**
     * Update center occupancy
     *
     * @param int $centerId
     * @param int $newOccupancy
     * @return Center
     */
    public function updateOccupancy(int $centerId, int $newOccupancy): Center
    {
        $center = Center::findOrFail($centerId);

        if ($newOccupancy > $center->capacity) {
            throw new \InvalidArgumentException(
                "Occupancy ({$newOccupancy}) cannot exceed capacity ({$center->capacity})"
            );
        }

        if ($newOccupancy < 0) {
            throw new \InvalidArgumentException('Occupancy cannot be negative');
        }

        $center->update(['current_occupancy' => $newOccupancy]);

        // Log if center is at capacity
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
     *
     * @return Collection Centers expiring within 30 days
     */
    public function checkLicenseExpiry(): Collection
    {
        $expiringCenters = Center::where('status', 'published')
            ->where('license_expiry_date', '<=', now()->addDays(30))
            ->where('license_expiry_date', '>', now())
            ->get();

        foreach ($expiringCenters as $center) {
            \Log::warning("MOH license expiring soon", [
                'center_id' => $center->id,
                'center_name' => $center->name,
                'expiry_date' => $center->license_expiry_date->toDateString(),
                'days_remaining' => $center->license_expiry_date->diffInDays(now()),
            ]);

            // TODO: Send email notification to admin
        }

        return $expiringCenters;
    }

    /**
     * Get center with full statistics
     *
     * @param int $centerId
     * @return array
     */
    public function getWithStatistics(int $centerId): array
    {
        $center = Center::with(['services', 'staff', 'bookings', 'testimonials'])
            ->withCount([
                'services',
                'staff',
                'bookings',
                'testimonials' => fn($q) => $q->where('status', 'approved'),
            ])
            ->findOrFail($centerId);

        // Calculate statistics
        $approvedTestimonials = $center->testimonials->where('status', 'approved');
        $averageRating = $approvedTestimonials->avg('rating');
        $occupancyRate = $center->capacity > 0 
            ? round(($center->current_occupancy / $center->capacity) * 100, 2)
            : 0;

        $upcomingBookings = $center->bookings()
            ->where('booking_date', '>=', now())
            ->whereIn('status', ['confirmed', 'pending'])
            ->count();

        return [
            'center' => $center,
            'statistics' => [
                'services_count' => $center->services_count,
                'staff_count' => $center->staff_count,
                'total_bookings' => $center->bookings_count,
                'upcoming_bookings' => $upcomingBookings,
                'approved_testimonials' => $center->testimonials_count,
                'average_rating' => $averageRating ? round($averageRating, 2) : null,
                'occupancy_rate' => $occupancyRate,
                'is_license_valid' => $center->license_expiry_date > now(),
                'license_expires_in_days' => $center->license_expiry_date->diffInDays(now()),
            ],
        ];
    }

    /**
     * Get distinct cities for filtering
     *
     * @return array
     */
    public function getAvailableCities(): array
    {
        return Center::where('status', 'published')
            ->distinct()
            ->pluck('city')
            ->sort()
            ->values()
            ->toArray();
    }

    /**
     * Generate unique slug for center
     *
     * @param string $name
     * @param int|null $excludeId
     * @return string
     */
    protected function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug exists
     *
     * @param string $slug
     * @param int|null $excludeId
     * @return bool
     */
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
     *
     * @param Center $center
     * @throws \RuntimeException
     */
    protected function validateForPublishing(Center $center): void
    {
        $errors = [];

        // Check required fields
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

        // Check if has at least one service
        if ($center->services()->count() === 0) {
            $errors[] = 'Center must have at least one service';
        }

        // Check if has media (photos)
        if ($center->media()->count() === 0) {
            $errors[] = 'Center must have at least one photo';
        }

        if (!empty($errors)) {
            throw new \RuntimeException(
                'Center cannot be published: ' . implode(', ', $errors)
            );
        }
    }
}
```

---

### 2. `backend/app/Services/Center/ServiceManagementService.php`

```php
<?php

namespace App\Services\Center;

use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ServiceManagementService
{
    /**
     * Get all services for a center
     *
     * @param int $centerId
     * @param bool $publishedOnly
     * @return Collection
     */
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

    /**
     * Create new service for center
     *
     * @param int $centerId
     * @param array $data
     * @return Service
     */
    public function create(int $centerId, array $data): Service
    {
        $data['center_id'] = $centerId;
        
        // Auto-generate slug from name
        $data['slug'] = $this->generateUniqueSlug($centerId, $data['name']);

        // Set display order to last if not specified
        if (!isset($data['display_order'])) {
            $maxOrder = Service::where('center_id', $centerId)->max('display_order') ?? 0;
            $data['display_order'] = $maxOrder + 1;
        }

        return Service::create($data);
    }

    /**
     * Update existing service
     *
     * @param int $serviceId
     * @param array $data
     * @return Service
     */
    public function update(int $serviceId, array $data): Service
    {
        $service = Service::findOrFail($serviceId);

        // Update slug if name changed
        if (isset($data['name']) && $data['name'] !== $service->name) {
            $data['slug'] = $this->generateUniqueSlug(
                $service->center_id,
                $data['name'],
                $serviceId
            );
        }

        $service->update($data);

        return $service->fresh();
    }

    /**
     * Soft delete service
     *
     * @param int $serviceId
     * @return bool
     */
    public function delete(int $serviceId): bool
    {
        $service = Service::findOrFail($serviceId);

        // Check if service has active bookings
        $activeBookings = $service->bookings()
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('booking_date', '>=', now())
            ->count();

        if ($activeBookings > 0) {
            throw new \RuntimeException(
                "Cannot delete service with {$activeBookings} active bookings"
            );
        }

        return $service->delete();
    }

    /**
     * Publish service
     *
     * @param int $serviceId
     * @return Service
     */
    public function publish(int $serviceId): Service
    {
        $service = Service::findOrFail($serviceId);
        $service->update(['status' => 'published']);

        return $service;
    }

    /**
     * Reorder services for a center
     *
     * @param int $centerId
     * @param array $orderArray Array of service IDs in new order
     * @return bool
     */
    public function reorder(int $centerId, array $orderArray): bool
    {
        foreach ($orderArray as $order => $serviceId) {
            Service::where('id', $serviceId)
                ->where('center_id', $centerId)
                ->update(['display_order' => $order + 1]);
        }

        return true;
    }

    /**
     * Generate unique slug for service within center
     *
     * @param int $centerId
     * @param string $name
     * @param int|null $excludeId
     * @return string
     */
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

    /**
     * Check if slug exists for center
     *
     * @param int $centerId
     * @param string $slug
     * @param int|null $excludeId
     * @return bool
     */
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
```

---

### 3. `backend/app/Services/Center/StaffService.php`

```php
<?php

namespace App\Services\Center;

use App\Models\Staff;
use Illuminate\Database\Eloquent\Collection;

class StaffService
{
    /**
     * Get all staff for a center
     *
     * @param int $centerId
     * @param bool $activeOnly
     * @return Collection
     */
    public function getStaffForCenter(int $centerId, bool $activeOnly = true): Collection
    {
        $query = Staff::where('center_id', $centerId)
            ->orderBy('display_order');

        if ($activeOnly) {
            $query->where('status', 'active');
        }

        return $query->get();
    }

    /**
     * Create new staff member
     *
     * @param int $centerId
     * @param array $data
     * @return Staff
     */
    public function create(int $centerId, array $data): Staff
    {
        $data['center_id'] = $centerId;

        // Set display order to last if not specified
        if (!isset($data['display_order'])) {
            $maxOrder = Staff::where('center_id', $centerId)->max('display_order') ?? 0;
            $data['display_order'] = $maxOrder + 1;
        }

        return Staff::create($data);
    }

    /**
     * Update existing staff member
     *
     * @param int $staffId
     * @param array $data
     * @return Staff
     */
    public function update(int $staffId, array $data): Staff
    {
        $staff = Staff::findOrFail($staffId);
        $staff->update($data);

        return $staff->fresh();
    }

    /**
     * Delete staff member
     *
     * @param int $staffId
     * @return bool
     */
    public function delete(int $staffId): bool
    {
        $staff = Staff::findOrFail($staffId);
        return $staff->delete();
    }

    /**
     * Reorder staff members for a center
     *
     * @param int $centerId
     * @param array $orderArray
     * @return bool
     */
    public function reorder(int $centerId, array $orderArray): bool
    {
        foreach ($orderArray as $order => $staffId) {
            Staff::where('id', $staffId)
                ->where('center_id', $centerId)
                ->update(['display_order' => $order + 1]);
        }

        return true;
    }

    /**
     * Get active staff count for center
     *
     * @param int $centerId
     * @return int
     */
    public function getActiveStaffCount(int $centerId): int
    {
        return Staff::where('center_id', $centerId)
            ->where('status', 'active')
            ->count();
    }
}
```

---

### 4. `backend/app/Services/Content/FAQService.php`

```php
<?php

namespace App\Services\Content;

use App\Models\FAQ;
use Illuminate\Database\Eloquent\Collection;

class FAQService
{
    /**
     * Get published FAQs by category
     *
     * @param string|null $category
     * @return Collection
     */
    public function getPublishedByCategory(?string $category = null): Collection
    {
        $query = FAQ::where('status', 'published')
            ->orderBy('display_order')
            ->orderBy('created_at');

        if ($category) {
            $query->where('category', $category);
        }

        return $query->get();
    }

    /**
     * Get all FAQs grouped by category
     *
     * @param bool $publishedOnly
     * @return array
     */
    public function getAllGroupedByCategory(bool $publishedOnly = true): array
    {
        $query = FAQ::query()->orderBy('display_order');

        if ($publishedOnly) {
            $query->where('status', 'published');
        }

        return $query->get()->groupBy('category')->toArray();
    }

    /**
     * Search FAQs (full-text search)
     *
     * @param string $searchTerm
     * @return Collection
     */
    public function search(string $searchTerm): Collection
    {
        return FAQ::where('status', 'published')
            ->where(function ($query) use ($searchTerm) {
                $query->where('question', 'like', "%{$searchTerm}%")
                      ->orWhere('answer', 'like', "%{$searchTerm}%");
            })
            ->orderBy('display_order')
            ->get();
    }

    /**
     * Create new FAQ
     *
     * @param array $data
     * @return FAQ
     */
    public function create(array $data): FAQ
    {
        // Set display order to last if not specified
        if (!isset($data['display_order'])) {
            $maxOrder = FAQ::where('category', $data['category'])->max('display_order') ?? 0;
            $data['display_order'] = $maxOrder + 1;
        }

        return FAQ::create($data);
    }

    /**
     * Update existing FAQ
     *
     * @param int $faqId
     * @param array $data
     * @return FAQ
     */
    public function update(int $faqId, array $data): FAQ
    {
        $faq = FAQ::findOrFail($faqId);
        $faq->update($data);

        return $faq->fresh();
    }

    /**
     * Delete FAQ
     *
     * @param int $faqId
     * @return bool
     */
    public function delete(int $faqId): bool
    {
        $faq = FAQ::findOrFail($faqId);
        return $faq->delete();
    }

    /**
     * Reorder FAQs within a category
     *
     * @param string $category
     * @param array $orderArray
     * @return bool
     */
    public function reorder(string $category, array $orderArray): bool
    {
        foreach ($orderArray as $order => $faqId) {
            FAQ::where('id', $faqId)
                ->where('category', $category)
                ->update(['display_order' => $order + 1]);
        }

        return true;
    }

    /**
     * Publish FAQ
     *
     * @param int $faqId
     * @return FAQ
     */
    public function publish(int $faqId): FAQ
    {
        $faq = FAQ::findOrFail($faqId);
        $faq->update(['status' => 'published']);

        return $faq;
    }

    /**
     * Get available FAQ categories
     *
     * @return array
     */
    public function getCategories(): array
    {
        return ['general', 'booking', 'services', 'pricing', 'accessibility'];
    }
}
```

---

### 5. `backend/app/Services/Contact/ContactService.php`

```php
<?php

namespace App\Services\Contact;

use App\Models\ContactSubmission;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class ContactService
{
    /**
     * Submit contact form
     *
     * @param array $data
     * @return ContactSubmission
     */
    public function submit(array $data): ContactSubmission
    {
        // Spam detection
        if ($this->detectSpam($data)) {
            $submission = ContactSubmission::create(array_merge($data, [
                'status' => 'spam',
            ]));

            \Log::warning('Spam contact submission detected', [
                'ip' => $data['ip_address'] ?? null,
                'email' => $data['email'],
            ]);

            // Still return success to user (don't reveal spam detection)
            return $submission;
        }

        // Create submission
        $submission = ContactSubmission::create(array_merge($data, [
            'status' => 'new',
        ]));

        // Notify admin (queue)
        $this->notifyAdmin($submission);

        return $submission;
    }

    /**
     * Mark submission as spam
     *
     * @param int $submissionId
     * @return ContactSubmission
     */
    public function markAsSpam(int $submissionId): ContactSubmission
    {
        $submission = ContactSubmission::findOrFail($submissionId);
        $submission->update(['status' => 'spam']);

        return $submission;
    }

    /**
     * Resolve submission
     *
     * @param int $submissionId
     * @return ContactSubmission
     */
    public function resolve(int $submissionId): ContactSubmission
    {
        $submission = ContactSubmission::findOrFail($submissionId);
        $submission->update(['status' => 'resolved']);

        return $submission;
    }

    /**
     * Update submission status
     *
     * @param int $submissionId
     * @param string $status
     * @return ContactSubmission
     */
    public function updateStatus(int $submissionId, string $status): ContactSubmission
    {
        $submission = ContactSubmission::findOrFail($submissionId);
        $submission->update(['status' => $status]);

        return $submission;
    }

    /**
     * Simple spam detection
     *
     * @param array $data
     * @return bool
     */
    protected function detectSpam(array $data): bool
    {
        $ipAddress = $data['ip_address'] ?? null;

        if (!$ipAddress) {
            return false;
        }

        // Rate limiting: max 3 submissions per hour per IP
        $key = 'contact-form:' . $ipAddress;
        
        if (RateLimiter::tooManyAttempts($key, 3)) {
            return true;
        }

        RateLimiter::hit($key, 3600); // 1 hour

        // Check for honeypot field (if implemented in frontend)
        if (isset($data['honeypot']) && !empty($data['honeypot'])) {
            return true;
        }

        // Check for suspicious patterns in message
        $message = strtolower($data['message'] ?? '');
        $spamKeywords = ['viagra', 'casino', 'lottery', 'bitcoin', 'cryptocurrency'];

        foreach ($spamKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Send notification to admin
     *
     * @param ContactSubmission $submission
     */
    protected function notifyAdmin(ContactSubmission $submission): void
    {
        // TODO: Implement email notification
        // Mail::to(config('mail.admin_email'))->queue(new ContactSubmissionNotification($submission));

        \Log::info('New contact submission received', [
            'submission_id' => $submission->id,
            'email' => $submission->email,
            'subject' => $submission->subject,
        ]);
    }
}
```

---

### 6. `backend/app/Services/Newsletter/MailchimpService.php`

```php
<?php

namespace App\Services\Newsletter;

use App\Models\Subscription;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MailchimpService
{
    protected string $apiKey;
    protected string $listId;
    protected string $dataCenter;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.mailchimp.api_key');
        $this->listId = config('services.mailchimp.list_id');
        
        // Extract datacenter from API key (format: key-us1)
        $parts = explode('-', $this->apiKey);
        $this->dataCenter = $parts[1] ?? 'us1';
        
        $this->baseUrl = "https://{$this->dataCenter}.api.mailchimp.com/3.0";
    }

    /**
     * Subscribe email to Mailchimp list (double opt-in)
     *
     * @param string $email
     * @param array $preferences
     * @return array
     */
    public function subscribe(string $email, array $preferences = []): array
    {
        try {
            $response = Http::withBasicAuth('apikey', $this->apiKey)
                ->post("{$this->baseUrl}/lists/{$this->listId}/members", [
                    'email_address' => $email,
                    'status' => 'pending', // Double opt-in (Mailchimp sends confirmation email)
                    'merge_fields' => $preferences['merge_fields'] ?? [],
                    'interests' => $preferences['interests'] ?? [],
                ]);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'subscriber_id' => $data['id'] ?? null,
                    'status' => $data['status'] ?? 'pending',
                ];
            }

            Log::error('Mailchimp subscription failed', [
                'email' => $email,
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return [
                'success' => false,
                'error' => $response->json()['detail'] ?? 'Unknown error',
            ];

        } catch (\Exception $e) {
            Log::error('Mailchimp API exception', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Unsubscribe email from Mailchimp list
     *
     * @param string $email
     * @return bool
     */
    public function unsubscribe(string $email): bool
    {
        try {
            $subscriberHash = md5(strtolower($email));

            $response = Http::withBasicAuth('apikey', $this->apiKey)
                ->patch("{$this->baseUrl}/lists/{$this->listId}/members/{$subscriberHash}", [
                    'status' => 'unsubscribed',
                ]);

            return $response->successful();

        } catch (\Exception $e) {
            Log::error('Mailchimp unsubscribe failed', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Update subscriber preferences
     *
     * @param string $email
     * @param array $preferences
     * @return bool
     */
    public function updatePreferences(string $email, array $preferences): bool
    {
        try {
            $subscriberHash = md5(strtolower($email));

            $response = Http::withBasicAuth('apikey', $this->apiKey)
                ->patch("{$this->baseUrl}/lists/{$this->listId}/members/{$subscriberHash}", [
                    'merge_fields' => $preferences['merge_fields'] ?? [],
                    'interests' => $preferences['interests'] ?? [],
                ]);

            return $response->successful();

        } catch (\Exception $e) {
            Log::error('Mailchimp update preferences failed', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Sync local subscription with Mailchimp
     *
     * @param int $subscriptionId
     * @return bool
     */
    public function syncSubscription(int $subscriptionId): bool
    {
        $subscription = Subscription::findOrFail($subscriptionId);

        $result = $this->subscribe(
            $subscription->email,
            $subscription->preferences ?? []
        );

        if ($result['success']) {
            $subscription->update([
                'mailchimp_subscriber_id' => $result['subscriber_id'],
                'mailchimp_status' => $result['status'],
                'last_synced_at' => now(),
            ]);

            return true;
        }

        return false;
    }

    /**
     * Handle Mailchimp webhook events
     *
     * @param array $payload
     * @return bool
     */
    public function handleWebhook(array $payload): bool
    {
        $type = $payload['type'] ?? null;
        $email = $payload['data']['email'] ?? null;

        if (!$email) {
            Log::warning('Mailchimp webhook missing email', ['payload' => $payload]);
            return false;
        }

        $subscription = Subscription::where('email', $email)->first();

        if (!$subscription) {
            Log::info('Mailchimp webhook for unknown email', ['email' => $email]);
            return true; // Not an error, just unknown subscriber
        }

        switch ($type) {
            case 'unsubscribe':
            case 'cleaned':
                $subscription->update([
                    'mailchimp_status' => $type === 'cleaned' ? 'cleaned' : 'unsubscribed',
                    'unsubscribed_at' => now(),
                    'last_synced_at' => now(),
                ]);
                Log::info("Mailchimp webhook: {$type}", ['email' => $email]);
                break;

            case 'subscribe':
                $subscription->update([
                    'mailchimp_status' => 'subscribed',
                    'subscribed_at' => now(),
                    'last_synced_at' => now(),
                ]);
                Log::info('Mailchimp webhook: subscribe', ['email' => $email]);
                break;

            default:
                Log::info('Mailchimp webhook: unhandled type', ['type' => $type, 'email' => $email]);
        }

        return true;
    }

    /**
     * Verify Mailchimp webhook signature
     *
     * @param string $payload
     * @param string $signature
     * @return bool
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $webhookSecret = config('services.mailchimp.webhook_secret');

        if (!$webhookSecret) {
            Log::warning('Mailchimp webhook secret not configured');
            return true; // Allow if not configured (for development)
        }

        $calculatedSignature = hash_hmac('sha256', $payload, $webhookSecret);

        return hash_equals($calculatedSignature, $signature);
    }
}
```

---

## Controllers (5 files)

### 7. `backend/app/Http/Controllers/Api/V1/CenterController.php`

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Center\StoreCenterRequest;
use App\Http\Requests\Center\UpdateCenterRequest;
use App\Http\Resources\CenterResource;
use App\Http\Responses\ApiResponse;
use App\Models\Center;
use App\Services\Center\CenterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CenterController extends Controller
{
    public function __construct(
        protected CenterService $centerService
    ) {
        // Apply auth middleware only for admin actions
        $this->middleware('auth:sanctum')->only(['store', 'update', 'destroy']);
        $this->middleware('role:admin,super_admin')->only(['store', 'update', 'destroy']);
    }

    /**
     * Get paginated list of centers
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'status', 'city', 'search', 'license_valid',
            'min_capacity', 'max_capacity', 'sort_by', 'sort_order', 'per_page'
        ]);

        $centers = $this->centerService->list($filters);

        return ApiResponse::paginated(
            $centers,
            CenterResource::class,
            'Centers retrieved successfully'
        );
    }

    /**
     * Get single center by slug
     *
     * @param string $slug
     * @return JsonResponse
     */
    public function show(string $slug): JsonResponse
    {
        try {
            $center = $this->centerService->getBySlug($slug);

            return ApiResponse::success(
                new CenterResource($center),
                'Center retrieved successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::notFound('Center not found');
        }
    }

    /**
     * Create new center (admin only)
     *
     * @param StoreCenterRequest $request
     * @return JsonResponse
     */
    public function store(StoreCenterRequest $request): JsonResponse
    {
        try {
            $center = $this->centerService->create($request->validated());

            return ApiResponse::created(
                new CenterResource($center),
                'Center created successfully'
            );
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        }
    }

    /**
     * Update existing center (admin only)
     *
     * @param UpdateCenterRequest $request
     * @param Center $center
     * @return JsonResponse
     */
    public function update(UpdateCenterRequest $request, Center $center): JsonResponse
    {
        try {
            $updatedCenter = $this->centerService->update($center->id, $request->validated());

            return ApiResponse::success(
                new CenterResource($updatedCenter),
                'Center updated successfully'
            );
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        }
    }

    /**
     * Delete center (admin only)
     *
     * @param Center $center
     * @return JsonResponse
     */
    public function destroy(Center $center): JsonResponse
    {
        try {
            $this->centerService->delete($center->id);

            return ApiResponse::success(null, 'Center deleted successfully');
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        }
    }
}
```

---

### 8. `backend/app/Http/Controllers/Api/V1/ServiceController.php`

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Service\StoreServiceRequest;
use App\Http\Requests\Service\UpdateServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Http\Responses\ApiResponse;
use App\Models\Center;
use App\Models\Service;
use App\Services\Center\ServiceManagementService;
use Illuminate\Http\JsonResponse;

class ServiceController extends Controller
{
    public function __construct(
        protected ServiceManagementService $serviceService
    ) {
        $this->middleware('auth:sanctum')->only(['store', 'update', 'destroy']);
        $this->middleware('role:admin,super_admin')->only(['store', 'update', 'destroy']);
    }

    /**
     * Get all services for a center
     *
     * @param Center $center
     * @return JsonResponse
     */
    public function index(Center $center): JsonResponse
    {
        $services = $this->serviceService->getServicesForCenter($center->id, true);

        return ApiResponse::success(
            ServiceResource::collection($services),
            'Services retrieved successfully'
        );
    }

    /**
     * Get single service
     *
     * @param Center $center
     * @param Service $service
     * @return JsonResponse
     */
    public function show(Center $center, Service $service): JsonResponse
    {
        // Verify service belongs to center
        if ($service->center_id !== $center->id) {
            return ApiResponse::notFound('Service not found for this center');
        }

        return ApiResponse::success(
            new ServiceResource($service->load('media')),
            'Service retrieved successfully'
        );
    }

    /**
     * Create new service (admin only)
     *
     * @param StoreServiceRequest $request
     * @param Center $center
     * @return JsonResponse
     */
    public function store(StoreServiceRequest $request, Center $center): JsonResponse
    {
        $service = $this->serviceService->create($center->id, $request->validated());

        return ApiResponse::created(
            new ServiceResource($service),
            'Service created successfully'
        );
    }

    /**
     * Update service (admin only)
     *
     * @param UpdateServiceRequest $request
     * @param Service $service
     * @return JsonResponse
     */
    public function update(UpdateServiceRequest $request, Service $service): JsonResponse
    {
        $updatedService = $this->serviceService->update($service->id, $request->validated());

        return ApiResponse::success(
            new ServiceResource($updatedService),
            'Service updated successfully'
        );
    }

    /**
     * Delete service (admin only)
     *
     * @param Service $service
     * @return JsonResponse
     */
    public function destroy(Service $service): JsonResponse
    {
        try {
            $this->serviceService->delete($service->id);

            return ApiResponse::success(null, 'Service deleted successfully');
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        }
    }
}
```

---

### 9. `backend/app/Http/Controllers/Api/V1/FAQController.php`

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\FAQ\StoreFAQRequest;
use App\Http\Requests\FAQ\UpdateFAQRequest;
use App\Http\Resources\FAQResource;
use App\Http\Responses\ApiResponse;
use App\Models\FAQ;
use App\Services\Content\FAQService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FAQController extends Controller
{
    public function __construct(
        protected FAQService $faqService
    ) {
        $this->middleware('auth:sanctum')->only(['store', 'update', 'destroy']);
        $this->middleware('role:admin,super_admin')->only(['store', 'update', 'destroy']);
    }

    /**
     * Get published FAQs (optionally filtered by category)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $category = $request->query('category');
        $search = $request->query('search');

        if ($search) {
            $faqs = $this->faqService->search($search);
        } elseif ($category) {
            $faqs = $this->faqService->getPublishedByCategory($category);
        } else {
            $faqs = $this->faqService->getAllGroupedByCategory(true);
            
            return ApiResponse::success(
                $faqs,
                'FAQs retrieved successfully'
            );
        }

        return ApiResponse::success(
            FAQResource::collection($faqs),
            'FAQs retrieved successfully'
        );
    }

    /**
     * Create new FAQ (admin only)
     *
     * @param StoreFAQRequest $request
     * @return JsonResponse
     */
    public function store(StoreFAQRequest $request): JsonResponse
    {
        $faq = $this->faqService->create($request->validated());

        return ApiResponse::created(
            new FAQResource($faq),
            'FAQ created successfully'
        );
    }

    /**
     * Update FAQ (admin only)
     *
     * @param UpdateFAQRequest $request
     * @param FAQ $faq
     * @return JsonResponse
     */
    public function update(UpdateFAQRequest $request, FAQ $faq): JsonResponse
    {
        $updatedFaq = $this->faqService->update($faq->id, $request->validated());

        return ApiResponse::success(
            new FAQResource($updatedFaq),
            'FAQ updated successfully'
        );
    }

    /**
     * Delete FAQ (admin only)
     *
     * @param FAQ $faq
     * @return JsonResponse
     */
    public function destroy(FAQ $faq): JsonResponse
    {
        $this->faqService->delete($faq->id);

        return ApiResponse::success(null, 'FAQ deleted successfully');
    }
}
```

---

### 10. `backend/app/Http/Controllers/Api/V1/ContactController.php`

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contact\ContactRequest;
use App\Http\Responses\ApiResponse;
use App\Services\Contact\ContactService;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
{
    public function __construct(
        protected ContactService $contactService
    ) {}

    /**
     * Submit contact form
     *
     * @param ContactRequest $request
     * @return JsonResponse
     */
    public function store(ContactRequest $request): JsonResponse
    {
        $data = array_merge($request->validated(), [
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $submission = $this->contactService->submit($data);

        return ApiResponse::created(
            ['submission_id' => $submission->id],
            'Thank you for contacting us. We will respond within 24-48 hours.'
        );
    }
}
```

---

### 11. `backend/app/Http/Controllers/Api/V1/SubscriptionController.php`

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Newsletter\SubscribeRequest;
use App\Http\Responses\ApiResponse;
use App\Jobs\SyncMailchimpSubscriptionJob;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Subscribe to newsletter
     *
     * @param SubscribeRequest $request
     * @return JsonResponse
     */
    public function store(SubscribeRequest $request): JsonResponse
    {
        // Check if already subscribed
        $existing = Subscription::where('email', $request->email)->first();

        if ($existing) {
            if ($existing->mailchimp_status === 'subscribed') {
                return ApiResponse::error(
                    'This email is already subscribed to our newsletter',
                    ['email' => ['Already subscribed']],
                    400
                );
            }

            // Resubscribe if previously unsubscribed
            $existing->update([
                'mailchimp_status' => 'pending',
                'preferences' => $request->preferences ?? null,
            ]);

            $subscription = $existing;
        } else {
            // Create new subscription
            $subscription = Subscription::create([
                'email' => $request->email,
                'mailchimp_status' => 'pending',
                'preferences' => $request->preferences ?? null,
            ]);
        }

        // Queue Mailchimp sync job
        SyncMailchimpSubscriptionJob::dispatch($subscription->id);

        return ApiResponse::created(
            ['subscription_id' => $subscription->id],
            'Subscription successful! Please check your email to confirm your subscription.'
        );
    }

    /**
     * Unsubscribe from newsletter
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $subscription = Subscription::where('email', $request->email)->first();

        if (!$subscription) {
            return ApiResponse::notFound('Email not found in our subscription list');
        }

        $subscription->update([
            'mailchimp_status' => 'unsubscribed',
            'unsubscribed_at' => now(),
        ]);

        // Queue Mailchimp sync job
        SyncMailchimpSubscriptionJob::dispatch($subscription->id);

        return ApiResponse::success(
            null,
            'You have been unsubscribed from our newsletter'
        );
    }
}
```

---

## Request Validators (8 files)

### 12. `backend/app/Http/Requests/Center/StoreCenterRequest.php`

```php
<?php

namespace App\Http\Requests\Center;

use Illuminate\Foundation\Http\FormRequest;

class StoreCenterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['required', 'string'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'size:6', 'regex:/^\d{6}$/'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^\+65[689]\d{7}$/'],
            'email' => ['required', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            
            // MOH Compliance
            'moh_license_number' => ['required', 'string', 'max:50', 'unique:centers,moh_license_number'],
            'license_expiry_date' => ['required', 'date', 'after:today'],
            'accreditation_status' => ['nullable', 'in:pending,accredited,not_accredited,expired'],
            
            // Operational
            'capacity' => ['required', 'integer', 'min:1', 'max:1000'],
            'current_occupancy' => ['nullable', 'integer', 'min:0'],
            'staff_count' => ['nullable', 'integer', 'min:0'],
            'staff_patient_ratio' => ['nullable', 'numeric', 'min:0'],
            
            // JSON fields
            'operating_hours' => ['nullable', 'array'],
            'medical_facilities' => ['nullable', 'array'],
            'amenities' => ['nullable', 'array'],
            'transport_info' => ['nullable', 'array'],
            'languages_supported' => ['nullable', 'array'],
            'government_subsidies' => ['nullable', 'array'],
            
            // Geolocation
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            
            // Status
            'status' => ['nullable', 'in:draft,published,archived'],
            
            // SEO
            'meta_title' => ['nullable', 'string', 'max:60'],
            'meta_description' => ['nullable', 'string', 'max:160'],
        ];
    }

    public function messages(): array
    {
        return [
            'postal_code.regex' => 'Postal code must be exactly 6 digits',
            'phone.regex' => 'Please provide a valid Singapore phone number (e.g., +6562345678)',
            'moh_license_number.unique' => 'This MOH license number is already registered',
            'license_expiry_date.after' => 'License expiry date must be in the future',
        ];
    }
}
```

---

### 13. `backend/app/Http/Requests/Center/UpdateCenterRequest.php`

```php
<?php

namespace App\Http\Requests\Center;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCenterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $centerId = $this->route('center')->id;

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['sometimes', 'string'],
            'address' => ['sometimes', 'string', 'max:500'],
            'city' => ['sometimes', 'string', 'max:100'],
            'postal_code' => ['sometimes', 'string', 'size:6', 'regex:/^\d{6}$/'],
            'phone' => ['sometimes', 'string', 'max:20', 'regex:/^\+65[689]\d{7}$/'],
            'email' => ['sometimes', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            
            'moh_license_number' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('centers', 'moh_license_number')->ignore($centerId),
            ],
            'license_expiry_date' => ['sometimes', 'date', 'after:today'],
            'accreditation_status' => ['sometimes', 'in:pending,accredited,not_accredited,expired'],
            
            'capacity' => ['sometimes', 'integer', 'min:1', 'max:1000'],
            'current_occupancy' => ['sometimes', 'integer', 'min:0', 'lte:capacity'],
            'staff_count' => ['sometimes', 'integer', 'min:0'],
            'staff_patient_ratio' => ['sometimes', 'numeric', 'min:0'],
            
            'operating_hours' => ['sometimes', 'array'],
            'medical_facilities' => ['sometimes', 'array'],
            'amenities' => ['sometimes', 'array'],
            'transport_info' => ['sometimes', 'array'],
            'languages_supported' => ['sometimes', 'array'],
            'government_subsidies' => ['sometimes', 'array'],
            
            'latitude' => ['sometimes', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'numeric', 'between:-180,180'],
            
            'status' => ['sometimes', 'in:draft,published,archived'],
            
            'meta_title' => ['sometimes', 'string', 'max:60'],
            'meta_description' => ['sometimes', 'string', 'max:160'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_occupancy.lte' => 'Current occupancy cannot exceed capacity',
        ];
    }
}
```

---

### 14. `backend/app/Http/Requests/Service/StoreServiceRequest.php`

```php
<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'price_unit' => ['nullable', 'in:hour,day,week,month'],
            'duration' => ['nullable', 'string', 'max:100'],
            'features' => ['nullable', 'array'],
            'status' => ['nullable', 'in:draft,published,archived'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'price.min' => 'Price cannot be negative',
            'price.max' => 'Price exceeds maximum allowed value',
        ];
    }
}
```

---

### 15. `backend/app/Http/Requests/Service/UpdateServiceRequest.php`

```php
<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'price' => ['sometimes', 'numeric', 'min:0', 'max:999999.99'],
            'price_unit' => ['sometimes', 'in:hour,day,week,month'],
            'duration' => ['sometimes', 'string', 'max:100'],
            'features' => ['sometimes', 'array'],
            'status' => ['sometimes', 'in:draft,published,archived'],
            'display_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
```

---

### 16. `backend/app/Http/Requests/FAQ/StoreFAQRequest.php`

```php
<?php

namespace App\Http\Requests\FAQ;

use Illuminate\Foundation\Http\FormRequest;

class StoreFAQRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['required', 'in:general,booking,services,pricing,accessibility'],
            'question' => ['required', 'string', 'max:500'],
            'answer' => ['required', 'string'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'in:draft,published'],
        ];
    }
}
```

---

### 17. `backend/app/Http/Requests/FAQ/UpdateFAQRequest.php`

```php
<?php

namespace App\Http\Requests\FAQ;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFAQRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['sometimes', 'in:general,booking,services,pricing,accessibility'],
            'question' => ['sometimes', 'string', 'max:500'],
            'answer' => ['sometimes', 'string'],
            'display_order' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', 'in:draft,published'],
        ];
    }
}
```

---

### 18. `backend/app/Http/Requests/Contact/ContactRequest.php`

```php
<?php

namespace App\Http\Requests\Contact;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Public endpoint
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^\+65[689]\d{7}$/'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
            'center_id' => ['nullable', 'exists:centers,id'],
            'honeypot' => ['nullable', 'max:0'], // Spam trap (should be empty)
        ];
    }

    public function messages(): array
    {
        return [
            'message.min' => 'Message must be at least 10 characters',
            'phone.regex' => 'Please provide a valid Singapore phone number',
        ];
    }
}
```

---

### 19. `backend/app/Http/Requests/Newsletter/SubscribeRequest.php`

```php
<?php

namespace App\Http\Requests\Newsletter;

use Illuminate\Foundation\Http\FormRequest;

class SubscribeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'preferences' => ['nullable', 'array'],
        ];
    }
}
```

---

## API Resources (6 files)

### 20. `backend/app/Http/Resources/CenterResource.php`

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CenterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,
            
            // Contact Info
            'address' => $this->address,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            
            // MOH Compliance
            'moh_license_number' => $this->moh_license_number,
            'license_expiry_date' => $this->license_expiry_date->toDateString(),
            'accreditation_status' => $this->accreditation_status,
            'is_license_valid' => $this->license_expiry_date > now(),
            
            // Operational
            'capacity' => $this->capacity,
            'current_occupancy' => $this->current_occupancy,
            'occupancy_rate' => $this->capacity > 0 
                ? round(($this->current_occupancy / $this->capacity) * 100, 2)
                : 0,
            'staff_count' => $this->staff_count,
            'staff_patient_ratio' => $this->staff_patient_ratio,
            
            // JSON Data
            'operating_hours' => $this->operating_hours,
            'medical_facilities' => $this->medical_facilities,
            'amenities' => $this->amenities,
            'transport_info' => $this->transport_info,
            'languages_supported' => $this->languages_supported,
            'government_subsidies' => $this->government_subsidies,
            
            // Geolocation
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            
            // Status
            'status' => $this->status,
            
            // SEO
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            
            // Timestamps
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            
            // Relationships (when loaded)
            'services' => ServiceResource::collection($this->whenLoaded('services')),
            'staff' => StaffResource::collection($this->whenLoaded('staff')),
            'media' => MediaResource::collection($this->whenLoaded('media')),
            'testimonials' => TestimonialResource::collection($this->whenLoaded('testimonials')),
            
            // Counts (when loaded)
            'services_count' => $this->when(isset($this->services_count), $this->services_count),
            'staff_count_total' => $this->when(isset($this->staff_count), $this->staff_count),
            'bookings_count' => $this->when(isset($this->bookings_count), $this->bookings_count),
            'testimonials_count' => $this->when(isset($this->testimonials_count), $this->testimonials_count),
            
            // Calculated fields (when requested)
            'average_rating' => $this->when($request->route()->getName() === 'centers.show', function () {
                $approvedTestimonials = $this->testimonials()->where('status', 'approved')->get();
                return $approvedTestimonials->count() > 0 
                    ? round($approvedTestimonials->avg('rating'), 2)
                    : null;
            }),
        ];
    }
}
```

---

### 21. `backend/app/Http/Resources/ServiceResource.php`

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price ? (float) $this->price : null,
            'price_unit' => $this->price_unit,
            'price_display' => $this->price 
                ? '$' . number_format($this->price, 2) . ($this->price_unit ? '/' . $this->price_unit : '')
                : 'Price on Application',
            'duration' => $this->duration,
            'features' => $this->features ?? [],
            'status' => $this->status,
            'display_order' => $this->display_order,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            
            // Relationships
            'center' => $this->when($request->route()->getName() !== 'centers.services.index', function () {
                return [
                    'id' => $this->center->id,
                    'name' => $this->center->name,
                    'slug' => $this->center->slug,
                ];
            }),
            'media' => MediaResource::collection($this->whenLoaded('media')),
        ];
    }
}
```

---

### 22. `backend/app/Http/Resources/StaffResource.php`

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'position' => $this->position,
            'qualifications' => $this->qualifications ?? [],
            'years_of_experience' => $this->years_of_experience,
            'bio' => $this->bio,
            'photo' => $this->photo,
            'display_order' => $this->display_order,
            'status' => $this->status,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
```

---

### 23. `backend/app/Http/Resources/FAQResource.php`

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FAQResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category' => $this->category,
            'question' => $this->question,
            'answer' => $this->answer,
            'display_order' => $this->display_order,
            'status' => $this->status,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
```

---

### 24. `backend/app/Http/Resources/ContactSubmissionResource.php`

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactSubmissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'subject' => $this->subject,
            'message' => $this->message,
            'status' => $this->status,
            'created_at' => $this->created_at->toIso8601String(),
            
            // Admin only fields
            'ip_address' => $this->when(
                $request->user()?->role === 'admin' || $request->user()?->role === 'super_admin',
                $this->ip_address
            ),
            'user_agent' => $this->when(
                $request->user()?->role === 'admin' || $request->user()?->role === 'super_admin',
                $this->user_agent
            ),
        ];
    }
}
```

---

### 25. `backend/app/Http/Resources/SubscriptionResource.php`

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'mailchimp_status' => $this->mailchimp_status,
            'subscribed_at' => $this->subscribed_at?->toIso8601String(),
            'unsubscribed_at' => $this->unsubscribed_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
```
