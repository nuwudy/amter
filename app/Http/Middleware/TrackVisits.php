<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Visit;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TrackVisits
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            $path = $request->path();

            // Only track standard GET page requests.
            // Skip AJAX, Livewire, Filament admin pages, and other background assets.
            if ($request->isMethod('GET')
                && !$request->ajax()
                && !str_starts_with($path, 'admin')
                && !str_starts_with($path, 'livewire')
                && !str_starts_with($path, 'filament')
                && !str_contains($path, '.') // Skip static file hits
            ) {
                $ip = $request->header('cf-connecting-ip') ?? $request->ip();
                
                // Exclude local IP addresses
                if ($ip === '127.0.0.1' || $ip === '::1' || str_starts_with($ip, '192.168.')) {
                    $country = 'Local';
                    $countryCode = 'LCL';
                    $region = 'Local Network';
                } else {
                    // Cache IP GeoLookup for 7 days to prevent hitting rate-limits and slowing down requests
                    $geo = Cache::remember("ip-geo-{$ip}", 86400 * 7, function () use ($ip, $request) {
                        // Check if Cloudflare is providing the country code first
                        $cfCountry = $request->header('cf-ipcountry');

                        try {
                            // Query ip-api.com (Free, extremely fast, no auth key needed)
                            $apiResponse = Http::timeout(2.5)->get("http://ip-api.com/json/{$ip}");
                            if ($apiResponse->successful()) {
                                $data = $apiResponse->json();
                                if (($data['status'] ?? '') === 'success') {
                                    return [
                                        'country' => $data['country'] ?? 'Unknown',
                                        'country_code' => $data['countryCode'] ?? $cfCountry ?? 'UN',
                                        'region' => $data['regionName'] ?? 'Unknown',
                                    ];
                                }
                            }
                        } catch (\Exception $e) {
                            Log::warning("IP GeoLookup timeout/failed for IP {$ip}: " . $e->getMessage());
                        }

                        // Fallback if API fails but Cloudflare has the country code
                        if ($cfCountry) {
                            return [
                                'country' => $cfCountry,
                                'country_code' => $cfCountry,
                                'region' => 'Unknown',
                            ];
                        }

                        return [
                            'country' => 'Unknown',
                            'country_code' => 'UN',
                            'region' => 'Unknown',
                        ];
                    });

                    $country = $geo['country'];
                    $countryCode = $geo['country_code'];
                    $region = $geo['region'];
                }

                // Save to database
                Visit::create([
                    'ip_address' => $ip,
                    'user_agent' => substr($request->userAgent(), 0, 500),
                    'url' => $request->fullUrl(),
                    'referer' => $request->header('referer') ? substr($request->header('referer'), 0, 255) : null,
                    'country_code' => $countryCode,
                    'country' => $country,
                    'region' => $region,
                ]);
            }
        } catch (\Exception $e) {
            // Never break the page load if logging fails
            Log::error('Visit tracking failed: ' . $e->getMessage());
        }

        return $response;
    }
}
