<?php

namespace Tests\Feature\Admin;

use App\Models\AnalyticsContentClick;
use App\Models\AnalyticsNavigationClick;
use App\Models\AnalyticsPageView;
use App\Services\Analytics\OrganizationAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OvfnAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_ovfn_dashboard_returns_real_summary_by_type_and_daily_zero_filled_series(): void
    {
        $today = today();
        AnalyticsNavigationClick::create(['organization' => 'ovfn', 'target' => 'fake-news', 'source' => 'home']);
        AnalyticsPageView::create(['organization' => 'pulso_vzla', 'page' => 'home', 'source' => 'direct', 'created_at' => $today]);
        AnalyticsPageView::create(['organization' => 'ovfn', 'page' => 'fake-news', 'source' => 'home', 'created_at' => $today]);
        foreach (['x_post', 'noti_fake', 'analysis'] as $type) {
            AnalyticsContentClick::create(['organization' => 'ovfn', 'content_type' => $type, 'content_id' => 1, 'source' => 'organization', 'created_at' => $today]);
        }

        $data = app(OrganizationAnalyticsService::class)->dashboard('ovfn', 3);

        $this->assertSame(1, $data['summary']['home_navigation_clicks']);
        $this->assertSame(1, $data['summary']['portal_views']);
        $this->assertSame(1, $data['summary']['organization_views']);
        $this->assertSame(['x_post' => 1, 'noti_fake' => 1, 'analysis' => 1, 'total' => 3], $data['contentClicks']);
        $this->assertCount(3, $data['contentClicksChart']['labels']);
        $this->assertSame(3, count($data['contentClicksChart']['analysis']));
        $this->assertSame(1, $data['panelOrigin']['pulso']);
        $this->assertSame(1, $data['panelOrigin']['total']);
    }

    public function test_ovfn_origin_direct_access_never_becomes_negative(): void
    {
        AnalyticsNavigationClick::create(['organization' => 'ovfn', 'target' => 'fake-news', 'source' => 'home']);
        AnalyticsNavigationClick::create(['organization' => 'ovfn', 'target' => 'fake-news', 'source' => 'home']);
        AnalyticsPageView::create(['organization' => 'ovfn', 'page' => 'fake-news', 'source' => 'home']);

        $origin = app(OrganizationAnalyticsService::class)->dashboard('ovfn')['panelOrigin'];

        $this->assertSame(0, $origin['direct']);
        $this->assertSame(1, $origin['total']);
    }
}
