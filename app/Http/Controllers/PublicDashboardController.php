<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Services\DashboardQueryService;
use App\Services\FakeNewsVenezuelaService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicDashboardController extends Controller
{
    public function index(DashboardQueryService $dashboard): View
    {
        return $this->dashboardView($dashboard, 'dashboard.index');
    }

    public function jep(DashboardQueryService $dashboard): View
    {
        return $this->organizationView($dashboard, 'dashboard.organizations.jep', 'jep');
    }

    public function accesoJusticia(Request $request, DashboardQueryService $dashboard): View
    {
        $data = $dashboard->get();
        $organization = $data['organizations']->firstWhere('slug', 'acceso-justicia');
        $search = Str::limit(trim((string) $request->query('q', '')), 100, '');
        $organizationLastSync = null;

        abort_unless($organization, 404);

        if (Schema::hasTable('organizations') && Schema::hasTable('publications')) {
            $organizationModel = Organization::query()
                ->where('slug', 'acceso-justicia')
                ->firstOrFail();
            $organizationLastSync = $organizationModel->last_synced_at?->toIso8601String();

            $posts = $dashboard->accesoLegalPublicationsQuery()
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('excerpt', 'like', "%{$search}%")
                            ->orWhere('title', 'like', "%{$search}%")
                            ->orWhere('category', 'like', "%{$search}%");
                    });
                })
                ->latest('published_at')
                ->paginate(6)
                ->withQueryString()
                ->through(fn ($post) => [
                    'id' => $post->external_id,
                    'publication_id' => $post->id,
                    'text' => $post->excerpt ?: $post->title,
                    'created_at' => $post->published_at?->toIso8601String(),
                    'likes' => $post->likes,
                    'retweets' => $post->shares,
                    'image' => $post->image_url,
                    'url' => $post->url,
                ]);
        } else {
            $posts = new LengthAwarePaginator([], 0, 6, 1, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);
        }

        return view('dashboard.organizations.acceso', [
            ...$data,
            'organization' => $organization,
            'posts' => $posts,
            'search' => $search,
            'lastSync' => $organizationLastSync,
        ]);
    }

    public function fakeNews(
        DashboardQueryService $dashboard,
        FakeNewsVenezuelaService $fakeNews
    ): View {
        $data = $dashboard->get();
        $organization = $data['organizations']->firstWhere('slug', 'fake-news');

        abort_unless($organization, 404);

        $currentVerificationTotal = $data['ovfnVerificationTotal'];

        $postsFakeNewsWeb = collect($fakeNews->getLatestPosts())
            ->map(fn ($posts, string $type) => collect($posts)->map(function (array $post) use ($type): array {
                $url = (string) ($post['url'] ?? '#');
                if (! Str::startsWith($url, ['https://', 'http://'])) {
                    return $post;
                }
                $post['url'] = URL::signedRoute('analytics.ovfn.content.redirect', [
                    'contentType' => $type === 'en_profundidad' ? 'analysis' : 'noti_fake',
                    'contentId' => abs(crc32($url)),
                    'source' => 'organization',
                    'url' => $url,
                ]);
                return $post;
            }));

        return view('dashboard.organizations.fake-news', [
            ...$data,
            'organization' => $organization,
            'postsFakeNewsWeb' => $postsFakeNewsWeb,
            'currentVerificationTotal' => $currentVerificationTotal,
        ]);
    }

    public function universidades(DashboardQueryService $dashboard): View
    {
        return $this->organizationView($dashboard, 'dashboard.organizations.universidades', 'universidades');
    }

    private function dashboardView(DashboardQueryService $dashboard, string $view): View
    {
        return view($view, $dashboard->get());
    }

    private function organizationView(
        DashboardQueryService $dashboard,
        string $view,
        string $slug,
        ?string $postsKey = null
    ): View {
        $data = $dashboard->get();
        $organization = $data['organizations']->firstWhere('slug', $slug);

        abort_unless($organization, 404);

        return view($view, [
            ...$data,
            'organization' => $organization,
            'posts' => $postsKey
                ? collect($data[$postsKey] ?? [])
                : collect($organization['posts'] ?? []),
        ]);
    }
}
