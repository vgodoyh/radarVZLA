<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use App\Models\ObuMonthlyNote;
use App\Models\JepMetricSnapshot;
use App\Services\Analytics\AnalyticsTracker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AnalyticsContentRedirectController extends Controller
{
    private const OBU_MONTHLY_NOTE_URL = 'https://observatoriodeuniversidades.com/noticias-obu-las-continuas-violaciones-a-los-derechos-laborales-movilizan-a-los-universitarios-en-2026/';

    public function __invoke(
        Request $request,
        string $publication,
        string $source,
        AnalyticsTracker $tracker,
    ): RedirectResponse {
        if ($request->routeIs('analytics.jep.featured.redirect')) {
            $snapshot = JepMetricSnapshot::query()
                ->whereHas('organization', fn ($query) => $query->where('slug', 'jep'))
                ->findOrFail((int) $publication);
            $fieldByType = [
                'featured_instagram' => 'featured_indicator_instagram_url',
                'featured_x' => 'featured_indicator_x_url',
                'featured_read_more' => 'featured_indicator_read_more_url',
            ];
            $field = $fieldByType[$request->route('type')] ?? null;
            $url = $field ? $snapshot->{$field} : null;

            abort_unless(filled($url) && Str::startsWith($url, ['https://', 'http://']), 404);
            $tracker->recordContentClick($request, 'jep', $request->route('type'), $snapshot->id, $source);

            return redirect()->away($url);
        }

        if ($request->routeIs('analytics.jep.alert.redirect')) {
            $snapshot = JepMetricSnapshot::query()
                ->whereHas('organization', fn ($query) => $query->where('slug', 'jep'))
                ->findOrFail((int) $publication);
            $url = $snapshot->monthly_alert_x_url;

            abort_unless(filled($url) && Str::startsWith($url, ['https://', 'http://']), 404);
            $tracker->recordContentClick($request, 'jep', 'monthly_alert_x', $snapshot->id, $source);

            return redirect()->away($url);
        }

        if ($request->routeIs('analytics.jep.content.redirect')) {
            $contentId = (int) $publication;

            $tracker->recordContentClick($request, 'jep', 'monthly_alert', $contentId, $source);

            return redirect()->to(route('organizations.jep').'#alerta-del-mes');
        }

        if ($request->routeIs('analytics.obu.content.redirect')) {
            $note = ObuMonthlyNote::query()
                ->whereHas('organization', fn ($query) => $query->where('slug', 'universidades'))
                ->find((int) $publication);
            $contentId = $note?->id ?? (int) $publication;
            $url = $note?->url ?: self::OBU_MONTHLY_NOTE_URL;

            abort_unless($note || $contentId === abs(crc32(self::OBU_MONTHLY_NOTE_URL)), 404);
            $tracker->recordContentClick($request, 'universidades', 'monthly_note', $contentId, $source);

            return redirect()->away($url);
        }

        $publication = Publication::query()->findOrFail((int) $publication);
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
