<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SyncDashboardData;
use App\Models\DashboardSyncRun;
use App\Services\Analytics\OrganizationAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Throwable;

class AccesoJusticiaSyncController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        $gate = Cache::lock('dispatch-access-justice-sync', 10);

        if (! $gate->get()) {
            return back()->with('sync_error', 'Ya existe una solicitud de sincronización en proceso.');
        }

        try {
            $active = DashboardSyncRun::query()
                ->where('status', 'running')
                ->where('started_at', '>=', now()->subMinutes(10))
                ->where(function ($query) {
                    $query->where('organization', 'acceso_justicia')
                        ->orWhereNull('organization');
                })
                ->exists();

            if ($active) {
                return back()->with('sync_error', 'La sincronización ya está en ejecución.');
            }

            $run = DashboardSyncRun::create([
                'organization' => 'acceso_justicia',
                'process' => 'publications',
                'status' => 'running',
                'started_at' => now(),
            ]);

            try {
                SyncDashboardData::dispatch('acceso-justicia', $run->id);
            } catch (Throwable $exception) {
                $run->update([
                    'status' => 'failed',
                    'finished_at' => now(),
                    'error' => $exception->getMessage(),
                ]);

                report($exception);

                return back()->with('sync_error', 'No fue posible iniciar la sincronización.');
            }

            return back()->with('sync_success', 'La sincronización fue iniciada.');
        } finally {
            $gate->release();
        }
    }

    public function status(OrganizationAnalyticsService $analytics): JsonResponse
    {
        $sync = $analytics->syncStatus();

        return response()->json([
            'status' => $sync['status'],
            'finished_at' => $sync['finished_at']?->toIso8601String(),
            'last_synced_at' => $sync['last_synced_at']?->toIso8601String(),
            'alerts_count' => $sync['alerts_count'],
        ]);
    }
}
