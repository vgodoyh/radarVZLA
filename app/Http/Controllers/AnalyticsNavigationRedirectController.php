<?php

namespace App\Http\Controllers;

use App\Services\Analytics\AnalyticsTracker;
use App\Services\Analytics\NavigationDestinationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AnalyticsNavigationRedirectController extends Controller
{
    private const ALLOWED_SOURCES = ['home', 'header'];

    public function __invoke(
        Request $request,
        string $organization,
        string $source,
        AnalyticsTracker $tracker,
        NavigationDestinationService $destinations,
    ): RedirectResponse {
        $destination = $destinations->resolve($organization);

        abort_unless($destination && in_array($source, self::ALLOWED_SOURCES, true), 404);

        $tracker->recordNavigationClick(
            $request,
            $destination['organization'],
            $destination['target'],
            $source,
        );

        if ($source === 'home') {
            $request->session()->put('analytics_page_view_source', 'home');
            $request->session()->put('analytics_page_view_source_expires_at', now()->addSeconds(30)->timestamp);
        }

        return redirect()->away('https://pulsovenezuela.org/'.$destination['target'], 301);
    }
}
