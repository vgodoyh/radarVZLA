<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateUserLastActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && (! $user->last_activity_at || $user->last_activity_at->lt(now()->subMinutes(2)))) {
            $userAgent = $request->userAgent();

            $user->forceFill([
                'last_activity_at' => now(),
                'last_device_type' => $this->deviceType($userAgent),
                'last_platform' => $this->platform($userAgent),
                'last_browser' => $this->browser($userAgent),
            ])->saveQuietly();
        }

        return $next($request);
    }

    private function deviceType(?string $userAgent): string
    {
        $userAgent = strtolower($userAgent ?? '');

        if (preg_match('/ipad|tablet|android(?!.*mobile)/i', $userAgent)) {
            return 'tablet';
        }

        if (preg_match('/mobile|iphone|ipod|android/i', $userAgent)) {
            return 'mobile';
        }

        return 'desktop';
    }

    private function platform(?string $userAgent): string
    {
        $userAgent = strtolower($userAgent ?? '');

        return match (true) {
            str_contains($userAgent, 'android') => 'Android',
            str_contains($userAgent, 'iphone') || str_contains($userAgent, 'ipad') || str_contains($userAgent, 'ipod') => 'iOS',
            str_contains($userAgent, 'windows') => 'Windows',
            str_contains($userAgent, 'mac os') || str_contains($userAgent, 'macintosh') => 'macOS',
            str_contains($userAgent, 'linux') => 'Linux',
            default => 'Otro',
        };
    }

    private function browser(?string $userAgent): string
    {
        $userAgent = strtolower($userAgent ?? '');

        return match (true) {
            str_contains($userAgent, 'edg/') || str_contains($userAgent, 'edge/') => 'Edge',
            str_contains($userAgent, 'opr/') || str_contains($userAgent, 'opera') => 'Opera',
            str_contains($userAgent, 'firefox/') => 'Firefox',
            str_contains($userAgent, 'chrome/') || str_contains($userAgent, 'crios/') => 'Chrome',
            str_contains($userAgent, 'safari/') => 'Safari',
            default => 'Otro',
        };
    }
}
