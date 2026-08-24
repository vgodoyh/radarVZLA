<?php

namespace App\Jobs;

use App\Models\DashboardSyncRun;
use App\Models\Organization;
use App\Models\Publication;
use App\Services\TwitterService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Throwable;

class SyncDashboardData implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 180;

    public function __construct(
        public ?string $organizationSlug = null,
        public ?int $runId = null,
    ) {}

    public function handle(TwitterService $twitter): void
    {
        $run = $this->runId
            ? DashboardSyncRun::query()->findOrFail($this->runId)
            : DashboardSyncRun::create([
                'organization' => $this->organizationKey(),
                'process' => 'publications',
                'status' => 'running',
                'started_at' => now(),
            ]);

        if ($run->status === 'failed' && str_starts_with((string) $run->error, 'Sincronización interrumpida o expirada')) {
            return;
        }

        $run->update(['status' => 'running', 'finished_at' => null, 'error' => null]);
        $lock = Cache::lock('dashboard-publications-sync', $this->timeout + 60);
        $lockAcquired = false;

        try {
        $lockAcquired = $lock->get();

        if (! $lockAcquired) {
            $run->update([
                'status' => 'failed',
                'finished_at' => now(),
                'error' => 'Ya existe una sincronización en ejecución.',
            ]);

            return;
        }

        $count = 0;
        $organizationRun = null;

            $items = $this->organizations()
                ->when($this->organizationSlug, fn ($items) => $items->where('slug', $this->organizationSlug));

            if ($this->organizationSlug && $items->isEmpty()) {
                throw new \InvalidArgumentException("Organización no configurada: {$this->organizationSlug}");
            }

            foreach ($items as $item) {
                $organizationRun = ! $this->organizationSlug && $item['slug'] === 'acceso-justicia'
                    ? DashboardSyncRun::create([
                        'organization' => 'acceso_justicia',
                        'process' => 'publications',
                        'status' => 'running',
                        'started_at' => now(),
                    ])
                    : null;
                $organization = Organization::updateOrCreate(['slug' => $item['slug']], $item + ['active' => true]);

                $limit = 7;
                $posts = $twitter->getLatestPosts($item['x_username'], $limit);

                if ($item['slug'] === 'acceso-justicia') {
                    $posts = $posts
                        ->concat($twitter->getPostsByHashtag($item['x_username'], '#AlertaLegal', 7))
                        ->unique('id')
                        ->values();
                }

                foreach ($posts as $post) {
                    $externalId = (string) ($post['id'] ?? sha1($post['url'] ?? $post['text'] ?? ''));
                    Publication::updateOrCreate(
                        ['organization_id' => $organization->id, 'source' => 'x', 'external_id' => $externalId],
                        ['category' => $post['category'] ?? null, 'excerpt' => $post['text'] ?? '', 'url' => $post['url'] ?? "https://x.com/{$item['x_username']}", 'image_url' => $post['image'] ?? null, 'likes' => $post['likes'] ?? 0, 'shares' => $post['retweets'] ?? 0, 'published_at' => $this->date($post['created_at'] ?? null), 'metadata' => $post]
                    );
                    $count++;
                }

                if ($posts->isNotEmpty()) {
                    $organization->update(['last_synced_at' => now()]);
                }

                $organizationRun?->update([
                    'status' => 'success',
                    'finished_at' => now(),
                    'summary' => ['publications' => $posts->count()],
                ]);
                $organizationRun = null;
            }

            $run->update([
                'status' => $this->organizationSlug ? 'success' : 'completed',
                'finished_at' => now(),
                'summary' => ['publications' => $count],
            ]);
        } catch (Throwable $exception) {
            $organizationRun?->update([
                'status' => 'failed',
                'finished_at' => now(),
                'error' => $exception->getMessage(),
            ]);
            $run->update(['status' => 'failed', 'finished_at' => now(), 'error' => $exception->getMessage()]);
            throw $exception;
        } finally {
            if ($lockAcquired) {
                $lock->release();
            }
        }
    }

    private function organizationKey(): ?string
    {
        return $this->organizationSlug === 'acceso-justicia' ? 'acceso_justicia' : $this->organizationSlug;
    }

    /** @return Collection<int, array<string, mixed>> */
    private function organizations(): Collection
    {
        $configured = config('dashboard.organizations', []);

        if (! is_array($configured)) {
            return collect();
        }

        $organizations = [];

        foreach ($configured as $item) {
            if (! is_array($item)) {
                continue;
            }

            $organization = [];

            foreach ($item as $key => $value) {
                if (is_string($key)) {
                    $organization[$key] = $value;
                }
            }

            $organizations[] = $organization;
        }

        return collect($organizations);
    }

    private function date(mixed $value): ?Carbon
    {
        try {
            return $value ? Carbon::parse($value) : null;
        } catch (Throwable) {
            return null;
        }
    }
}
