<?php

namespace App\Http\Controllers;

use App\Services\Analytics\AnalyticsTracker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AnalyticsOvfnContentRedirectController extends Controller
{
    public function __invoke(Request $request, string $contentType, int $contentId, AnalyticsTracker $tracker): RedirectResponse
    {
        abort_unless(in_array($contentType, ['analysis', 'noti_fake'], true), 404);
        abort_unless(in_array($request->query('source'), ['home', 'organization'], true), 404);
        $url = (string) $request->query('url');
        abort_unless(Str::startsWith($url, ['https://', 'http://']), 404);

        $tracker->recordContentClick($request, 'ovfn', $contentType, $contentId, (string) $request->query('source'));

        return redirect()->away($url);
    }
}
