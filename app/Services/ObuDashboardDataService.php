<?php

namespace App\Services;

use App\Models\ObuBimonthlyAlert;
use App\Models\ObuDatasetValue;
use App\Models\ObuMetricSnapshot;
use App\Models\ObuMonitoringPeriod;
use App\Models\ObuMonthlyNote;
use App\Models\Organization;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ObuDashboardDataService
{
    public const DATASETS = [
        'documented_complaints',
        'protest_types',
        'university_protests_by_year',
        'historical_complaints',
        'university_ranking',
        'complaint_sources',
        'news_by_university_type',
    ];

    public function currentMonitoringPeriod(): ?ObuMonitoringPeriod
    {
        if (! Schema::hasTable('obu_monitoring_periods')) {
            return null;
        }

        return ObuMonitoringPeriod::query()->current()->latest('valid_from')->first();
    }

    public function lastEditorialUpdate(): ?CarbonInterface
    {
        $organizationId = $this->organizationId();

        if (! $organizationId) {
            return null;
        }

        $timestamps = collect([
            Schema::hasTable('obu_metric_snapshots')
                ? ObuMetricSnapshot::query()->where('organization_id', $organizationId)->max('valid_from')
                : null,
            Schema::hasTable('obu_monitoring_periods')
                ? ObuMonitoringPeriod::query()->where('organization_id', $organizationId)->max('valid_from')
                : null,
            Schema::hasTable('obu_monthly_notes')
                ? ObuMonthlyNote::query()->where('organization_id', $organizationId)->max('created_at')
                : null,
            Schema::hasTable('obu_bimonthly_alerts')
                ? ObuBimonthlyAlert::query()->where('organization_id', $organizationId)->max('created_at')
                : null,
        ])->filter()->map(fn ($value) => $value instanceof CarbonInterface ? $value : Carbon::parse($value));

        return $timestamps->sortByDesc(fn (CarbonInterface $value) => $value->getTimestamp())
            ->first()
            ?->copy()
            ->setTimezone('America/Caracas');
    }

    /** @return array<string, mixed> */
    public function publicData(): array
    {
        $period = $this->currentMonitoringPeriod();
        $year = $period?->period_end?->year ?? 2025;
        $datasets = collect(self::DATASETS)->mapWithKeys(fn (string $key) => [$key => $this->dataset($key, $key === 'university_ranking' ? 2025 : null)])->all();
        $notes = Schema::hasTable('obu_monthly_notes')
            ? ObuMonthlyNote::query()->where('organization_id', $this->organizationId())->where('is_published', true)->latest('publication_date')->first()
            : null;
        $alerts = Schema::hasTable('obu_bimonthly_alerts')
            ? ObuBimonthlyAlert::query()->where('organization_id', $this->organizationId())->where('is_published', true)->latest('period_end')->first()
            : null;

        return [
            'obuMonitoringPeriod' => $period,
            'obuMonthlyNote' => $notes ? $this->withFileUrl($notes, 'image_path') : null,
            'obuBimonthlyAlert' => $alerts ? $this->withFileUrl($alerts, 'image_path', 'file_path') : null,
            'obuDatasets' => $datasets,
            'obuDatasetYears' => Schema::hasTable('obu_dataset_values')
                ? ObuDatasetValue::query()->where('organization_id', $this->organizationId())->whereNotNull('period_year')->distinct()->orderBy('period_year')->pluck('period_year')->values()->all()
                : [],
            'obuDefaultDatasetYear' => $year,
        ];
    }

    /** @return Collection<int, array<string, mixed>> */
    public function dataset(string $key, ?int $year = null): Collection
    {
        if (! Schema::hasTable('obu_dataset_values')) {
            return collect();
        }

        return ObuDatasetValue::query()
            ->where('organization_id', $this->organizationId())
            ->where('dataset_key', $key)
            ->when(in_array($key, ['documented_complaints', 'protest_types', 'complaint_sources'], true), fn ($query) => $query->whereNotNull('subgroup'))
            ->when($year !== null, fn ($query) => $query->where('period_year', $year))
            ->orderBy('sort_order')
            ->get()
            ->map(fn (ObuDatasetValue $row) => [
                'year' => $row->period_year,
                'category' => $row->category,
                'subgroup' => $row->subgroup,
                'label' => $row->label,
                'value' => (int) $row->value,
                'percentage' => $row->percentage !== null ? (float) $row->percentage : null,
                'sort_order' => (int) $row->sort_order,
            ]);
    }

    private function organizationId(): ?int
    {
        return Schema::hasTable('organizations')
            ? Organization::query()->where('slug', 'universidades')->value('id')
            : null;
    }

    /** @return array<string, mixed> */
    private function withFileUrl(object $model, string ...$fields): array
    {
        $data = $model->toArray();
        foreach ($fields as $field) {
            $data[$field.'_url'] = filled($model->{$field}) ? Storage::disk('public')->url($model->{$field}) : null;
        }

        return $data;
    }
}
