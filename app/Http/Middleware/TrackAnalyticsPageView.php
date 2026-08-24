<?php

namespace App\Http\Middleware;

use App\Services\Analytics\AnalyticsTracker;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackAnalyticsPageView
{
    public function __construct(private readonly AnalyticsTracker $tracker) {}

    public function handle(Request $request, Closure $next, string $organization, string $page): Response
    {
        $response = $next($request);

        if ($request->isMethod('GET') && $response->isSuccessful()) {
            $this->tracker->recordPageView($request, $organization, $page);
        }

        return $response;
    }
}
