<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ObuBimonthlyAlert;
use App\Models\ObuDatasetValue;
use App\Models\ObuDatasetValueVersion;
use App\Models\ObuMetricSnapshot;
use App\Models\ObuMonitoringPeriod;
use App\Models\ObuMonthlyNote;
use App\Models\Organization;
use App\Services\Analytics\OrganizationAnalyticsService;
use App\Services\ObuDashboardDataService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ObuDashboardController extends Controller
{
    public function __invoke(OrganizationAnalyticsService $analytics, ObuDashboardDataService $obuData): View
    {
        $organization = $this->organization();
        $analyticsData = $analytics->dashboard('universidades');
        $rankingRows = collect($analyticsData['contentRanking'] ?? []);
        $analyticsData['contentRanking'] = new LengthAwarePaginator(
            $rankingRows->forPage(request()->integer('content_page', 1), 5)->values(),
            $rankingRows->count(),
            5,
            request()->integer('content_page', 1),
            ['path' => request()->url(), 'pageName' => 'content_page']
        );

        return view('admin.organizations.obu.index', [
            ...$analyticsData,
            'organization' => $organization,
            'currentMetrics' => $obuData->currentMonitoringPeriod(),
            'metricsHistory' => ObuMetricSnapshot::query()
                ->where('organization_id', $organization->id)
                ->with('user:id,name')
                ->orderByDesc('valid_from')
                ->get(),
            'monitoringPeriod' => $obuData->currentMonitoringPeriod(),
            'monitoringHistory' => ObuMonitoringPeriod::query()
                ->where('organization_id', $organization->id)
                ->with('user:id,name')
                ->orderByDesc('valid_from')
                ->get(),
            'latestMonthlyNote' => ObuMonthlyNote::query()->where('organization_id', $organization->id)->latest('publication_date')->first(),
            'monthlyNotesHistory' => ObuMonthlyNote::query()->where('organization_id', $organization->id)->with('user:id,name')->latest('publication_date')->get(),
            'latestBimonthlyAlert' => ObuBimonthlyAlert::query()->where('organization_id', $organization->id)->latest('period_end')->first(),
            'bimonthlyAlertsHistory' => ObuBimonthlyAlert::query()->where('organization_id', $organization->id)->with('user:id,name')->latest('period_end')->get(),
            'datasetYears' => $obuData->publicData()['obuDatasetYears'],
            'datasetCounts' => collect(ObuDashboardDataService::DATASETS)->mapWithKeys(fn (string $datasetKey) => [
                $datasetKey => ObuDatasetValue::query()
                    ->where('organization_id', $organization->id)
                    ->where('dataset_key', $datasetKey)
                    ->count(),
                ])->all(),
            'datasetsForEditing' => collect(ObuDashboardDataService::DATASETS)->mapWithKeys(fn (string $datasetKey) => [
                $datasetKey => ObuDatasetValue::query()
                    ->where('organization_id', $organization->id)
                    ->where('dataset_key', $datasetKey)
                    ->orderBy('period_year')
                    ->orderBy('sort_order')
                    ->get(),
            ])->all(),
            'datasetVersionHistory' => ObuDatasetValueVersion::query()
                ->where('organization_id', $organization->id)
                ->with('user:id,name')
                ->latest('valid_until')
                ->limit(5)
                ->get(),
        ]);
    }

    public function updateMetrics(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'analyzed_information' => ['required', 'integer', 'min:0'],
            'protests' => ['required', 'integer', 'min:0'],
            'complaints' => ['required', 'integer', 'min:0'],
            'period_start' => ['required', 'date_format:Y-m'],
            'period_end' => ['required', 'date_format:Y-m'],
        ]);
        $organization = $this->organization();

        $periodStart = Carbon::createFromFormat('Y-m-d', $validated['period_start'].'-01')->startOfMonth();
        $periodEnd = Carbon::createFromFormat('Y-m-d', $validated['period_end'].'-01')->endOfMonth();
        if ($periodEnd->lt($periodStart)) {
            throw ValidationException::withMessages([
                'period_end' => 'El período final no puede ser anterior al período inicial.',
            ]);
        }

        $changed = DB::transaction(function () use ($organization, $validated, $periodStart, $periodEnd): bool {
            $monitoringPeriod = ObuMonitoringPeriod::query()
                ->where('organization_id', $organization->id)
                ->current()
                ->lockForUpdate()
                ->first();
            $newStart = $periodStart->toDateString();
            $newEnd = $periodEnd->toDateString();
            $periodChanged = ! $monitoringPeriod
                || $monitoringPeriod->period_start?->toDateString() !== $newStart
                || $monitoringPeriod->period_end?->toDateString() !== $newEnd
                || (int) $monitoringPeriod->analyzed_information !== (int) $validated['analyzed_information']
                || (int) $monitoringPeriod->protests !== (int) $validated['protests']
                || (int) $monitoringPeriod->complaints !== (int) $validated['complaints'];

            if (! $periodChanged) {
                return false;
            }

            $changedAt = now();
            $monitoringPeriod?->update(['valid_until' => $changedAt]);
            ObuMonitoringPeriod::create([
                'organization_id' => $organization->id,
                'period_start' => $newStart,
                'period_end' => $newEnd,
                'analyzed_information' => $validated['analyzed_information'],
                'protests' => $validated['protests'],
                'complaints' => $validated['complaints'],
                'data_date' => $monitoringPeriod?->data_date,
                'valid_from' => $changedAt,
                'user_id' => auth()->id(),
            ]);

            return true;
        });

        return to_route('admin.obu.index')->with(
            $changed ? 'obu_metrics_success' : 'obu_metrics_info',
            $changed
                ? 'Cifras de OBU actualizadas correctamente.'
                : 'No se detectaron cambios para guardar.'
        );
    }

    public function storeMonthlyNote(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['required', 'string', 'max:5000'],
            'publication_date' => ['required', 'date'],
            'url' => ['nullable', 'url', 'max:2048'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_published' => ['nullable', 'boolean'],
        ]);
        $organization = $this->organization();
        $latest = ObuMonthlyNote::query()->where('organization_id', $organization->id)->latest('publication_date')->first();

        ObuMonthlyNote::create([
            'organization_id' => $organization->id,
            'title' => $validated['title'],
            'excerpt' => $validated['excerpt'],
            'publication_date' => $validated['publication_date'],
            'url' => $validated['url'] ?? null,
            'image_path' => $request->hasFile('image')
                ? $request->file('image')->store('obu/monthly-notes', 'public')
                : $latest?->image_path,
            'is_published' => $request->boolean('is_published'),
            'user_id' => auth()->id(),
        ]);

        return to_route('admin.obu.index')->with('obu_note_success', 'Nota mensual guardada correctamente.');
    }

    public function storeBimonthlyAlert(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['required', 'string', 'max:5000'],
            'url' => ['nullable', 'url', 'max:2048'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'file' => ['nullable', 'file', 'mimetypes:application/pdf', 'extensions:pdf', 'max:20480'],
            'is_published' => ['nullable', 'boolean'],
        ]);
        $organization = $this->organization();
        $latest = ObuBimonthlyAlert::query()->where('organization_id', $organization->id)->latest('period_end')->first();

        if (! $request->hasFile('file') && ! $latest) {
            return back()->withErrors(['file' => 'El archivo PDF es obligatorio para la primera alerta.'])->withInput();
        }

        $file = $request->file('file');
        ObuBimonthlyAlert::create([
            'organization_id' => $organization->id,
            'title' => $validated['title'],
            'excerpt' => $validated['excerpt'],
            'url' => $validated['url'] ?? null,
            'period_start' => $validated['period_start'],
            'period_end' => $validated['period_end'],
            'image_path' => $request->hasFile('image')
                ? $request->file('image')->store('obu/bimonthly-alerts', 'public')
                : $latest?->image_path,
            'file_path' => $file ? $file->store('obu/bimonthly-alerts', 'public') : $latest->file_path,
            'file_name' => $file?->getClientOriginalName() ?? $latest->file_name,
            'mime_type' => $file?->getMimeType() ?? $latest->mime_type,
            'file_size' => $file?->getSize() ?? $latest->file_size,
            'is_published' => $request->boolean('is_published'),
            'user_id' => auth()->id(),
        ]);

        return to_route('admin.obu.index')->with('obu_alert_success', 'Alerta bimensual guardada correctamente.');
    }

    public function updateMonitoringPeriod(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'analyzed_information' => ['required', 'integer', 'min:0'],
            'protests' => ['required', 'integer', 'min:0'],
            'complaints' => ['required', 'integer', 'min:0'],
            'data_date' => ['nullable', 'date'],
        ]);
        $organization = $this->organization();
        $changed = DB::transaction(function () use ($organization, $validated): bool {
            $current = ObuMonitoringPeriod::query()->where('organization_id', $organization->id)->current()->lockForUpdate()->first();
            $newStart = date('Y-m-d', strtotime($validated['period_start']));
            $newEnd = date('Y-m-d', strtotime($validated['period_end']));
            $newDate = filled($validated['data_date'] ?? null) ? date('Y-m-d', strtotime($validated['data_date'])) : null;

            if ($current && $current->period_start?->toDateString() === $newStart && $current->period_end?->toDateString() === $newEnd
                && (int) $current->analyzed_information === (int) $validated['analyzed_information']
                && (int) $current->protests === (int) $validated['protests']
                && (int) $current->complaints === (int) $validated['complaints']
                && $current->data_date?->toDateString() === $newDate) {
                return false;
            }

            $changedAt = now();
            $current?->update(['valid_until' => $changedAt]);
            ObuMonitoringPeriod::create([
                'organization_id' => $organization->id,
                'period_start' => $newStart,
                'period_end' => $newEnd,
                'analyzed_information' => $validated['analyzed_information'],
                'protests' => $validated['protests'],
                'complaints' => $validated['complaints'],
                'data_date' => $newDate,
                'valid_from' => $changedAt,
                'user_id' => auth()->id(),
            ]);

            return true;
        });

        return to_route('admin.obu.index')->with(
            $changed ? 'obu_monitoring_success' : 'obu_monitoring_info',
            $changed ? 'Datos de monitoreo actualizados correctamente.' : 'No se detectaron cambios para guardar.'
        );
    }

    public function updateDataset(Request $request): RedirectResponse
    {
        $datasetKeys = ObuDashboardDataService::DATASETS;
        $datasetKey = $request->validate([
            'dataset_key' => ['required', 'string', 'in:'.implode(',', $datasetKeys)],
        ])['dataset_key'];
        $technicalRules = in_array($datasetKey, ['documented_complaints', 'protest_types'], true) ? [] : [
            'values.*.period_year' => ['nullable', 'integer', 'between:1900,2200'],
            'values.*.category' => ['required', 'string', 'max:120'],
            'values.*.subgroup' => ['nullable', 'string', 'max:80'],
            'values.*.label' => ['required', 'string', 'max:255'],
            'values.*.percentage' => ['nullable', 'numeric', 'between:0,100'],
            'values.*.sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
        ];
        $validated = $request->validate([
            'values' => ['required', 'array', 'min:1'],
            'values.*.id' => ['required', 'integer', 'distinct'],
            'values.*.value' => ['required', 'integer', 'min:0'],
            ...$technicalRules,
        ]);
        $validated['dataset_key'] = $datasetKey;
        $organization = $this->organization();

        DB::transaction(function () use ($organization, $validated, $datasetKey): void {
            $changedAt = now();

            foreach ($validated['values'] as $input) {
                $row = ObuDatasetValue::query()
                    ->where('organization_id', $organization->id)
                    ->where('dataset_key', $datasetKey)
                    ->whereKey($input['id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if (in_array($datasetKey, ['documented_complaints', 'protest_types'], true)) {
                    $row->fill(['value' => $input['value']]);

                    if (! $row->isDirty()) {
                        continue;
                    }

                    ObuDatasetValueVersion::create([
                        'obu_dataset_value_id' => $row->id,
                        'organization_id' => $row->organization_id,
                        'dataset_key' => $row->dataset_key,
                        'period_year' => $row->getOriginal('period_year'),
                        'category' => $row->getOriginal('category'),
                        'subgroup' => $row->getOriginal('subgroup'),
                        'label' => $row->getOriginal('label'),
                        'value' => $row->getOriginal('value'),
                        'percentage' => $row->getOriginal('percentage'),
                        'sort_order' => $row->getOriginal('sort_order'),
                        'valid_from' => $row->getOriginal('updated_at') ?: $row->getOriginal('created_at') ?: $changedAt,
                        'valid_until' => $changedAt,
                        'user_id' => auth()->id(),
                    ]);

                    $row->save();
                    continue;
                }

                $input['period_year'] = $input['period_year'] ?? null;
                $input['subgroup'] = $input['subgroup'] ?? null;
                $input['percentage'] = $input['percentage'] ?? null;

                $duplicate = ObuDatasetValue::query()
                    ->where('organization_id', $organization->id)
                    ->where('dataset_key', $validated['dataset_key'])
                    ->where('id', '<>', $row->id)
                    ->where('category', $input['category'])
                    ->when($input['period_year'] === null, fn ($query) => $query->whereNull('period_year'), fn ($query) => $query->where('period_year', $input['period_year']))
                    ->when($input['subgroup'] === null, fn ($query) => $query->whereNull('subgroup'), fn ($query) => $query->where('subgroup', $input['subgroup']))
                    ->exists();

                if ($duplicate) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'values' => 'No puede haber dos registros activos con la misma identidad de dataset.',
                    ]);
                }

                $row->fill([
                    'period_year' => $input['period_year'],
                    'category' => $input['category'],
                    'subgroup' => $input['subgroup'],
                    'label' => $input['label'],
                    'value' => $input['value'],
                    'percentage' => $input['percentage'],
                    'sort_order' => $input['sort_order'],
                ]);

                if (! $row->isDirty()) {
                    continue;
                }

                ObuDatasetValueVersion::create([
                    'obu_dataset_value_id' => $row->id,
                    'organization_id' => $row->organization_id,
                    'dataset_key' => $row->dataset_key,
                    'period_year' => $row->getOriginal('period_year'),
                    'category' => $row->getOriginal('category'),
                    'subgroup' => $row->getOriginal('subgroup'),
                    'label' => $row->getOriginal('label'),
                    'value' => $row->getOriginal('value'),
                    'percentage' => $row->getOriginal('percentage'),
                    'sort_order' => $row->getOriginal('sort_order'),
                    'valid_from' => $row->getOriginal('updated_at') ?: $row->getOriginal('created_at') ?: $changedAt,
                    'valid_until' => $changedAt,
                    'user_id' => auth()->id(),
                ]);

                $row->save();
            }
        });

        $successMessage = match ($datasetKey) {
            'documented_complaints' => 'Denuncias actualizadas correctamente.',
            'protest_types' => 'Tipos de protesta actualizados correctamente.',
            default => 'Dataset actualizado y versión anterior conservada.',
        };

        return to_route('admin.obu.index')->with('obu_dataset_success', $successMessage);
    }

    private function organization(): Organization
    {
        return Organization::query()->where('slug', 'universidades')->firstOrFail();
    }

}
