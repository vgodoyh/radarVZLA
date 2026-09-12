<?php

namespace App\Http\Controllers;

use App\Services\Analytics\AnalyticsTracker;
use App\Services\Analytics\NavigationDestinationService;
use Illuminate\Http\Request;

class AnalyticsNavigationStoreController extends Controller
{
    public function __invoke(
        Request $request,
        AnalyticsTracker $tracker,
        NavigationDestinationService $destinations,
    )
    {
        $validated = $request->validate([
            'organization' => ['required', 'string', 'max:40'],
            'source' => ['required', 'string', 'in:home,header'],
            'target' => ['required', 'string', 'max:80'],
        ]);
        $destination = $destinations->resolve($validated['organization']);

        abort_unless($destination && hash_equals($destination['target'], $validated['target']), 422);

        $tracker->recordNavigationClick(
            $request,
            $destination['organization'],
            $destination['target'],
            $validated['source'],
        );

        if ($validated['source'] === 'home') {
            $request->session()->put('analytics_page_view_source', 'home');
            $request->session()->put('analytics_page_view_source_expires_at', now()->addSeconds(30)->timestamp);
        }

        return response()->noContent();
    }
}
