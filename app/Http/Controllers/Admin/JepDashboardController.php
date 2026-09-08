<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JepDetentionCenter;
use App\Models\JepVulnerableGroup;
use App\Models\Organization;
use App\Services\Analytics\OrganizationAnalyticsService;
use App\Services\JepEditorialMetricsService;
use App\Services\TwitterService;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class JepDashboardController extends Controller
{
    public function __construct(private readonly JepEditorialMetricsService $metrics) {}

    public function __invoke(OrganizationAnalyticsService $analytics): View
    {
        $organization = $this->organization();
        return view('admin.organizations.jep.index', [
            ...$analytics->dashboard('jep'),
            'organization' => $organization,
            'currentMetrics' => $this->metrics->current($organization),
            'metricsHistory' => $this->metrics->history($organization),
        ]);
    }

    public function updateMetrics(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'total_political_prisoners' => ['required', 'integer', 'min:0'],
            'total_political_prisoners_trend' => ['nullable', 'numeric', 'between:-9999.99,9999.99'],
            'women' => ['required', 'integer', 'min:0'],
            'women_trend' => ['nullable', 'numeric', 'between:-9999.99,9999.99'],
            'seriously_ill' => ['required', 'integer', 'min:0'],
            'seriously_ill_trend' => ['nullable', 'numeric', 'between:-9999.99,9999.99'],
            'foreign_or_dual_nationality' => ['required', 'integer', 'min:0'],
            'foreign_or_dual_nationality_trend' => ['nullable', 'numeric', 'between:-9999.99,9999.99'],
            'releases' => ['required', 'integer', 'min:0'],
            'releases_trend' => ['nullable', 'numeric', 'between:-9999.99,9999.99'],
            'releases_period_start_month' => ['nullable', 'integer', 'between:1,12'],
            'releases_period_start_day' => ['nullable', 'integer', 'between:1,31'],
            'releases_period_start_year' => ['nullable', 'integer', 'min:1900', 'max:2200'],
            'releases_period_end_month' => ['nullable', 'integer', 'between:1,12'],
            'releases_period_end_day' => ['nullable', 'integer', 'between:1,31'],
            'releases_period_end_year' => ['nullable', 'integer', 'min:1900', 'max:2200'],
            'active_retired_officials' => ['required', 'integer', 'min:0'],
            'new_detentions' => ['required', 'integer', 'min:0'],
            'missing_location' => ['required', 'integer', 'min:0'],
            'deaths_in_custody' => ['required', 'integer', 'min:0'],
            'deaths_period_start_month' => ['nullable', 'integer', 'between:1,12'],
            'deaths_period_start_year' => ['nullable', 'integer', 'min:1900', 'max:2200'],
            'deaths_period_end_month' => ['nullable', 'integer', 'between:1,12'],
            'deaths_period_end_year' => ['nullable', 'integer', 'min:1900', 'max:2200'],
            'monthly_alert_title' => ['nullable', 'string', 'max:255'],
            'monthly_alert_excerpt' => ['nullable', 'string', 'max:10000'],
            'monthly_alert_x_url' => ['nullable', 'url', 'regex:/^https?:\/\/(?:www\.)?(?:x\.com|twitter\.com)\/[^\/?#]+\/status\/\d+(?:[?#].*)?$/i'],
            'featured_indicator_title' => ['nullable', 'string', 'max:255'],
            'featured_indicator_text' => ['nullable', 'string', 'max:10000'],
            'featured_indicator_instagram_url' => ['nullable', 'url', 'max:2048'],
            'featured_indicator_x_url' => ['nullable', 'url', 'max:2048'],
            'featured_indicator_read_more_url' => ['nullable', 'url', 'max:2048'],
            'featured_indicator_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'data_date' => ['nullable', 'date'],
            'groups' => ['required', 'array', 'min:1'],
            'groups.*.group_key' => ['required', 'string', 'max:80'],
            'groups.*.label' => ['required', 'string', 'max:255'],
            'groups.*.value' => ['required', 'integer', 'min:0'],
            'groups.*.sort_order' => ['required', 'integer', 'min:0'],
            'centers' => ['required', 'array', 'min:1'],
            'centers.*.name' => ['required', 'string', 'max:255'],
            'centers.*.value' => ['required', 'integer', 'min:0'],
            'centers.*.sort_order' => ['required', 'integer', 'min:0'],
            'death_custody_distribution' => ['required', 'array', 'min:1'],
            'death_custody_distribution.*.category_key' => ['required', 'string', 'max:80'],
            'death_custody_distribution.*.label' => ['required', 'string', 'max:255'],
            'death_custody_distribution.*.value' => ['required', 'integer', 'min:0'],
            'death_custody_distribution.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);
        $changes = collect($validated)->except(['groups', 'centers', 'death_custody_distribution', 'featured_indicator_image'])->map(fn ($value) => $value === '' ? null : $value)->all();
        $image = $request->file('featured_indicator_image');
        if ($image instanceof UploadedFile && $image->isValid()) {
            $changes['featured_indicator_image_path'] = $this->storeFeaturedIndicatorImage($image);
        }
        return $this->saveChanges($changes, [
            'groups' => $validated['groups'],
            'centers' => $validated['centers'],
            'death_custody_distribution' => $validated['death_custody_distribution'],
        ], 'Cifras de JEP actualizadas correctamente.');
    }

    public function fetchMonthlyAlertPost(Request $request, TwitterService $twitter)
    {
        $validated = $request->validate([
            'url' => ['required', 'url', 'regex:/^https?:\/\/(?:www\.)?(?:x\.com|twitter\.com)\/[^\/?#]+\/status\/\d+(?:[?#].*)?$/i'],
        ]);
        $result = $twitter->fetchPostTextFromUrl($validated['url']);

        if ($result['status'] === 200 && filled($result['text'])) {
            return response()->json(['success' => true, 'text' => $result['text']]);
        }

        $message = match ($result['status']) {
            404 => 'La publicación no fue encontrada.',
            403 => 'No fue posible acceder a esta publicación.',
            429 => 'X limitó temporalmente las consultas. Inténtalo nuevamente más tarde.',
            default => 'No fue posible obtener el contenido de la publicación.',
        };

        return response()->json(['success' => false, 'message' => $message], $result['status'] >= 400 && $result['status'] < 500 ? $result['status'] : 502);
    }

    public function updateMainMetrics(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'total_political_prisoners' => ['required', 'integer', 'min:0'],
            'total_political_prisoners_trend' => ['nullable', 'numeric', 'between:-9999.99,9999.99'],
            'women' => ['required', 'integer', 'min:0'], 'women_trend' => ['nullable', 'numeric', 'between:-9999.99,9999.99'],
            'seriously_ill' => ['required', 'integer', 'min:0'], 'seriously_ill_trend' => ['nullable', 'numeric', 'between:-9999.99,9999.99'],
            'foreign_or_dual_nationality' => ['required', 'integer', 'min:0'], 'foreign_or_dual_nationality_trend' => ['nullable', 'numeric', 'between:-9999.99,9999.99'],
            'releases' => ['required', 'integer', 'min:0'], 'releases_trend' => ['nullable', 'numeric', 'between:-9999.99,9999.99'],
            'releases_period_start_date' => ['nullable', 'date'],
            'releases_period_end_date' => ['nullable', 'date', 'after_or_equal:releases_period_start_date'],
        ]);

        foreach (['start' => 'releases_period_start_date', 'end' => 'releases_period_end_date'] as $part => $field) {
            $date = filled($validated[$field] ?? null) ? \Illuminate\Support\Carbon::parse($validated[$field]) : null;
            $validated["releases_period_{$part}_month"] = $date?->month;
            $validated["releases_period_{$part}_day"] = $date?->day;
            $validated["releases_period_{$part}_year"] = $date?->year;
            unset($validated[$field]);
        }

        return $this->saveChanges(
            collect($validated)->map(fn ($value) => $value === '' ? null : $value)->all(),
            [],
            'Cifras principales actualizadas correctamente.'
        );
    }

    public function updateFeaturedIndicator(Request $request): RedirectResponse
    {
        Log::debug('JEP featured indicator: request received', [
            'user_id' => auth()->id(),
            'has_image' => $request->hasFile('featured_indicator_image'),
            'title' => $request->input('featured_indicator_title'),
            'text_length' => strlen((string) $request->input('featured_indicator_text')),
            'instagram' => $request->input('featured_indicator_instagram_url'),
            'x' => $request->input('featured_indicator_x_url'),
            'read_more' => $request->input('featured_indicator_read_more_url'),
        ]);

        $validated = $request->validate([
            'featured_indicator_title' => ['nullable', 'string', 'max:255'],
            'featured_indicator_text' => ['nullable', 'string', 'max:10000'],
            'featured_indicator_instagram_url' => ['nullable', 'url', 'max:2048'],
            'featured_indicator_x_url' => ['nullable', 'url', 'max:2048'],
            'featured_indicator_read_more_url' => ['nullable', 'url', 'max:2048'],
            'featured_indicator_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
        Log::debug('JEP featured indicator: validated', [
            'keys' => array_keys($validated),
            'has_image_key' => array_key_exists('featured_indicator_image', $validated),
        ]);
        $changes = collect($validated)->except('featured_indicator_image')->map(fn ($value) => $value === '' ? null : $value)->all();
        Log::debug('JEP featured indicator: changes before image', ['changes' => $changes]);
        $image = $request->file('featured_indicator_image');
        if ($image instanceof UploadedFile && $image->isValid()) {
            $changes['featured_indicator_image_path'] = $this->storeFeaturedIndicatorImage($image);
        }
        Log::debug('JEP featured indicator: changes after image', ['changes' => $changes]);
        Log::debug('JEP featured indicator: calling saveChanges', ['changes' => $changes]);
        return $this->saveChanges($changes, [], 'Indicador destacado actualizado correctamente.');
    }

    private function storeFeaturedIndicatorImage(UploadedFile $image): string
    {
        $realPath = $image->getRealPath();
        $pathname = $image->getPathname();

        Log::debug('JEP featured indicator image upload diagnostics', [
            'class' => get_class($image),
            'is_uploaded_file' => $image instanceof UploadedFile,
            'is_valid' => $image->isValid(),
            'error' => $image->getError(),
            'error_message' => $image->getErrorMessage(),
            'client_name' => $image->getClientOriginalName(),
            'client_extension' => $image->getClientOriginalExtension(),
            'mime' => $image->getMimeType(),
            'size' => $image->getSize(),
            'pathname' => $pathname,
            'real_path' => $realPath,
            'file_exists' => is_string($pathname) && file_exists($pathname),
            'is_readable' => is_string($pathname) && is_readable($pathname),
            'public_disk_root' => config('filesystems.disks.public.root'),
        ]);

        $sourcePath = $image->getRealPath();
        if (! $sourcePath) {
            $sourcePath = $image->getPathname();
        }

        Log::debug('JEP featured indicator: selected source path', [
            'source_path' => $sourcePath,
            'exists' => $sourcePath ? file_exists($sourcePath) : false,
            'readable' => $sourcePath ? is_readable($sourcePath) : false,
        ]);

        if (! is_string($sourcePath) || $sourcePath === '' || ! is_file($sourcePath) || ! is_readable($sourcePath)) {
            throw new \RuntimeException('No se pudo acceder al archivo temporal de la imagen.');
        }

        $extension = strtolower($image->getClientOriginalExtension() ?: 'bin');
        $filename = Str::uuid().'.'.$extension;
        $relativePath = 'jep/featured-indicator/'.$filename;
        $stream = fopen($sourcePath, 'rb');
        if ($stream === false) {
            throw new \RuntimeException('No se pudo abrir el archivo temporal de la imagen.');
        }

        try {
            $stored = Storage::disk('public')->writeStream($relativePath, $stream);
            Log::debug('JEP featured indicator: image write result', [
                'result' => $stored,
                'relative_path' => $relativePath,
                'exists_after_write' => Storage::disk('public')->exists($relativePath),
            ]);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        if ($stored === false) {
            throw new \RuntimeException('No se pudo almacenar la imagen del indicador destacado.');
        }

        return $relativePath;
    }

    public function updateDeathCustodyDistribution(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'death_custody_distribution' => ['required', 'array', 'size:3'],
            'death_custody_distribution.*.category_key' => ['required', 'string', 'max:80'],
            'death_custody_distribution.*.label' => ['required', 'string', 'max:255'],
            'death_custody_distribution.*.value' => ['required', 'integer', 'min:0'],
            'death_custody_distribution.*.sort_order' => ['required', 'integer', 'min:0'],
            'deaths_period_start' => ['nullable', 'date'],
            'deaths_period_end' => ['nullable', 'date', 'after_or_equal:deaths_period_start'],
        ]);

        foreach (['start' => 'deaths_period_start', 'end' => 'deaths_period_end'] as $part => $field) {
            $date = filled($validated[$field] ?? null) ? \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $validated[$field]) : null;
            $validated["deaths_period_{$part}_month"] = $date?->month;
            $validated["deaths_period_{$part}_day"] = $date?->day;
            $validated["deaths_period_{$part}_year"] = $date?->year;
            unset($validated[$field]);
        }

        $changes = collect($validated)->except('death_custody_distribution')->all();
        return $this->saveChanges($changes, ['death_custody_distribution' => $validated['death_custody_distribution']], 'Fallecidos en custodia actualizados correctamente.');
    }

    public function updateMonthlyAlert(Request $request): RedirectResponse
    {
        return $this->saveModule($request, [
            'monthly_alert_title' => ['nullable', 'string', 'max:255'],
            'monthly_alert_excerpt' => ['nullable', 'string', 'max:10000'],
            'monthly_alert_x_url' => ['nullable', 'url', 'regex:/^https?:\/\/(?:www\.)?(?:x\.com|twitter\.com)\/[^\/?#]+\/status\/\d+(?:[?#].*)?$/i'],
        ], 'Alerta del mes actualizada correctamente.');
    }

    public function updateIndicators(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'active_retired_officials' => ['required', 'integer', 'min:0'], 'new_detentions' => ['required', 'integer', 'min:0'],
            'missing_location' => ['required', 'integer', 'min:0'],
            'deaths_period_start' => ['nullable', 'date_format:Y-m'],
            'deaths_period_end' => ['nullable', 'date_format:Y-m', 'after_or_equal:deaths_period_start'],
        ]);

        foreach (['start' => 'deaths_period_start', 'end' => 'deaths_period_end'] as $part => $field) {
            $date = filled($validated[$field] ?? null) ? \Illuminate\Support\Carbon::createFromFormat('Y-m', $validated[$field]) : null;
            $validated["deaths_period_{$part}_month"] = $date?->month;
            $validated["deaths_period_{$part}_year"] = $date?->year;
            unset($validated[$field]);
        }

        return $this->saveChanges(
            collect($validated)->map(fn ($value) => $value === '' ? null : $value)->all(),
            [],
            'Indicadores actualizados correctamente.'
        );
    }

    public function updateVulnerableGroups(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'groups' => ['required', 'array', 'size:3'],
            'groups.sindicalistas' => ['required', 'integer', 'min:0'],
            'groups.organizaciones_politicas' => ['required', 'integer', 'min:0'],
            'groups.sociedad_civil' => ['required', 'integer', 'min:0'],
        ]);

        $labels = [
            'sindicalistas' => 'Sindicalistas',
            'organizaciones_politicas' => 'Organizaciones políticas',
            'sociedad_civil' => 'Sociedad civil',
        ];
        $groups = collect($labels)->map(function (string $label, string $groupKey) use ($validated) {
            return [
                'group_key' => $groupKey,
                'label' => $label,
                'value' => $validated['groups'][$groupKey],
                'sort_order' => match ($groupKey) {
                    'sindicalistas' => 1,
                    'organizaciones_politicas' => 2,
                    default => 3,
                },
            ];
        })->values()->all();

        return $this->saveChanges([], ['groups' => $groups], 'Grupos vulnerables actualizados correctamente.');
    }

    public function updateDetentionCenters(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'centers' => ['required', 'array', 'min:1'], 'centers.*.name' => ['required', 'string', 'max:255'],
            'centers.*.value' => ['required', 'integer', 'min:0'], 'centers.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);
        return $this->saveChanges([], ['centers' => $validated['centers']], 'Centros de detención actualizados correctamente.');
    }

    private function saveModule(Request $request, array $rules, string $message): RedirectResponse
    {
        $validated = $request->validate($rules);
        return $this->saveChanges(
            collect($validated)->map(fn ($value) => $value === '' ? null : $value)->all(),
            [],
            $message
        );
    }

    private function saveChanges(array $changes, array $relations, string $message): RedirectResponse
    {
        $changed = $this->metrics->updateSnapshot($this->organization(), $changes, $relations);
        return to_route('admin.jep.index')->with($changed ? 'jep_metrics_success' : 'jep_metrics_info', $changed ? $message : 'No se detectaron cambios para guardar.');
    }

    private function organization(): Organization { return Organization::query()->where('slug', 'jep')->firstOrFail(); }
}
