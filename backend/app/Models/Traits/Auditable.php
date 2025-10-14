<?php

namespace App\Models\Traits;

use Illuminate\Support\Arr;

trait Auditable
{
    /**
     * Attributes to explicitly include in audit snapshots. If empty, include all except $auditExclude.
     * @var array
     */
    protected array $auditInclude = [];

    /**
     * Attributes to exclude from audit snapshots (sensitive data)
     * @var array
     */
    protected array $auditExclude = [
        'password',
        'remember_token',
        'two_factor_secret',
        'api_token',
    ];

    /**
     * Attributes to redact (replace with '[REDACTED]') if present
     * @var array
     */
    protected array $auditRedact = [
        'password',
        'two_factor_secret',
        'api_token',
    ];

    /**
     * Return a filtered + redacted snapshot of the model suitable for audit logs.
     * @param  array|null  $only
     * @return array
     */
    public function toAudit(?array $only = null): array
    {
        $attributes = $this->getAttributes();

        if (is_array($only)) {
            $attributes = Arr::only($attributes, $only);
        } elseif (!empty($this->auditInclude)) {
            $attributes = Arr::only($attributes, $this->auditInclude);
        } else {
            $attributes = Arr::except($attributes, $this->auditExclude);
        }

        // Redact sensitive attributes
        foreach ($this->auditRedact as $key) {
            if (array_key_exists($key, $attributes)) {
                $attributes[$key] = '[REDACTED]';
            }
        }

        // Normalize DateTime/casts via attribute casting if available
        foreach ($attributes as $k => $v) {
            if ($v instanceof \DateTimeInterface) {
                $attributes[$k] = $v->format(DATE_ATOM);
            }
        }

        return $attributes;
    }

    /**
     * Scrub an arbitrary attribute array using this model's audit rules.
     * This preserves the values passed in (useful for old-values snapshots).
     */
    public function scrubAuditAttributes(array $attributes): array
    {
        if (!empty($this->auditInclude)) {
            $attributes = \Illuminate\Support\Arr::only($attributes, $this->auditInclude);
        } else {
            $attributes = \Illuminate\Support\Arr::except($attributes, $this->auditExclude);
        }

        foreach ($this->auditRedact as $key) {
            if (array_key_exists($key, $attributes)) {
                $attributes[$key] = '[REDACTED]';
            }
        }

        foreach ($attributes as $k => $v) {
            if ($v instanceof \DateTimeInterface) {
                $attributes[$k] = $v->format(DATE_ATOM);
            }
        }

        return $attributes;
    }

    /**
     * Whether this model opts into auditing. Default true; models can override.
     */
    public static function shouldAudit(): bool
    {
        return true;
    }
}
