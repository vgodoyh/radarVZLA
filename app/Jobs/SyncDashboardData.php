<?php

namespace App\Jobs;

use App\Models\DashboardSyncRun;
use App\Models\Organization;
use App\Models\Publication;
use App\Services\TwitterService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Throwable;

class SyncDashboardData implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 180;

    public function handle(TwitterService $twitter): void
    {
        $run = DashboardSyncRun::create(['status' => 'running', 'started_at' => now()]);
        $count = 0;

        try {
            foreach (config('dashboard.organizations') as $item) {
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
            }

            $run->update(['status' => 'completed', 'finished_at' => now(), 'summary' => ['publications' => $count]]);
        } catch (Throwable $exception) {
            $run->update(['status' => 'failed', 'finished_at' => now(), 'error' => $exception->getMessage()]);
            throw $exception;
        }
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
