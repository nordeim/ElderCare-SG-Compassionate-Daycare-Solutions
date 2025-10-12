<?php

namespace App\Services\Consent;

use App\Models\User;
use App\Models\Consent;
use Illuminate\Support\Facades\Request;

class ConsentService
{
    /**
     * Capture a user's consent.
     *
     * @param int $userId
     * @param string $type
     * @param string $consentText
     * @param string $version
     * @return Consent
     */
    public function captureConsent(int $userId, string $type, string $consentText, string $version): Consent
    {
        return Consent::create([
            'user_id' => $userId,
            'consent_type' => $type,
            'consent_given' => true,
            'consent_text' => $consentText,
            'consent_version' => $version,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Withdraw a user's consent.
     *
     * @param int $userId
     * @param string $type
     * @return bool
     */
    public function withdrawConsent(int $userId, string $type): bool
    {
        $consent = Consent::where('user_id', $userId)->where('consent_type', $type)->latest()->first();

        if ($consent && $consent->isActive()) {
            $consent->update([
                'consent_given' => false,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
            return true;
        }

        return false;
    }

    /**
     * Check if a user has given active consent for a specific type.
     *
     * @param int $userId
     * @param string $type
     * @return bool
     */
    public function checkConsent(int $userId, string $type): bool
    {
        return Consent::where('user_id', $userId)
            ->where('consent_type', $type)
            ->active()
            ->exists();
    }

    /**
     * Get the consent history for a user.
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getConsentHistory(int $userId)
    {
        return Consent::where('user_id', $userId)->orderBy('created_at', 'desc')->get();
    }

    /**
     * Export consent data for a user.
     *
     * @param int $userId
     * @return array
     */
    public function exportConsentData(int $userId): array
    {
        return $this->getConsentHistory($userId)->toArray();
    }
}
