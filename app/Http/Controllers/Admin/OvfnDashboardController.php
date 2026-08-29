<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OvfnVerificationTotal;
use App\Models\OvfnPlatformDistribution;
use App\Models\OvfnPlatformDistributionItem;
use App\Services\Analytics\OrganizationAnalyticsService;
use App\Services\OvfnEditorialMetricsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OvfnDashboardController extends Controller
{
    public function __invoke(OrganizationAnalyticsService $analytics): View
    {
        $organization = $this->organization();
        $currentVerificationTotal = $this->current($organization);
        $verificationHistory = OvfnVerificationTotal::query()
            ->where('organization_id', $organization->id)
            ->with('user:id,name')
            ->orderByDesc('valid_from')
            ->get();
        $currentDistribution = OvfnPlatformDistribution::query()
            ->where('organization_id', $organization->id)->current()->with('items')->first();
        $distributionHistory = OvfnPlatformDistribution::query()
            ->where('organization_id', $organization->id)->with(['items', 'user:id,name'])
            ->orderByDesc('valid_from')->get();

        return view('admin.organizations.ovfn.index', [
            ...$analytics->dashboard('ovfn'),
            'organization' => $organization,
            'currentVerificationTotal' => $currentVerificationTotal,
            'verificationHistory' => $verificationHistory,
            'currentDistribution' => $currentDistribution,
            'distributionHistory' => $distributionHistory,
        ]);
    }

    public function updateTotalVerifications(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'total' => ['required', 'integer', 'min:0'],
            'data_date' => ['required', 'date'],
        ]);
        $organization = $this->organization();

        $changed = DB::transaction(function () use ($organization, $validated): bool {
            $current = OvfnVerificationTotal::query()
                ->where('organization_id', $organization->id)
                ->current()
                ->lockForUpdate()
                ->first();
            $newDate = date('Y-m-d', strtotime($validated['data_date']));

            if ($current && (int) $current->total === (int) $validated['total']
                && $current->data_date?->toDateString() === $newDate) {
                return false;
            }

            $changedAt = now();
            $current?->update(['valid_until' => $changedAt]);

            OvfnVerificationTotal::create([
                'organization_id' => $organization->id,
                'total' => $validated['total'],
                'data_date' => $newDate,
                'valid_from' => $changedAt,
                'user_id' => auth()->id(),
            ]);

            return true;
        });

        return to_route('admin.ovfn.index')->with(
            $changed ? 'ovfn_verification_success' : 'ovfn_verification_info',
            $changed
                ? 'Total de verificaciones actualizado correctamente.'
                : 'No se detectaron cambios para guardar.'
        );
    }

    public function updatePlatformDistribution(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'data_from_date' => ['required', 'date'],
            'platforms' => ['required', 'array:'.implode(',', OvfnEditorialMetricsService::PLATFORMS)],
            'platforms.*' => ['required', 'integer', 'min:0'],
        ]);
        abort_unless(collect($validated['platforms'])->contains(fn ($value) => (int) $value > 0), 422);
        $organization = $this->organization();

        $changed = DB::transaction(function () use ($organization, $validated): bool {
            $current = OvfnPlatformDistribution::query()->where('organization_id', $organization->id)
                ->current()->with('items')->lockForUpdate()->first();
            $newDate = date('Y-m-d', strtotime($validated['data_from_date']));
            $currentValues = $current?->items->pluck('value', 'platform')->map(fn ($value) => (int) $value)->all() ?? [];
            $newValues = collect(OvfnEditorialMetricsService::PLATFORMS)
                ->mapWithKeys(fn (string $platform) => [$platform => (int) $validated['platforms'][$platform]])->all();

            if ($current && $current->data_from_date?->toDateString() === $newDate && $currentValues === $newValues) {
                return false;
            }

            $changedAt = now();
            $current?->update(['valid_until' => $changedAt]);
            $distribution = OvfnPlatformDistribution::create([
                'organization_id' => $organization->id,
                'data_from_date' => $newDate,
                'valid_from' => $changedAt,
                'user_id' => auth()->id(),
            ]);
            foreach ($newValues as $position => $value) {
                OvfnPlatformDistributionItem::create([
                    'distribution_id' => $distribution->id,
                    'platform' => $position,
                    'value' => $value,
                    'sort_order' => array_search($position, OvfnEditorialMetricsService::PLATFORMS, true) + 1,
                ]);
            }

            return true;
        });

        return to_route('admin.ovfn.index')->with(
            $changed ? 'ovfn_distribution_success' : 'ovfn_distribution_info',
            $changed
                ? 'Distribución por plataforma actualizada correctamente.'
                : 'No se detectaron cambios para guardar.'
        );
    }

    private function organization(): Organization
    {
        return Organization::query()->where('slug', 'fake-news')->firstOrFail();
    }

    private function current(Organization $organization): ?OvfnVerificationTotal
    {
        return OvfnVerificationTotal::query()
            ->where('organization_id', $organization->id)
            ->current()
            ->first();
    }
}
