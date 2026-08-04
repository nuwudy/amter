<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BunnyService
{
    /**
     * Fetch video statistics from Bunny.net API.
     * 
     * @param string $videoId
     * @return array|null
     */
    public function getVideoStats(string $videoId)
    {
        $libraryId = config('services.bunny.library_id');
        $apiKey = config('services.bunny.api_key');

        $response = Http::withHeaders(['AccessKey' => $apiKey])
            ->get("https://video.bunnycdn.com/library/{$libraryId}/videos/{$videoId}");

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }
}
