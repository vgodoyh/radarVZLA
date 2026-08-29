<?php

namespace Tests\Feature\Analytics;

use App\Livewire\Admin\Analytics\AccesoJusticiaAlertRanking;
use App\Models\AnalyticsContentClick;
use App\Models\AnalyticsNavigationClick;
use App\Models\AnalyticsPageView;
use App\Models\DashboardSyncRun;
use App\Models\Organization;
use App\Models\Publication;
use App\Services\Analytics\OrganizationAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class AccessJusticeAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_refreshes_within_thirty_minutes_do_not_duplicate_page_view(): void
    {
        Carbon::setTestNow('2026-08-22 10:00:00');

        $this->get(route('dashboard.public'))->assertOk();
        $this->get(route('dashboard.public'))->assertOk();

        $this->assertDatabaseCount('analytics_page_views', 1);
        $view = AnalyticsPageView::firstOrFail();
        $this->assertSame('pulso_vzla', $view->organization);
        $this->assertSame('home', $view->page);
        $this->assertNotSame(session()->getId(), $view->session_id);
        $this->assertSame(64, strlen((string) $view->session_id));
        $this->assertSame(64, strlen((string) $view->ip_hash));
    }

    public function test_visit_after_thirty_minutes_is_recorded_again(): void
    {
        Carbon::setTestNow('2026-08-22 10:00:00');
        $this->get(route('dashboard.public'))->assertOk();

        Carbon::setTestNow('2026-08-22 10:31:00');
        $this->get(route('dashboard.public'))->assertOk();

        $this->assertDatabaseCount('analytics_page_views', 2);
    }

    public function test_public_alert_links_track_the_correct_source_and_page_visits(): void
    {
        $publication = $this->legalAlert();

        $this->get(route('dashboard.public'))
            ->assertOk()
            ->assertSee(route('analytics.content.redirect', [$publication, 'home']));
        $this->get(route('organizations.acceso-justicia'))
            ->assertOk()
            ->assertSee(route('analytics.content.redirect', [$publication, 'organization']));

        $this->assertDatabaseHas('analytics_page_views', [
            'organization' => 'pulso_vzla',
            'page' => 'home',
        ]);
        $this->assertDatabaseHas('analytics_page_views', [
            'organization' => 'acceso_justicia',
            'page' => 'acceso-justicia',
            'source' => 'direct',
        ]);
    }

    public function test_direct_organization_visit_stores_only_logical_path_without_navigation_click(): void
    {
        $this->organization('acceso-justicia');

        $this->get('/acceso-justicia')->assertOk();

        $this->assertDatabaseHas('analytics_page_views', [
            'organization' => 'acceso_justicia',
            'page' => 'acceso-justicia',
        ]);
        $this->assertDatabaseMissing('analytics_page_views', [
            'page' => 'http://radar-vzla.test/acceso-justicia',
        ]);
        $this->assertFalse(AnalyticsPageView::query()->get()->contains(
            fn (AnalyticsPageView $view) => str_contains($view->page, '://')
                || str_contains($view->page, 'radar-vzla.test'),
        ));
        $this->assertDatabaseCount('analytics_navigation_clicks', 0);
    }

    public function test_home_navigation_click_is_recorded_then_panel_visit_is_tracked(): void
    {
        $this->organization('acceso-justicia');

        $trackingUrl = route('analytics.navigation.redirect', [
            'organization' => 'acceso-justicia',
            'source' => 'home',
        ]);

        $this->get(route('dashboard.public'))
            ->assertOk()
            ->assertSee($trackingUrl);
        $this->get($trackingUrl)
            ->assertRedirect(route('organizations.acceso-justicia'));

        $this->assertDatabaseHas('analytics_navigation_clicks', [
            'organization' => 'acceso_justicia',
            'target' => 'acceso-justicia',
            'source' => 'home',
        ]);

        $this->get(route('organizations.acceso-justicia'))->assertOk();

        $this->assertDatabaseHas('analytics_page_views', [
            'organization' => 'acceso_justicia',
            'page' => 'acceso-justicia',
            'source' => 'home',
        ]);
    }

    public function test_external_referer_is_classified_as_external_origin(): void
    {
        $this->organization('acceso-justicia');

        $this->withHeader('Referer', 'https://www.google.com/search?q=acceso')->get('/acceso-justicia')
            ->assertOk();

        $this->assertDatabaseHas('analytics_page_views', [
            'organization' => 'acceso_justicia',
            'page' => 'acceso-justicia',
            'source' => 'external',
        ]);
    }

    public function test_deduplication_is_scoped_to_the_same_origin(): void
    {
        $this->organization('acceso-justicia');

        $this->get('/acceso-justicia')->assertOk();
        $trackingUrl = route('analytics.navigation.redirect', [
            'organization' => 'acceso-justicia',
            'source' => 'home',
        ]);
        $this->get($trackingUrl)->assertRedirect(route('organizations.acceso-justicia'));
        $this->get(route('organizations.acceso-justicia'))->assertOk();

        $this->assertDatabaseCount('analytics_page_views', 2);
        $this->assertDatabaseHas('analytics_page_views', [
            'organization' => 'acceso_justicia',
            'page' => 'acceso-justicia',
            'source' => 'direct',
        ]);
        $this->assertDatabaseHas('analytics_page_views', [
            'organization' => 'acceso_justicia',
            'page' => 'acceso-justicia',
            'source' => 'home',
        ]);
    }

    public function test_refreshes_from_the_same_panel_origin_remain_deduplicated(): void
    {
        $this->organization('acceso-justicia');

        $this->get('/acceso-justicia')->assertOk();
        $this->get('/acceso-justicia')->assertOk();

        $this->assertDatabaseCount('analytics_page_views', 1);
        $this->assertDatabaseHas('analytics_page_views', [
            'organization' => 'acceso_justicia',
            'page' => 'acceso-justicia',
            'source' => 'direct',
        ]);
    }

    public function test_panel_origin_returns_only_explicitly_classified_real_sources(): void
    {
        $this->organization('acceso-justicia');

        foreach (['home', 'direct', 'external'] as $source) {
            AnalyticsPageView::create($this->pageView('acceso_justicia', 'acceso-justicia') + ['source' => $source]);
        }
        AnalyticsPageView::create($this->pageView('acceso_justicia', 'acceso-justicia'));

        $origin = app(OrganizationAnalyticsService::class)->dashboard('acceso_justicia')['panelOrigin'];

        $this->assertSame(['pulso' => 0, 'direct' => 4, 'total' => 4], $origin);
        $this->assertSame(4, app(OrganizationAnalyticsService::class)->dashboard('acceso_justicia')['summary']['organization_views']);
    }

    public function test_panel_origin_total_matches_panel_visit_kpi_for_same_period(): void
    {
        Carbon::setTestNow('2026-08-22 13:15:00');
        $this->organization('acceso-justicia');

        foreach (range(1, 5) as $index) {
            AnalyticsPageView::create($this->pageView('acceso_justicia', 'acceso-justicia'));
        }

        foreach (range(1, 2) as $index) {
            AnalyticsNavigationClick::create([
                'organization' => 'acceso_justicia',
                'target' => 'acceso-justicia',
                'source' => 'home',
                'session_id' => hash('sha256', "panel-home-{$index}"),
            ]);
        }

        $data = app(OrganizationAnalyticsService::class)->dashboard('acceso_justicia');

        $this->assertSame(5, $data['summary']['organization_views']);
        $this->assertSame(['pulso' => 2, 'direct' => 3, 'total' => 5], $data['panelOrigin']);
    }

    public function test_clicks_record_home_and_organization_sources_and_redirect_to_publication_url(): void
    {
        $publication = $this->legalAlert('https://example.org/alerta-real');

        $this->get(route('analytics.content.redirect', [$publication, 'home']))
            ->assertRedirect('https://example.org/alerta-real');
        $this->get(route('analytics.content.redirect', [$publication, 'organization']))
            ->assertRedirect('https://example.org/alerta-real');

        $this->assertDatabaseHas('analytics_content_clicks', [
            'content_id' => $publication->id,
            'organization' => 'acceso_justicia',
            'content_type' => 'alert',
            'source' => 'home',
        ]);
        $this->assertDatabaseHas('analytics_content_clicks', [
            'content_id' => $publication->id,
            'source' => 'organization',
        ]);
        $this->assertDatabaseCount('analytics_content_clicks', 2);
    }

    public function test_invalid_source_is_rejected(): void
    {
        $publication = $this->legalAlert();

        $this->get("/analytics/content/{$publication->id}/invalid")->assertNotFound();
        $this->assertDatabaseCount('analytics_content_clicks', 0);
    }

    public function test_publication_from_another_organization_is_rejected(): void
    {
        $organization = $this->organization('jep');
        $publication = $this->publication($organization, '#AlertaLegal externa');

        $this->get(route('analytics.content.redirect', [$publication, 'home']))->assertNotFound();
        $this->assertDatabaseCount('analytics_content_clicks', 0);
    }

    public function test_non_legal_publication_is_rejected(): void
    {
        $organization = $this->organization('acceso-justicia');
        $publication = $this->publication($organization, 'Publicación sin etiqueta');

        $this->get(route('analytics.content.redirect', [$publication, 'home']))->assertNotFound();
        $this->assertDatabaseCount('analytics_content_clicks', 0);
    }

    public function test_dashboard_aggregates_metrics_and_orders_ranking_by_total_clicks(): void
    {
        Carbon::setTestNow('2026-08-22 12:00:00');
        $first = $this->legalAlert('https://example.org/primera', 'Primera #AlertaLegal');
        $second = $this->legalAlert('https://example.org/segunda', 'Segunda #AlertaLegal');

        AnalyticsPageView::create($this->pageView('pulso_vzla', 'home'));
        AnalyticsPageView::create($this->pageView('pulso_vzla', 'home'));
        AnalyticsPageView::create($this->pageView('acceso_justicia', 'acceso-justicia'));
        AnalyticsNavigationClick::create([
            'organization' => 'acceso_justicia',
            'target' => 'acceso-justicia',
            'source' => 'home',
            'session_id' => hash('sha256', 'navigation'),
        ]);
        $this->clicks($first, 'home', 2);
        $this->clicks($first, 'organization', 1);
        $this->clicks($second, 'organization', 1);

        $data = app(OrganizationAnalyticsService::class)->dashboard('acceso_justicia');

        $this->assertSame(2, $data['summary']['portal_views']);
        $this->assertSame(1, $data['summary']['organization_views']);
        $this->assertSame(1, $data['summary']['home_navigation_clicks']);
        $this->assertSame(4, $data['summary']['alert_clicks']);
        $this->assertSame(2, $data['summary']['home_clicks']);
        $this->assertSame(2, $data['summary']['organization_clicks']);
        $ranking = app(OrganizationAnalyticsService::class)
            ->alertRanking('acceso_justicia')
            ->items();
        $this->assertSame($first->id, $ranking[0]['publication_id']);
        $this->assertSame(3, $ranking[0]['total_clicks']);
        $this->assertCount(30, $ranking[0]['tendency']);
        $this->assertSame(3, array_sum($ranking[0]['tendency']));
        $this->assertSame($second->id, $ranking[1]['publication_id']);
        $this->assertCount(30, $ranking[1]['tendency']);
        $this->assertSame(1, array_sum($ranking[1]['tendency']));
        $this->assertCount(30, $data['chart']['labels']);
    }

    public function test_alert_ranking_uses_livewire_pagination_and_keeps_organization_isolated(): void
    {
        Carbon::setTestNow('2026-08-22 12:00:00');
        $organization = $this->organization('acceso-justicia');
        $otherOrganization = $this->organization('otra-organizacion');

        foreach (range(1, 7) as $index) {
            $this->clicks(
                $this->publication($organization, "Alerta {$index} #AlertaLegal", "https://example.org/alerta-{$index}"),
                'home',
                1,
            );
        }

        $this->clicks(
            $this->publication($otherOrganization, 'Alerta externa #AlertaLegal', 'https://example.org/externa'),
            'home',
            5,
            'otra_organizacion',
        );

        $component = Livewire::test(AccesoJusticiaAlertRanking::class)
            ->assertSee('Alerta 1 #AlertaLegal')
            ->assertSee('Alerta 5 #AlertaLegal')
            ->assertDontSee('Alerta 6 #AlertaLegal')
            ->assertDontSee('Alerta externa #AlertaLegal');

        $component
            ->call('gotoPage', 2, 'alertsPage')
            ->assertNoRedirect()
            ->assertSee('Alerta 6 #AlertaLegal')
            ->assertSee('Alerta 7 #AlertaLegal')
            ->assertDontSee('Alerta 1 #AlertaLegal')
            ->assertDontSee('Alerta externa #AlertaLegal');
    }

    public function test_sync_status_uses_real_alert_count_and_organization_timestamp(): void
    {
        Carbon::setTestNow('2026-08-22 13:15:00');
        $organization = $this->organization('acceso-justicia');
        $organization->update(['last_synced_at' => now()->subHour()]);

        foreach (range(1, 25) as $index) {
            $this->publication(
                $organization,
                "Alerta {$index} #AlertaLegal",
                "https://example.org/alerta-{$index}",
            );
        }

        DashboardSyncRun::create([
            'status' => 'completed',
            'started_at' => now()->subHours(2),
            'finished_at' => now()->subHour(),
        ]);

        $sync = app(OrganizationAnalyticsService::class)
            ->dashboard('acceso_justicia')['sync'];

        $this->assertSame(25, $sync['alerts_count']);
        $this->assertTrue(now()->subHour()->equalTo($sync['last_synced_at']));
        $this->assertSame('success', $sync['status']);
    }

    public function test_sync_status_uses_latest_real_organization_run(): void
    {
        Carbon::setTestNow('2026-08-22 13:15:00');
        $organization = $this->organization('acceso-justicia');
        $organization->update(['last_synced_at' => now()->subHours(3)]);

        DashboardSyncRun::create([
            'organization' => 'acceso_justicia',
            'process' => 'publications',
            'status' => 'success',
            'started_at' => now()->subHours(2),
            'finished_at' => now()->subHour(),
        ]);

        $sync = app(OrganizationAnalyticsService::class)
            ->dashboard('acceso_justicia')['sync'];

        $this->assertSame('success', $sync['status']);
        $this->assertTrue(now()->subHour()->equalTo($sync['last_synced_at']));
        $this->assertNull($sync['message']);
    }

    private function legalAlert(
        string $url = 'https://example.org/alerta',
        string $excerpt = 'Contenido #AlertaLegal',
    ): Publication {
        return $this->publication($this->organization('acceso-justicia'), $excerpt, $url);
    }

    private function organization(string $slug): Organization
    {
        return Organization::firstOrCreate(
            ['slug' => $slug],
            ['name' => $slug, 'position' => 1, 'active' => true],
        );
    }

    private function publication(
        Organization $organization,
        string $excerpt,
        string $url = 'https://example.org/alerta',
    ): Publication {
        return Publication::create([
            'organization_id' => $organization->id,
            'source' => 'x',
            'external_id' => str()->uuid()->toString(),
            'excerpt' => $excerpt,
            'url' => $url,
        ]);
    }

    /** @return array<string, mixed> */
    private function pageView(string $organization, string $page): array
    {
        return [
            'organization' => $organization,
            'page' => $page,
            'session_id' => hash('sha256', str()->random()),
            'ip_hash' => hash('sha256', str()->random()),
        ];
    }

    private function clicks(Publication $publication, string $source, int $count, string $trackingOrganization = 'acceso_justicia'): void
    {
        foreach (range(1, $count) as $index) {
            AnalyticsContentClick::create([
                'organization' => $trackingOrganization,
                'content_type' => 'alert',
                'content_id' => $publication->id,
                'source' => $source,
                'session_id' => hash('sha256', "{$publication->id}-{$source}-{$index}"),
            ]);
        }
    }
}
