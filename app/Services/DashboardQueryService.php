<?php

namespace App\Services;

use App\Models\DashboardSyncRun;
use App\Models\Organization;
use App\Models\Publication;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DashboardQueryService
{
    /** @return array<string, mixed> */
    public function get(): array
    {
        $organizations = $this->organizations();
        $bySlug = $organizations->keyBy('slug');
        $accesoLegalPosts = $this->posts($bySlug->get('acceso-justicia'))
            ->filter(fn (array $post) => Str::contains(
                Str::lower($post['text'] ?? ''),
                '#alertalegal'
            ))
            ->take(5)
            ->values();

        return [
            'stats' => collect($this->configStats())->map(fn (array $stat) => [
                'label' => __("dashboard.stats.{$stat['key']}"),
                'value' => number_format($stat['value'], 0, ',', '.'),
                'change' => sprintf('%+.1f%%', $stat['change']),
                'icon' => $stat['icon'],
                'direction' => $stat['change'] >= 0 ? 'up' : 'down',
                'sentiment' => $stat['sentiment'],
            ])->all(),
            'organizations' => $organizations,
            'alertasLegales' => $accesoLegalPosts,
            'accesoLegalPublicationsTotal' => $this->hasPublicationTables()
                ? $this->accesoLegalPublicationsQuery()->count()
                : 0,
            'accesoPosts' => $this->panoramaPosts($accesoLegalPosts),
            'postsFakeNewsX' => $this->posts($bySlug->get('fake-news')),
            'postsFakeNewsWeb' => ['en_profundidad' => collect(), 'noti_fake' => collect()],
            'economicSocialItems' => [
                ['label' => __('dashboard.indicators.living_wage'), 'icon' => 'bi-cash-coin', 'value' => 150],
                ['label' => __('dashboard.indicators.infrastructure_damage'), 'icon' => 'bi-building', 'value' => 98],
                ['label' => __('dashboard.indicators.student_support'), 'icon' => 'bi-mortarboard', 'value' => 62],
            ],
            'civilPoliticalItems' => [
                ['label' => __('dashboard.indicators.university_autonomy'), 'icon' => 'bi-shield-check', 'value' => 124],
                ['label' => __('dashboard.indicators.elections'), 'icon' => 'bi-check2-square', 'value' => 76],
                ['label' => __('dashboard.indicators.peaceful_assembly'), 'icon' => 'bi-people', 'value' => 53],
            ],
            'years' => ['2020', '2021', '2022', '2023', '2024'],
            'protestsData' => [120, 260, 410, 240, 300],
            'complaintsData' => [95, 200, 380, 210, 330],
            'economicSocialData' => [60, 110, 190, 120, 150],
            'civilPoliticalData' => [35, 90, 190, 90, 180],
            'lastSync' => $this->lastSync(),
        ];
    }

    public function accesoLegalPublicationsQuery(): Builder
    {
        return Publication::query()
            ->whereHas('organization', fn ($query) => $query->where('slug', 'acceso-justicia'))
            ->where('source', 'x')
            ->where('excerpt', 'like', '%#AlertaLegal%');
    }

    private function hasPublicationTables(): bool
    {
        return Schema::hasTable('organizations') && Schema::hasTable('publications');
    }

    /** @return Collection<int, array<string, mixed>> */
    private function organizations(): Collection
    {
        if (! Schema::hasTable('organizations')) {
            return collect($this->configOrganizations())->map(fn (array $item) => $this->fallbackOrganization($item));
        }

        $items = Organization::query()
            ->where('active', true)
            ->with([
                'publications' => fn ($query) => $query
                    ->where(function ($query) {
                        $query
                            ->whereHas('organization', fn ($organization) => $organization->where('slug', '!=', 'acceso-justicia'))
                            ->orWhere(function ($query) {
                                $query
                                    ->whereHas('organization', fn ($organization) => $organization->where('slug', 'acceso-justicia'))
                                    ->where('source', 'x')
                                    ->where('excerpt', 'like', '%#AlertaLegal%');
                            });
                    })
                    ->latest('published_at')
                    ->limit(30),
            ])
            ->orderBy('position')
            ->get();

        if ($items->isEmpty()) {
            return collect($this->configOrganizations())->map(fn (array $item) => $this->fallbackOrganization($item));
        }

        return $items->map(fn (Organization $organization) => [
            'slug' => $organization->slug,
            'name' => $organization->name,
            'username' => $organization->x_username,
            'website_url' => $organization->website_url,
            'logo' => asset($organization->logo_path),
            'logo_x' => asset($organization->x_logo_path ?: $organization->logo_path),
            'color' => $organization->color,
            'posts' => $organization->publications->map(fn ($post) => $this->normalizeStoredPost($post)),
        ]);
    }

    /** @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    private function fallbackOrganization(array $item): array
    {
        return ['slug' => $item['slug'], 'name' => $item['name'], 'username' => $item['x_username'], 'website_url' => $item['website_url'] ?? null, 'logo' => asset($item['logo_path']), 'logo_x' => asset($item['x_logo_path']), 'color' => $item['color'], 'posts' => collect()];
    }

    /** @param array<string, mixed>|null $organization
     * @return Collection<int, array<string, mixed>>
     */
    private function posts(?array $organization): Collection
    {
        return collect($organization['posts'] ?? []);
    }

    /** @return Collection<int, array<string, mixed>> */
    private function panoramaPosts(Collection $posts): Collection
    {
        return $posts->map(function (array $post): array {
            $publishedAt = filled($post['created_at'] ?? null)
                ? Carbon::parse($post['created_at'])
                    ->locale(app()->getLocale())
                    ->translatedFormat('d M Y')
                : '';

            return [
                'publication_id' => $post['publication_id'] ?? null,
                'title' => Str::limit(trim($post['text'] ?? ''), 115),
                'date' => $publishedAt,
                'image' => $post['image'] ?? null,
                'url' => $post['url'] ?? '#',
            ];
        })->values();
    }

    /** @return array<string, mixed> */
    private function normalizeStoredPost(Publication $post): array
    {
        return ['id' => $post->external_id, 'publication_id' => $post->id, 'text' => $post->excerpt ?: $post->title, 'created_at' => $post->published_at?->toIso8601String(), 'likes' => $post->likes, 'retweets' => $post->shares, 'image' => $post->image_url, 'url' => $post->url, 'category' => $post->category];
    }

    private function lastSync(): ?string
    {
        if (! Schema::hasTable('dashboard_sync_runs')) {
            return null;
        }

        $value = DashboardSyncRun::query()->where('status', 'completed')->latest('finished_at')->value('finished_at');

        return filled($value) ? Carbon::parse($value, 'UTC')->toIso8601String() : null;
    }

    /** @return array<int, array<string, mixed>> */
    private function configOrganizations(): array
    {
        $items = config('dashboard.organizations');

        return is_array($items) ? array_values(array_filter($items, 'is_array')) : [];
    }

    /** @return array<int, array<string, mixed>> */
    private function configStats(): array
    {
        $items = config('dashboard.stats');

        return is_array($items) ? array_values(array_filter($items, 'is_array')) : [];
    }
}
