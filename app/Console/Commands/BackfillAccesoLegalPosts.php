<?php

namespace App\Console\Commands;

use App\Models\Organization;
use App\Models\Publication;
use App\Services\TwitterService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class BackfillAccesoLegalPosts extends Command
{
    protected $signature = 'dashboard:backfill-alertas-legales
        {--since=2026-06-01 : Inclusive start date}
        {--until=2026-08-01 : Exclusive end date}';

    protected $description = 'Import historical #AlertaLegal posts from AccesoAJusticia';

    public function handle(TwitterService $twitter): int
    {
        $organization = Organization::query()->where('slug', 'acceso-justicia')->firstOrFail();
        $posts = $twitter->getPostsByHashtagBetween(
            $organization->x_username,
            '#AlertaLegal',
            (string) $this->option('since'),
            (string) $this->option('until')
        );

        foreach ($posts as $post) {
            Publication::updateOrCreate(
                [
                    'organization_id' => $organization->id,
                    'source' => 'x',
                    'external_id' => (string) $post['id'],
                ],
                [
                    'category' => $post['category'] ?? null,
                    'excerpt' => $post['text'] ?? '',
                    'url' => $post['url'],
                    'image_url' => $post['image'] ?? null,
                    'likes' => $post['likes'] ?? 0,
                    'shares' => $post['retweets'] ?? 0,
                    'published_at' => Carbon::parse($post['created_at']),
                    'metadata' => $post,
                ]
            );
        }

        if ($posts->isNotEmpty()) {
            $organization->update(['last_synced_at' => now()]);
        }

        $this->components->info("{$posts->count()} publicaciones históricas procesadas.");

        return self::SUCCESS;
    }
}
