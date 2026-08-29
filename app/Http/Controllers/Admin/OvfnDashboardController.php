<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OvfnVerificationTotal;
use App\Services\Analytics\OrganizationAnalyticsService;
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

        return view('admin.organizations.ovfn.index', [
            ...$analytics->dashboard('ovfn'),
            'organization' => $organization,
            'currentVerificationTotal' => $currentVerificationTotal,
            'verificationHistory' => $verificationHistory,
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
            'ovfn_status', $changed ? 'Total de Verificaciones actualizado correctamente.' : 'No se detectaron cambios.'
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
