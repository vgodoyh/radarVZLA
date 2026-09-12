<?php

namespace App\Http\Controllers;

use App\Services\Analytics\AnalyticsTracker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AnalyticsNavigationRedirectController extends Controller
{
    /** @var array<string, array{organization: string, target: string, route: string}> */
    private const DESTINATIONS = [
        'jep' => [
            'organization' => 'jep',
            'target' => 'justicia-encuentro-perdon',
            'route' => 'organizations.jep',
        ],
        'acceso-justicia' => [
            'organization' => 'acceso_justicia',
            'target' => 'acceso-justicia',
            'route' => 'organizations.acceso-justicia',
        ],
        'ovfn' => [
            'organization' => 'ovfn',
            'target' => 'fake-news',
            'route' => 'organizations.fake-news',
        ],
        'universidades' => [
            'organization' => 'universidades',
            'target' => 'observatorio-universidades',
            'route' => 'organizations.universidades',
        ],
    ];

    public function __invoke(
        Request $request,
        string $organization,
        string $source,
        AnalyticsTracker $tracker,
    ): RedirectResponse {
        $destination = self::DESTINATIONS[$organization] ?? null;

        abort_unless($destination && $source === 'home', 404);

        $tracker->recordNavigationClick(
            $request,
            $destination['organization'],
            $destination['target'],
            $source,
        );

        $request->session()->put('analytics_page_view_source', 'home');
        $request->session()->put('analytics_page_view_source_expires_at', now()->addSeconds(30)->timestamp);

        return redirect()->route($destination['route']);
    }
}
