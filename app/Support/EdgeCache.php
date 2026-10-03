<?php

namespace App\Support;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Throwable;

class EdgeCache
{
    public function isConfigured(): bool
    {
        if (blank(config('services.laravel_cloud.purge_token'))) {
            return false;
        }

        return filled(config('services.laravel_cloud.environment_id'));
    }

    /**
     * Purges every page cached at Laravel Cloud's edge. Failures are reported
     * instead of thrown, so a failing purge never breaks a sync.
     */
    public function purge(): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        $environmentId = config('services.laravel_cloud.environment_id');

        return rescue(function () use ($environmentId): bool {
            Http::withToken(config('services.laravel_cloud.purge_token'))
                ->acceptJson()
                ->timeout(10)
                ->retry(2, 1000, fn (Throwable $exception): bool => $this->isTransient($exception), throw: false)
                ->post("https://cloud.laravel.com/api/environments/{$environmentId}/purge-edge-cache", (object) [])
                ->throw();

            return true;
        }, false);
    }

    protected function isTransient(Throwable $exception): bool
    {
        if ($exception instanceof ConnectionException) {
            return true;
        }

        if (! $exception instanceof RequestException) {
            return false;
        }

        return $exception->response->serverError() || $exception->response->tooManyRequests();
    }
}
