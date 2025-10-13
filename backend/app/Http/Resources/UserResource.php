<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'preferred_language' => $this->preferred_language,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'profile' => $this->whenLoaded('profile', function () {
                return [
                    'avatar' => $this->profile->avatar,
                    'bio' => $this->profile->bio,
                    'birth_date' => $this->profile->birth_date?->toDateString(),
                    'address' => $this->profile->address,
                    'city' => $this->profile->city,
                    'postal_code' => $this->profile->postal_code,
                    'country' => $this->profile->country,
                ];
            }),
            'consents' => $this->when($request->route()->getName() === 'user.consents', function () {
                return $this->consents->map(fn($consent) => [
                    'type' => $consent->consent_type,
                    'given' => $consent->consent_given,
                    'version' => $consent->consent_version,
                    'created_at' => $consent->created_at->toIso8601String(),
                ]);
            }),
            $this->mergeWhen($request->user()?->role === 'admin' || $request->user()?->role === 'super_admin', [
                'deleted_at' => $this->deleted_at?->toIso8601String(),
            ]),
        ];
    }
}
