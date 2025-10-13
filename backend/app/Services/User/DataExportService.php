<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

class DataExportService
{
    public function exportUserData(int $userId): array
    {
        $user = User::with([
            'profile',
            'bookings.center',
            'bookings.service',
            'testimonials.center',
            'consents',
            'auditLogs',
        ])->findOrFail($userId);

        $exportData = [
            'export_date' => now()->toIso8601String(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'preferred_language' => $user->preferred_language ?? null,
                'email_verified_at' => $user->email_verified_at?->toIso8601String(),
                'created_at' => $user->created_at->toIso8601String(),
            ],
            'profile' => $user->profile ? [
                'bio' => $user->profile->bio,
                'birth_date' => $user->profile->birth_date?->toDateString(),
                'address' => $user->profile->address,
                'city' => $user->profile->city,
                'postal_code' => $user->profile->postal_code,
            ] : null,
            'bookings' => $user->bookings->map(fn($booking) => [
                'booking_number' => $booking->booking_number,
                'center_name' => $booking->center->name ?? null,
                'service_name' => $booking->service?->name ?? null,
                'booking_date' => $booking->booking_date?->toDateString(),
                'booking_time' => $booking->booking_time?->toTimeString(),
                'status' => $booking->status,
                'questionnaire_responses' => $booking->questionnaire_responses,
                'created_at' => $booking->created_at?->toIso8601String(),
            ]),
            'testimonials' => $user->testimonials->map(fn($t) => [
                'center_name' => $t->center->name ?? null,
                'title' => $t->title,
                'content' => $t->content,
                'rating' => $t->rating,
                'status' => $t->status,
                'created_at' => $t->created_at?->toIso8601String(),
            ]),
            'consents' => $user->consents->map(fn($c) => [
                'type' => $c->consent_type,
                'given' => $c->consent_given,
                'version' => $c->consent_version,
                'created_at' => $c->created_at->toIso8601String(),
            ]),
            'audit_logs' => $user->auditLogs->map(fn($log) => [
                'action' => $log->action,
                'model' => $log->auditable_type,
                'created_at' => $log->created_at->toIso8601String(),
            ]),
        ];

        $filename = "user-data-export-{$userId}-" . now()->format('YmdHis') . '.json';
        $path = "exports/{$filename}";

        Storage::disk('private')->put($path, json_encode($exportData, JSON_PRETTY_PRINT));

        $url = Storage::disk('private')->temporaryUrl($path, now()->addHour());

        return [
            'path' => $path,
            'url' => $url,
            'expires_at' => now()->addHour()->toIso8601String(),
        ];
    }
}
