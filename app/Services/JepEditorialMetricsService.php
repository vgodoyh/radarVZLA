<?php

namespace App\Services;

use App\Models\JepMetricSnapshot;
use App\Models\Organization;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class JepEditorialMetricsService
{
    private const RELATION_FIELDS = [
        'groups' => 'vulnerableGroups',
        'centers' => 'detentionCenters',
        'death_custody_distribution' => 'deathCustodyDistribution',
    ];

    public function formatTrend($value): ?string
    {
        if ($value === null) return null;
        if ((float) $value === 0.0) return '0%';

        $formatted = number_format((float) $value, 2, '.', '');
        $formatted = rtrim($formatted, '0');
        if (str_ends_with($formatted, '.')) $formatted .= '0';

        return ($value > 0 ? '+' : '').$formatted.'%';
    }

    public function current(?Organization $organization = null): ?JepMetricSnapshot
    {
        if (! Schema::hasTable('jep_metric_snapshots')) {
            return null;
        }

        $organization ??= Organization::query()->where('slug', 'jep')->first();

        return $organization
            ? JepMetricSnapshot::query()->current()
                ->where('organization_id', $organization->id)
                ->with(['vulnerableGroups', 'detentionCenters', 'deathCustodyDistribution', 'user:id,name'])
                ->latest('valid_from')
                ->first()
            : null;
    }

    public function history(Organization $organization)
    {
        return JepMetricSnapshot::query()
            ->where('organization_id', $organization->id)
            ->with(['vulnerableGroups', 'detentionCenters', 'deathCustodyDistribution', 'user:id,name'])
            ->orderByDesc('valid_from')
            ->get();
    }

    public function lastEditorialUpdate(?Organization $organization = null): ?CarbonInterface
    {
        if (! Schema::hasTable('jep_metric_snapshots')) {
            return null;
        }

        $organization ??= Organization::query()->where('slug', 'jep')->first();

        if (! $organization) {
            return null;
        }

        $value = JepMetricSnapshot::query()
            ->where('organization_id', $organization->id)
            ->max('valid_from');

        return $value ? Carbon::parse($value)->setTimezone('America/Caracas') : null;
    }

    /**
     * Creates a complete snapshot while changing only the requested module data.
     * Relations omitted from $relations are copied from the current snapshot.
     */
    public function updateSnapshot(Organization $organization, array $changes, array $relations = []): bool
    {
        return DB::transaction(function () use ($organization, $changes, $relations): bool {
            $current = JepMetricSnapshot::query()
                ->where('organization_id', $organization->id)
                ->current()
                ->lockForUpdate()
                ->first();

            if (! $current) {
                return false;
            }

            $scalarChanges = collect($changes)->except(array_keys(self::RELATION_FIELDS))->all();
            $relationChanges = collect($relations)->only(array_keys(self::RELATION_FIELDS))->all();
            $scalarChanged = collect($scalarChanges)->some(fn ($value, $key) => ! $this->sameValue($current->{$key}, $value, str_ends_with((string) $key, '_trend')));
            $relationsChanged = collect($relationChanges)->some(function ($items, $key) use ($current): bool {
                $relation = self::RELATION_FIELDS[$key];
                return $this->normalizeRelation($current->{$relation}, $key) !== $this->normalizeRelation(collect($items), $key);
            });

            if (! $scalarChanged && ! $relationsChanged) {
                return false;
            }

            $changedAt = now();
            $payload = $current->only((new JepMetricSnapshot())->getFillable());
            $payload = array_merge($payload, $scalarChanges, [
                'organization_id' => $organization->id,
                'valid_from' => $changedAt,
                'valid_until' => null,
                'user_id' => auth()->id(),
            ]);
            unset($payload['id'], $payload['created_at'], $payload['updated_at']);

            $current->update(['valid_until' => $changedAt]);
            $snapshot = JepMetricSnapshot::create($payload);

            foreach (self::RELATION_FIELDS as $key => $relation) {
                $items = array_key_exists($key, $relationChanges)
                    ? $relationChanges[$key]
                    : $this->relationAttributes($current->{$relation}, $key);
                foreach ($items as $item) {
                    $snapshot->{$relation}()->create($this->relationAttributes(collect([$item]), $key)->first());
                }
            }

            return true;
        });
    }

    private function sameValue($left, $right, bool $decimal = false): bool
    {
        if ($decimal) {
            return ($left === null && ($right === null || $right === ''))
                || ($left !== null && $right !== null && round((float) $left, 2) === round((float) $right, 2));
        }

        return (string) $left === (string) $right;
    }

    private function normalizeRelation($items, string $key): array
    {
        return $this->relationAttributes(collect($items), $key)->values()->all();
    }

    private function relationAttributes($items, string $key): \Illuminate\Support\Collection
    {
        $fields = match ($key) {
            'groups' => ['group_key', 'label', 'value', 'sort_order'],
            'centers' => ['name', 'value', 'sort_order'],
            default => ['category_key', 'label', 'value', 'sort_order'],
        };

        return collect($items)->map(fn ($item) => collect(is_array($item) ? $item : $item->toArray())
            ->only($fields)
            ->map(fn ($value) => (string) $value)
            ->all());
    }
}
