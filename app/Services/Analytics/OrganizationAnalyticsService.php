<?php

namespace App\Services\Analytics;

use App\Models\AnalyticsContentClick;
use App\Models\AnalyticsNavigationClick;
use App\Models\AnalyticsPageView;
use App\Models\DashboardSyncRun;
use App\Models\Organization;
use App\Models\Publication;
use App\Services\DashboardQueryService;
use Carbon\CarbonInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class OrganizationAnalyticsService
{
    private const SYNC_STALE_MINUTES = 15;

    public function __construct(private readonly DashboardQueryService $dashboard) {}

    /** @return array<string, mixed> */
    public function dashboard(string $organization, int $days = 30): array
    {
        $startDate = today()->subDays($days - 1)->startOfDay();

        $portalViews = AnalyticsPageView::query()
            ->where('organization', 'pulso_vzla')
            ->where('page', 'home')
            ->count();
        $organizationViews = AnalyticsPageView::query()
            ->where('organization', $organization)
            ->where('page', 'organizaciones/acceso-justicia')
            ->where('created_at', '>=', $startDate)
            ->count();
        $homeNavigationClicks = AnalyticsNavigationClick::query()
            ->where('organization', $organization)
            ->where('target', 'organizaciones/acceso-justicia')
            ->where('source', 'home')
            ->count();
        $clicks = AnalyticsContentClick::query()
            ->where('organization', $organization)
            ->where('content_type', 'alert');
        $panelOrigin = [
            'pulso' => $homeNavigationClicks,
            'direct' => max($organizationViews - $homeNavigationClicks, 0),
            'total' => $organizationViews,
        ];

        return [
            'summary' => [
                'portal_views' => $portalViews,
                'organization_views' => $organizationViews,
                'home_navigation_clicks' => $homeNavigationClicks,
                'alert_clicks' => (clone $clicks)->count(),
                'home_clicks' => (clone $clicks)->where('source', 'home')->count(),
                'organization_clicks' => (clone $clicks)->where('source', 'organization')->count(),
            ],
            'chart' => $this->dailyViews($organization, $startDate),
            'panelOrigin' => $panelOrigin,
            'sync' => $this->syncStatus(),
        ];
    }

    /** @return array{alerts_count: int, last_synced_at: CarbonInterface|null, finished_at: CarbonInterface|null, status: string|null, message: string|null} */
    public function syncStatus(): array
    {
        $organization = Organization::query()
            ->where('slug', 'acceso-justicia')
            ->first();

        $run = DashboardSyncRun::query()
            ->where('organization', 'acceso_justicia')
            ->where('process', 'publications')
            ->latest('started_at')
            ->first();

        if (
            $run?->status === 'running'
            && $run->started_at?->lt(now()->subMinutes(self::SYNC_STALE_MINUTES))
            && ! $this->isPublicationSyncLockHeld()
        ) {
            $run->update([
                'status' => 'failed',
                'finished_at' => now(),
                'error' => 'Sincronización interrumpida o expirada.',
            ]);
            $run->refresh();
        }

        $legacySuccessfulRun = $run ? null : DashboardSyncRun::query()
            ->whereNull('organization')
            ->where('status', 'completed')
            ->latest('finished_at')
            ->first();
        $lastSyncedAt = null;
        $finishedAt = null;
        $status = null;
        $message = null;

        if ($run) {
            $finishedAt = filled($run->finished_at) ? Carbon::parse($run->finished_at) : null;
            $lastSyncedAt = $run->status === 'success'
                ? $finishedAt
                : (filled($organization?->last_synced_at) ? Carbon::parse($organization->last_synced_at) : null);
            $status = $run->status;
            $message = $run->error;
        } elseif ($legacySuccessfulRun) {
            $lastSyncedAt = filled($legacySuccessfulRun->finished_at)
                ? Carbon::parse($legacySuccessfulRun->finished_at)
                : null;
            $finishedAt = $lastSyncedAt;
            $status = 'success';
        } elseif (filled($organization?->last_synced_at)) {
            $lastSyncedAt = Carbon::parse($organization->last_synced_at);
        }

        return [
            'alerts_count' => $organization
                ? $this->dashboard->accesoLegalPublicationsQuery()->count()
                : 0,
            'last_synced_at' => $lastSyncedAt,
            'finished_at' => $finishedAt,
            'status' => $status,
            'message' => $message,
        ];
    }

    public function alertRanking(
        string $organization,
        int $days = 30,
        int $perPage = 5,
        int $page = 1,
        string $pageName = 'alertsPage',
    ): LengthAwarePaginator {
        $startDate = today()->subDays($days - 1)->startOfDay();

        return $this->ranking($organization, $startDate, $perPage, $page, $pageName);
    }

    private function isPublicationSyncLockHeld(): bool
    {
        $lock = Cache::lock('dashboard-publications-sync', 240);

        if ($lock->get()) {
            $lock->release();

            return false;
        }

        return true;
    }

    /** @return array{labels: array<int, string>, portal: array<int, int>, organization: array<int, int>} */
    private function dailyViews(string $organization, CarbonInterface $startDate): array
    {
        $portal = $this->viewsByDate('pulso_vzla', 'home', $startDate);
        $organizationViews = $this->viewsByDate($organization, 'organizaciones/acceso-justicia', $startDate);
        $dates = collect(range(0, (int) $startDate->diffInDays(today())))
            ->map(fn (int $offset) => $startDate->copy()->addDays($offset));

        return [
            'labels' => $dates->map(fn (CarbonInterface $date) => $date->format('d/m'))->all(),
            'portal' => $dates->map(fn (CarbonInterface $date) => (int) ($portal[$date->toDateString()] ?? 0))->all(),
            'organization' => $dates->map(fn (CarbonInterface $date) => (int) ($organizationViews[$date->toDateString()] ?? 0))->all(),
        ];
    }

    /** @return Collection<string, int> */
    private function viewsByDate(string $organization, string $page, CarbonInterface $startDate): Collection
    {
        return AnalyticsPageView::query()
            ->selectRaw('DATE(created_at) as view_date, COUNT(*) as total')
            ->where('organization', $organization)
            ->where('page', $page)
            ->where('created_at', '>=', $startDate)
            ->groupBy('view_date')
            ->pluck('total', 'view_date');
    }

    /** @return LengthAwarePaginator<int, array{publication_id: int, alert: string|null, url: string, home_clicks: int, organization_clicks: int, total_clicks: int, tendency: array<int, int>}> */
    private function ranking(
        string $organization,
        CarbonInterface $startDate,
        int $perPage,
        int $currentPage,
        string $pageName,
    ): LengthAwarePaginator {
        $clicks = AnalyticsContentClick::query()
            ->select('content_id')
            ->selectRaw("SUM(CASE WHEN source = 'home' THEN 1 ELSE 0 END) as home_clicks")
            ->selectRaw("SUM(CASE WHEN source = 'organization' THEN 1 ELSE 0 END) as organization_clicks")
            ->selectRaw('COUNT(*) as total_clicks')
            ->where('organization', $organization)
            ->where('content_type', 'alert')
            ->where('created_at', '>=', $startDate)
            ->groupBy('content_id')
            ->orderByDesc('total_clicks')
            ->orderBy('content_id')
            ->get();
        $publications = Publication::query()
            ->whereIn('id', $clicks->pluck('content_id'))
            ->get()
            ->keyBy('id');

        $ranked = $clicks->map(function ($click) use ($publications): ?array {
            $publication = $publications->get($click->content_id);

            if (! $publication) {
                return null;
            }

            return [
                'publication_id' => $publication->id,
                'alert' => $publication->excerpt ?: $publication->title,
                'url' => $publication->url,
                'home_clicks' => (int) $click->home_clicks,
                'organization_clicks' => (int) $click->organization_clicks,
                'total_clicks' => (int) $click->total_clicks,
            ];
        })->filter()->values()->all();

        $paginator = new LengthAwarePaginator(
            collect($ranked)->forPage($currentPage, $perPage)->values(),
            count($ranked),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => $pageName,
            ],
        );

        $visibleIds = $paginator->getCollection()->pluck('publication_id');

        if ($visibleIds->isEmpty()) {
            return $paginator->withQueryString();
        }

        $dailyClicks = AnalyticsContentClick::query()
            ->select('content_id')
            ->selectRaw('DATE(created_at) as click_date')
            ->selectRaw('COUNT(*) as daily_total')
            ->where('organization', $organization)
            ->where('content_type', 'alert')
            ->where('created_at', '>=', $startDate)
            ->whereIn('content_id', $visibleIds)
            ->groupBy('content_id', 'click_date')
            ->get()
            ->groupBy('content_id');
        $dates = collect(range(0, (int) $startDate->diffInDays(today())))
            ->map(fn (int $offset) => $startDate->copy()->addDays($offset)->toDateString());

        $paginator->setCollection($paginator->getCollection()->map(function (array $item) use ($dailyClicks, $dates): array {
            return $item + [
                'tendency' => $dates->map(function (string $date) use ($dailyClicks, $item): int {
                    return (int) ($dailyClicks->get($item['publication_id'], collect())
                        ->firstWhere('click_date', $date)?->daily_total ?? 0);
                })->all(),
            ];
        }));

        return $paginator->withQueryString();
    }
}
