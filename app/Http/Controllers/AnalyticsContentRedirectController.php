<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use App\Services\Analytics\AnalyticsTracker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AnalyticsContentRedirectController extends Controller
{
    public function __invoke(
        Request $request,
        Publication $publication,
        string $source,
        AnalyticsTracker $tracker,
    ): RedirectResponse {
        $publication->loadMissing('organization');

        abort_unless(in_array($source, ['home', 'organization'], true), 404);
        $organization = $publication->organization?->slug;
        abort_unless(in_array($organization, ['acceso-justicia', 'fake-news'], true), 404);
        abort_unless($publication->source === 'x', 404);
        if ($organization === 'acceso-justicia') {
            abort_unless(Str::contains(Str::lower($publication->excerpt ?? ''), '#alertalegal'), 404);
        }
        abort_unless(Str::startsWith($publication->url, ['https://', 'http://']), 404);

        $tracker->recordContentClick(
            $request,
            $organization === 'fake-news' ? 'ovfn' : 'acceso_justicia',
            $organization === 'fake-news' ? 'x_post' : 'alert',
            $publication->id,
            $source,
        );

        return redirect()->away($publication->url);
    }
}
