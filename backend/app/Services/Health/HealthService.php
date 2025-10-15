<?php

namespace App\Services\Health;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HealthService
{
    /**
     * Run health checks and return structured result.
     *
     * @param array $options ['detailed' => bool, 'timeout_ms' => int]
     * @return array
     */
    public function check(array $options = []): array
    {
        $detailed = $options['detailed'] ?? false;
        $timeoutMs = $options['timeout_ms'] ?? 500;

        $result = [
            'ok' => true,
            'checks' => [],
            'timestamp' => now()->toIso8601String(),
        ];

        // DB check
        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            $time = (int) ((microtime(true) - $start) * 1000);
            $result['checks']['db'] = ['ok' => true, 'time_ms' => $time, 'info' => 'ok'];
        } catch (\Throwable $e) {
            $result['checks']['db'] = ['ok' => false, 'time_ms' => null, 'info' => $e->getMessage()];
            $result['ok'] = false;
            if (! $detailed) {
                // Keep minimal info by default
                $result['checks']['db']['info'] = 'failed';
            }
        }

        // Cache / Redis check (only a light put)
        try {
            $start = microtime(true);
            Cache::put('health_check', 'ok', 3);
            $time = (int) ((microtime(true) - $start) * 1000);
            $result['checks']['cache'] = ['ok' => true, 'time_ms' => $time, 'info' => 'ok'];
        } catch (\Throwable $e) {
            $result['checks']['cache'] = ['ok' => false, 'time_ms' => null, 'info' => $detailed ? $e->getMessage() : 'failed'];
            $result['ok'] = false;
        }

        // Optionally check storage (only in detailed mode)
        if ($detailed) {
            try {
                $start = microtime(true);
                $disk = Storage::disk(config('filesystems.default'));
                $ok = $disk->put('health_check.txt', 'ok');
                $time = (int) ((microtime(true) - $start) * 1000);
                $result['checks']['storage'] = ['ok' => (bool) $ok, 'time_ms' => $time, 'info' => $ok ? 'ok' : 'write_failed'];
                if (! $ok) {
                    $result['ok'] = false;
                }
                // cleanup
                if ($ok) {
                    $disk->delete('health_check.txt');
                }
            } catch (\Throwable $e) {
                $result['checks']['storage'] = ['ok' => false, 'time_ms' => null, 'info' => $e->getMessage()];
                $result['ok'] = false;
            }
        }

        return $result;
    }
}
