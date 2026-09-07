<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ObuBimonthlyAlert;
use App\Models\ObuDatasetValue;
use App\Models\ObuMetricSnapshot;
use App\Models\ObuMonitoringPeriod;
use App\Models\ObuMonthlyNote;
use App\Models\Organization;
use App\Services\Analytics\OrganizationAnalyticsService;
use App\Services\ObuDashboardDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ObuDashboardController extends Controller
{
    public function __invoke(OrganizationAnalyticsService $analytics, ObuDashboardDataService $obuData): View
    {
        $organization = $this->organization();

        return view('admin.organizations.obu.index', [
            ...$analytics->dashboard('universidades'),
            'organization' => $organization,
            'currentMetrics' => $this->current($organization),
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
        ]);
    }

    public function updateMetrics(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'universities_monitored' => ['required', 'integer', 'min:0'],
            'protests' => ['required', 'integer', 'min:0'],
            'complaints' => ['required', 'integer', 'min:0'],
            'data_date' => ['nullable', 'date'],
        ]);
        $organization = $this->organization();

        $changed = DB::transaction(function () use ($organization, $validated): bool {
            $current = ObuMetricSnapshot::query()
                ->where('organization_id', $organization->id)
                ->current()
                ->lockForUpdate()
                ->first();
            $newDate = filled($validated['data_date'] ?? null)
                ? date('Y-m-d', strtotime($validated['data_date']))
                : null;

            if ($current
                && (int) $current->universities_monitored === (int) $validated['universities_monitored']
                && (int) $current->protests === (int) $validated['protests']
                && (int) $current->complaints === (int) $validated['complaints']
                && $current->data_date?->toDateString() === $newDate) {
                return false;
            }

            $changedAt = now();
            $current?->update(['valid_until' => $changedAt]);

            ObuMetricSnapshot::create([
                'organization_id' => $organization->id,
                'universities_monitored' => $validated['universities_monitored'],
                'protests' => $validated['protests'],
                'complaints' => $validated['complaints'],
                'data_date' => $newDate,
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

    private function organization(): Organization
    {
        return Organization::query()->where('slug', 'universidades')->firstOrFail();
    }

    private function current(Organization $organization): ?ObuMetricSnapshot
    {
        return ObuMetricSnapshot::query()
            ->where('organization_id', $organization->id)
            ->current()
            ->first();
    }
}
