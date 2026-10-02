<?php

namespace App\Support;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

class Discogs
{
    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function get(string $path, array $query = []): array
    {
        $url = "https://api.discogs.com/{$path}";

        $response = $this->request()->get($url, $query);

        if ($response->status() === 429) {
            Sleep::for(60)->seconds();

            $response = $this->request()->get($url, $query);
        }

        $response->throw();

        Sleep::for(3)->seconds();

        return $response->json();
    }

    protected function request(): PendingRequest
    {
        $request = Http::withUserAgent(config('services.discogs.user_agent'))
            ->connectTimeout(5)
            ->timeout(25);

        $token = config('services.discogs.token');

        if (! $token) {
            return $request;
        }

        return $request->withHeaders(['Authorization' => "Discogs token={$token}"]);
    }
}
