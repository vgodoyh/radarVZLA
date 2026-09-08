<?php

namespace Tests\Feature\Admin;

use App\Models\JepMetricSnapshot;
use App\Models\AnalyticsContentClick;
use App\Models\AnalyticsNavigationClick;
use App\Models\AnalyticsPageView;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\JepMetricSnapshotSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class JepMetricSnapshotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_seed_is_idempotent_with_initial_values_and_children(): void
    {
        $this->organization();
        $this->seed(JepMetricSnapshotSeeder::class);
        $this->seed(JepMetricSnapshotSeeder::class);

        $snapshot = JepMetricSnapshot::query()->current()->with(['vulnerableGroups', 'detentionCenters', 'deathCustodyDistribution'])->firstOrFail();
        $this->assertSame(452, $snapshot->total_political_prisoners);
        $this->assertSame(44, $snapshot->women);
        $this->assertSame(38, $snapshot->seriously_ill);
        $this->assertSame(44, $snapshot->foreign_or_dual_nationality);
        $this->assertSame(95, $snapshot->releases);
        $this->assertSame([2, 10, 222], $snapshot->vulnerableGroups->pluck('value')->all());
        $this->assertSame([109, 76, 46], $snapshot->detentionCenters->pluck('value')->all());
        $this->assertSame([2, 7, 1], $snapshot->deathCustodyDistribution->pluck('value')->all());
        $this->assertSame(10, $snapshot->deathCustodyDistribution->sum('value'));
        $this->assertStringContainsString('El registro de detenciones abarca aquellas ocurridas durante el período', $snapshot->detentions_methodology_note);
        $this->assertCount(1, JepMetricSnapshot::all());
    }

    public function test_public_page_and_index_use_current_jep_total(): void
    {
        $this->organization();
        $this->seed(JepMetricSnapshotSeeder::class);

        $this->get(route('organizations.jep'))->assertOk()->assertSee('El registro de detenciones abarca aquellas ocurridas durante el período');
        $this->get(route('dashboard.public'))->assertOk()->assertSee('452')->assertSee('44')->assertSee('38')->assertSee('95');
    }

    public function test_jep_admin_uses_real_analytics_kpis_without_touching_editorial_snapshots(): void
    {
        $this->organization();
        $this->seed(JepMetricSnapshotSeeder::class);
        $user = $this->userWithPermissions(['view jep dashboard', 'edit jep metrics']);

        AnalyticsPageView::insert(collect(range(1, 3))->map(fn () => ['organization' => 'pulso_vzla', 'page' => 'home', 'created_at' => now(), 'updated_at' => now()])->all());
        AnalyticsPageView::insert(collect(range(1, 2))->map(fn () => ['organization' => 'jep', 'page' => 'justicia-encuentro-perdon', 'created_at' => now(), 'updated_at' => now()])->all());
        AnalyticsNavigationClick::insert(collect(range(1, 4))->map(fn () => ['organization' => 'jep', 'target' => 'justicia-encuentro-perdon', 'source' => 'home', 'created_at' => now(), 'updated_at' => now()])->all());
        AnalyticsContentClick::insert(collect(range(1, 2))->map(fn () => ['organization' => 'jep', 'content_type' => 'instagram', 'content_id' => 1, 'source' => 'organization', 'created_at' => now(), 'updated_at' => now()])->all());
        AnalyticsContentClick::create(['organization' => 'jep', 'content_type' => 'x_post', 'content_id' => 2, 'source' => 'organization']);

        $this->actingAs($user)->get(route('admin.jep.index'))
            ->assertOk()
            ->assertSee('Clics desde Pulso')
            ->assertSee('Visitas al portal Pulso Venezuela')
            ->assertSee('Visitas al panel JEP')
            ->assertSee('Clics en contenidos')
            ->assertSee('Instagram: 2 · X Post: 1');

        $this->assertCount(1, JepMetricSnapshot::all());
    }

    public function test_public_index_and_jep_page_follow_the_new_current_version_after_update(): void
    {
        $this->organization();
        $this->seed(JepMetricSnapshotSeeder::class);
        $user = $this->userWithPermissions(['view jep dashboard', 'edit jep metrics']);
        $current = JepMetricSnapshot::query()->current()->firstOrFail();
        $payload = $this->payload($current->toArray());
        $payload['total_political_prisoners'] = 460;
        $payload['women'] = 50;
        $payload['seriously_ill'] = 40;
        $payload['foreign_or_dual_nationality'] = 46;
        $payload['releases'] = 100;

        $this->actingAs($user)->patch(route('admin.jep.metrics.update'), $payload)->assertRedirect();

        $this->get(route('organizations.jep'))->assertOk()->assertSee('460')->assertSee('50')->assertSee('40')->assertSee('46')->assertSee('100');
        $this->get(route('dashboard.public'))->assertOk()->assertSee('460')->assertSee('50')->assertSee('40')->assertSee('46')->assertSee('100');
    }

    public function test_update_versions_snapshot_and_preserves_children(): void
    {
        $organization = $this->organization();
        $this->seed(JepMetricSnapshotSeeder::class);
        $user = $this->userWithPermissions(['view jep dashboard', 'edit jep metrics']);
        $current = JepMetricSnapshot::query()->current()->firstOrFail();

        Carbon::setTestNow('2026-09-06 12:00:00');
        $payload = $this->payload($current->toArray());
        $payload['total_political_prisoners'] = 460;

        $this->actingAs($user)->patch(route('admin.jep.metrics.update'), $payload)
            ->assertRedirect(route('admin.jep.index'))
            ->assertSessionHas('jep_metrics_success');

        $current->refresh();
        $new = JepMetricSnapshot::query()->where('organization_id', $organization->id)->current()->with(['vulnerableGroups', 'detentionCenters'])->firstOrFail();
        $this->assertSame(460, $new->total_political_prisoners);
        $this->assertNotNull($current->valid_until);
        $this->assertTrue($current->valid_until->equalTo($new->valid_from));
        $this->assertSame([2, 10, 222], $current->vulnerableGroups()->orderBy('sort_order')->pluck('value')->all());
        $this->assertSame([109, 76, 46], $current->detentionCenters()->orderBy('sort_order')->pluck('value')->all());
        $this->assertNull($new->valid_until);
        Carbon::setTestNow();
    }

    public function test_same_values_do_not_create_new_version_and_view_only_user_cannot_update(): void
    {
        $this->organization();
        $this->seed(JepMetricSnapshotSeeder::class);
        $current = JepMetricSnapshot::query()->current()->firstOrFail();
        $payload = $this->payload($current->toArray());
        $viewOnly = $this->userWithPermissions(['view jep dashboard']);

        $this->actingAs($viewOnly)->patch(route('admin.jep.metrics.update'), $payload)->assertForbidden();
        $editor = $this->userWithPermissions(['edit jep metrics']);
        $this->actingAs($editor)->patch(route('admin.jep.metrics.update'), $payload)->assertSessionHas('jep_metrics_info');
        $this->assertCount(1, JepMetricSnapshot::all());
    }

    public function test_editorial_trends_are_nullable_formatted_and_versioned(): void
    {
        $this->organization();
        $this->seed(JepMetricSnapshotSeeder::class);
        $user = $this->userWithPermissions(['view jep dashboard', 'edit jep metrics']);
        $current = JepMetricSnapshot::query()->current()->firstOrFail();
        $payload = $this->payload($current->toArray()) + [
            'total_political_prisoners_trend' => 5.2,
            'women_trend' => -3.1,
            'seriously_ill_trend' => 4.5,
            'foreign_or_dual_nationality_trend' => 2,
            'releases_trend' => 12.9,
        ];

        $this->actingAs($user)->patch(route('admin.jep.metrics.update'), $payload)->assertRedirect();
        $old = JepMetricSnapshot::query()->findOrFail($current->id);
        $new = JepMetricSnapshot::query()->current()->firstOrFail();
        $this->assertNull($old->total_political_prisoners_trend);
        $this->assertSame('5.20', $new->total_political_prisoners_trend);
        $this->get(route('dashboard.public'))->assertSee('+5.2%')->assertSee('-3.1%')->assertSee('+4.5%')->assertSee('+2.0%')->assertSee('+12.9%');

        $nextPayload = $this->payload($new->toArray());
        $nextPayload['women_trend'] = null;
        $nextPayload['seriously_ill_trend'] = 0;
        $this->actingAs($user)->patch(route('admin.jep.metrics.update'), $nextPayload)->assertRedirect();
        $current = JepMetricSnapshot::query()->current()->firstOrFail();
        $new->refresh();
        $this->assertNull($current->women_trend);
        $this->assertSame('0.00', $current->seriously_ill_trend);
        $this->get(route('dashboard.public'))->assertSee('0%')->assertDontSee('-3.1%');
        $this->assertSame('5.20', $new->total_political_prisoners_trend);
        $this->assertNotNull($new->valid_until);
        $this->assertNotNull($old->valid_until);
    }

    public function test_changing_only_death_distribution_versions_and_preserves_previous_values(): void
    {
        $organization = $this->organization();
        $this->seed(JepMetricSnapshotSeeder::class);
        $user = $this->userWithPermissions(['view jep dashboard', 'edit jep metrics']);
        $old = JepMetricSnapshot::query()->current()->with('deathCustodyDistribution')->firstOrFail();
        $payload = $this->payload($old->toArray());
        $payload['death_custody_distribution'][0]['value'] = 3;

        $this->actingAs($user)->patch(route('admin.jep.metrics.update'), $payload)->assertRedirect();

        $old->refresh();
        $new = JepMetricSnapshot::query()->where('organization_id', $organization->id)->current()->with('deathCustodyDistribution')->firstOrFail();
        $this->assertSame([2, 7, 1], $old->deathCustodyDistribution()->orderBy('sort_order')->pluck('value')->all());
        $this->assertSame([3, 7, 1], $new->deathCustodyDistribution->pluck('value')->all());
        $this->assertSame(11, $new->deathCustodyDistribution->sum('value'));
        $this->assertNotNull($old->valid_until);
        $this->get(route('organizations.jep'))
            ->assertOk()
            ->assertSee('jepDeathsCustodyChart')
            ->assertSee('>11<', false)
            ->assertSee('>3<', false);
    }

    public function test_public_vulnerable_groups_donut_uses_current_values(): void
    {
        $this->organization();
        $this->seed(JepMetricSnapshotSeeder::class);
        $user = $this->userWithPermissions(['edit jep metrics']);
        $current = JepMetricSnapshot::query()->current()->firstOrFail();
        $payload = $this->payload($current->toArray());
        $payload['groups'][0]['value'] = 20;

        $this->actingAs($user)->patch(route('admin.jep.metrics.update'), $payload)->assertRedirect();

        $current = JepMetricSnapshot::query()->current()->firstOrFail();
        $payload = $this->payload($current->toArray());
        $payload['groups'][0]['value'] = 30;

        $this->actingAs($user)->patch(route('admin.jep.metrics.update'), $payload)->assertRedirect();

        $this->get(route('organizations.jep'))
            ->assertOk()
            ->assertSee('jepVulnerableGroupsChart')
            ->assertSee('>262<', false)
            ->assertSee('>30<', false);
    }

    public function test_detention_methodology_is_fixed_publicly_and_not_editable_from_admin(): void
    {
        $organization = $this->organization();
        $this->seed(JepMetricSnapshotSeeder::class);
        $user = $this->userWithPermissions(['view jep dashboard', 'edit jep metrics']);
        $old = JepMetricSnapshot::query()->current()->firstOrFail();
        $payload = $this->payload($old->toArray());
        $payload['detentions_methodology_note'] = 'Texto metodológico actualizado.';

        $this->actingAs($user)->patch(route('admin.jep.metrics.update'), $payload)->assertRedirect();

        $this->assertCount(1, JepMetricSnapshot::all());
        $this->actingAs($user)->get(route('admin.jep.index'))
            ->assertOk()
            ->assertDontSee('Nota metodológica sobre detenciones')
            ->assertDontSee('Actualizar nota metodológica')
            ->assertDontSee('Historial de Nota metodológica');
        $this->get(route('organizations.jep'))
            ->assertSee('Nota metodológica sobre detenciones')
            ->assertSee('El registro de detenciones abarca aquellas ocurridas durante el período o aquellas ocurridas con anterioridad y han sido incorporadas a la base de datos recientemente.');
    }

    public function test_admin_can_fetch_a_single_x_post_without_creating_a_snapshot(): void
    {
        $this->organization();
        $this->seed(JepMetricSnapshotSeeder::class);
        $user = $this->userWithPermissions(['edit jep metrics']);
        Http::fake(['https://twitter-api45.p.rapidapi.com/tweet.php*' => Http::response(['text' => 'Texto completo desde X.'], 200)]);

        $this->actingAs($user)->postJson(route('admin.jep.monthly-alert.fetch-x-post'), [
            'url' => 'https://x.com/JEPVzla/status/1234567890',
        ])->assertOk()->assertJson(['success' => true, 'text' => 'Texto completo desde X.']);

        Http::assertSent(fn ($request) => $request->url() === 'https://twitter-api45.p.rapidapi.com/tweet.php?id=1234567890');
        $this->assertCount(1, JepMetricSnapshot::all());
    }

    public function test_featured_indicator_image_is_versioned_and_publicly_rendered(): void
    {
        $organization = $this->organization();
        $this->seed(JepMetricSnapshotSeeder::class);
        Storage::fake('public');
        $user = $this->userWithPermissions(['edit jep metrics']);
        $old = JepMetricSnapshot::query()->current()->firstOrFail();
        $payload = $this->payload($old->toArray());
        $payload['featured_indicator_title'] = 'Indicador de prueba';
        $payload['featured_indicator_image'] = UploadedFile::fake()->image('indicador.jpg');

        $this->actingAs($user)->patch(route('admin.jep.metrics.update'), $payload)->assertRedirect();

        $old->refresh();
        $new = JepMetricSnapshot::query()->where('organization_id', $organization->id)->current()->firstOrFail();
        $this->assertNotNull($new->featured_indicator_image_path);
        $this->assertNotSame($old->featured_indicator_image_path, $new->featured_indicator_image_path);
        Storage::disk('public')->assertExists($new->featured_indicator_image_path);
        $this->get(route('organizations.jep'))->assertOk()->assertSee(Storage::url($new->featured_indicator_image_path));

        $secondPayload = $this->payload($new->toArray());
        $secondPayload['featured_indicator_text'] = 'Texto actualizado sin reemplazar imagen.';
        $this->actingAs($user)->patch(route('admin.jep.metrics.update'), $secondPayload)->assertRedirect();
        $current = JepMetricSnapshot::query()->current()->firstOrFail();
        $this->assertSame($new->featured_indicator_image_path, $current->featured_indicator_image_path);
        $this->assertNotNull($new->fresh()->valid_until);
    }

    public function test_featured_indicator_rejects_unsupported_image_formats(): void
    {
        $this->organization();
        $this->seed(JepMetricSnapshotSeeder::class);
        $user = $this->userWithPermissions(['edit jep metrics']);
        $current = JepMetricSnapshot::query()->current()->firstOrFail();
        $payload = $this->payload($current->toArray());
        $payload['featured_indicator_image'] = UploadedFile::fake()->create('indicator.pdf', 10, 'application/pdf');

        $this->actingAs($user)->patch(route('admin.jep.metrics.update'), $payload)
            ->assertSessionHasErrors('featured_indicator_image');
        $this->assertCount(1, JepMetricSnapshot::all());
    }

    public function test_modular_updates_preserve_unrelated_editorial_blocks_and_admin_is_split(): void
    {
        $this->organization();
        $this->seed(JepMetricSnapshotSeeder::class);
        $user = $this->userWithPermissions(['view jep dashboard', 'edit jep metrics']);
        $current = JepMetricSnapshot::query()->current()->firstOrFail();

        $this->actingAs($user)->get(route('admin.jep.index'))
            ->assertOk()
            ->assertSee('Actualizar cifras principales')
            ->assertSee('Actualizar indicador destacado')
            ->assertSee('Actualizar fallecidos en custodia')
            ->assertDontSee('Actualizar distribución')
            ->assertDontSee('Actualizar nota metodológica')
            ->assertDontSee('Nota metodológica sobre detenciones')
            ->assertDontSee('Historial de Nota metodológica')
            ->assertSee('Actualizar alerta del mes')
            ->assertSee('Actualizar indicadores')
            ->assertSee('Actualizar grupos vulnerables')
            ->assertSee('Actualizar centros de detención')
            ->assertSee('releases_period_start_date')
            ->assertSee('releases_period_end_date')
            ->assertSee('deaths_period_start')
            ->assertSee('deaths_period_end')
            ->assertSee('type="date"', false)
            ->assertDontSee('name="deaths_in_custody"')
            ->assertDontSee('releases_period_start_month')
            ->assertDontSee('deaths_period_start_month')
            ->assertDontSee('Guardar cifras de JEP');

        $this->actingAs($user)->patch(route('admin.jep.main-metrics.update'), [
            'total_political_prisoners' => 460,
            'total_political_prisoners_trend' => null,
            'women' => $current->women, 'women_trend' => null,
            'seriously_ill' => $current->seriously_ill, 'seriously_ill_trend' => null,
            'foreign_or_dual_nationality' => $current->foreign_or_dual_nationality, 'foreign_or_dual_nationality_trend' => null,
            'releases' => $current->releases, 'releases_trend' => null,
            'releases_period_start_month' => $current->releases_period_start_month, 'releases_period_start_day' => $current->releases_period_start_day, 'releases_period_start_year' => $current->releases_period_start_year,
            'releases_period_end_month' => $current->releases_period_end_month, 'releases_period_end_day' => $current->releases_period_end_day, 'releases_period_end_year' => $current->releases_period_end_year,
        ])->assertRedirect();

        $updated = JepMetricSnapshot::query()->current()->firstOrFail();
        $this->assertSame(460, $updated->total_political_prisoners);
        $this->assertSame($current->monthly_alert_excerpt, $updated->monthly_alert_excerpt);
        $this->assertSame($current->featured_indicator_text, $updated->featured_indicator_text);

        $this->actingAs($user)->patch(route('admin.jep.monthly-alert.update'), [
            'monthly_alert_title' => 'Alerta actualizada',
            'monthly_alert_excerpt' => $updated->monthly_alert_excerpt,
            'monthly_alert_x_url' => $updated->monthly_alert_x_url,
        ])->assertRedirect();

        $alertUpdated = JepMetricSnapshot::query()->current()->firstOrFail();
        $this->assertSame(460, $alertUpdated->total_political_prisoners);
        $this->assertSame('Alerta actualizada', $alertUpdated->monthly_alert_title);
        $this->assertSame($updated->featured_indicator_text, $alertUpdated->featured_indicator_text);
        $this->actingAs($user)->get(route('admin.jep.index'))
            ->assertOk()
            ->assertSee('Historial de Grupos vulnerables')
            ->assertSee('Historial de Centros de detención')
            ->assertSee('Historial de Distribución de fallecidos');
    }

    public function test_main_metrics_groups_trends_and_converts_release_dates_without_schema_changes(): void
    {
        $this->organization();
        $this->seed(JepMetricSnapshotSeeder::class);
        $user = $this->userWithPermissions(['edit jep metrics']);
        $current = JepMetricSnapshot::query()->current()->firstOrFail();

        $this->actingAs($user)->patch(route('admin.jep.main-metrics.update'), [
            'total_political_prisoners' => $current->total_political_prisoners,
            'total_political_prisoners_trend' => $current->total_political_prisoners_trend,
            'women' => 45,
            'women_trend' => 3.2,
            'seriously_ill' => $current->seriously_ill,
            'seriously_ill_trend' => $current->seriously_ill_trend,
            'foreign_or_dual_nationality' => $current->foreign_or_dual_nationality,
            'foreign_or_dual_nationality_trend' => $current->foreign_or_dual_nationality_trend,
            'releases' => $current->releases,
            'releases_trend' => $current->releases_trend,
            'releases_period_start_date' => '2026-08-14',
            'releases_period_end_date' => '2026-08-31',
        ])->assertRedirect();

        $updated = JepMetricSnapshot::query()->current()->firstOrFail();
        $this->assertSame(45, $updated->women);
        $this->assertSame('3.20', (string) $updated->women_trend);
        $this->assertSame(8, $updated->releases_period_start_month);
        $this->assertSame(14, $updated->releases_period_start_day);
        $this->assertSame(2026, $updated->releases_period_start_year);
        $this->assertSame(8, $updated->releases_period_end_month);
        $this->assertSame(31, $updated->releases_period_end_day);
        $this->assertSame(2026, $updated->releases_period_end_year);
    }

    public function test_indicators_group_values_and_maps_month_periods_without_schema_changes(): void
    {
        $this->organization();
        $this->seed(JepMetricSnapshotSeeder::class);
        $user = $this->userWithPermissions(['edit jep metrics']);

        $this->actingAs($user)->patch(route('admin.jep.indicators.update'), [
            'active_retired_officials' => 199,
            'new_detentions' => 8,
            'missing_location' => 7,
            'deaths_period_start' => '2015-03',
            'deaths_period_end' => '2025-07',
        ])->assertRedirect();

        $updated = JepMetricSnapshot::query()->current()->firstOrFail();
        $this->assertSame(199, $updated->active_retired_officials);
        $this->assertSame(27, $updated->deaths_in_custody);
        $this->assertSame(3, $updated->deaths_period_start_month);
        $this->assertSame(2015, $updated->deaths_period_start_year);
        $this->assertSame(7, $updated->deaths_period_end_month);
        $this->assertSame(2025, $updated->deaths_period_end_year);
    }

    public function test_death_custody_module_updates_distribution_and_period_without_changing_indicators(): void
    {
        $organization = $this->organization();
        $this->seed(JepMetricSnapshotSeeder::class);
        $user = $this->userWithPermissions(['edit jep metrics']);
        $old = JepMetricSnapshot::query()->current()->with('deathCustodyDistribution')->firstOrFail();

        $this->actingAs($user)->patch(route('admin.jep.death-custody.update'), [
            'death_custody_distribution' => [
                ['category_key' => 'home_arrest', 'label' => 'Arresto domiciliario', 'value' => 1, 'sort_order' => 1],
                ['category_key' => 'detention_centers', 'label' => 'En centros de reclusión', 'value' => 18, 'sort_order' => 2],
                ['category_key' => 'hospitals', 'label' => 'Hospitales', 'value' => 8, 'sort_order' => 3],
            ],
            'deaths_period_start' => '2015-03-01',
            'deaths_period_end' => '2025-07-31',
        ])->assertRedirect();

        $updated = JepMetricSnapshot::query()->where('organization_id', $organization->id)->current()->with('deathCustodyDistribution')->firstOrFail();
        $this->assertSame([1, 18, 8], $updated->deathCustodyDistribution->pluck('value')->all());
        $this->assertSame(27, $updated->deathCustodyDistribution->sum('value'));
        $this->assertSame($old->active_retired_officials, $updated->active_retired_officials);
        $this->assertSame(3, $updated->deaths_period_start_month);
        $this->assertSame(1, $updated->deaths_period_start_day);
        $this->assertSame(2015, $updated->deaths_period_start_year);
        $this->assertSame(7, $updated->deaths_period_end_month);
        $this->assertSame(31, $updated->deaths_period_end_day);
        $this->assertSame(2025, $updated->deaths_period_end_year);
    }

    public function test_vulnerable_group_names_are_fixed_and_only_values_are_editable(): void
    {
        $organization = $this->organization();
        $this->seed(JepMetricSnapshotSeeder::class);
        $user = $this->userWithPermissions(['view jep dashboard', 'edit jep metrics']);

        $this->actingAs($user)->get(route('admin.jep.index'))
            ->assertOk()
            ->assertSee('Sindicalistas')
            ->assertSee('Organizaciones políticas')
            ->assertSee('Sociedad civil')
            ->assertDontSee('name="groups[0][label]"')
            ->assertDontSee('name="groups[0][group_key]"');

        $this->actingAs($user)->patch(route('admin.jep.vulnerable-groups.update'), [
            'groups' => [
                'sindicalistas' => 2,
                'organizaciones_politicas' => 10,
                'sociedad_civil' => 222,
            ],
        ])->assertRedirect();
        $this->assertCount(1, JepMetricSnapshot::where('organization_id', $organization->id)->get());

        $this->actingAs($user)->patch(route('admin.jep.vulnerable-groups.update'), [
            'groups' => [
                'sindicalistas' => 20,
                'organizaciones_politicas' => 10,
                'sociedad_civil' => 222,
            ],
        ])->assertRedirect();

        $current = JepMetricSnapshot::query()->where('organization_id', $organization->id)->current()->with('vulnerableGroups')->firstOrFail();
        $this->assertSame(['sindicalistas', 'organizaciones_politicas', 'sociedad_civil'], $current->vulnerableGroups->pluck('group_key')->all());
        $this->assertSame([20, 10, 222], $current->vulnerableGroups->pluck('value')->all());
        $this->assertCount(2, JepMetricSnapshot::where('organization_id', $organization->id)->get());
    }

    public function test_death_custody_dates_reject_invalid_and_reversed_ranges(): void
    {
        $this->organization();
        $this->seed(JepMetricSnapshotSeeder::class);
        $user = $this->userWithPermissions(['edit jep metrics']);
        $distribution = [
            ['category_key' => 'home_arrest', 'label' => 'Arresto domiciliario', 'value' => 1, 'sort_order' => 1],
            ['category_key' => 'detention_centers', 'label' => 'En centros de reclusión', 'value' => 18, 'sort_order' => 2],
            ['category_key' => 'hospitals', 'label' => 'Hospitales', 'value' => 8, 'sort_order' => 3],
        ];

        $this->actingAs($user)->patch(route('admin.jep.death-custody.update'), [
            'death_custody_distribution' => $distribution,
            'deaths_period_start' => 'not-a-date',
            'deaths_period_end' => '2025-07-31',
        ])->assertSessionHasErrors('deaths_period_start');

        $this->actingAs($user)->patch(route('admin.jep.death-custody.update'), [
            'death_custody_distribution' => $distribution,
            'deaths_period_start' => '2025-08-01',
            'deaths_period_end' => '2025-07-31',
        ])->assertSessionHasErrors('deaths_period_end');
    }

    public function test_invalid_or_missing_x_posts_are_reported_without_exposing_provider_errors(): void
    {
        $this->organization();
        $this->seed(JepMetricSnapshotSeeder::class);
        $user = $this->userWithPermissions(['edit jep metrics']);
        Http::fake(['https://twitter-api45.p.rapidapi.com/tweet.php*' => Http::response([], 404)]);

        $this->actingAs($user)->postJson(route('admin.jep.monthly-alert.fetch-x-post'), [
            'url' => 'https://x.com/JEPVzla/status/1234567890',
        ])->assertStatus(404)->assertJson(['success' => false, 'message' => 'La publicación no fue encontrada.']);

        $this->actingAs($user)->postJson(route('admin.jep.monthly-alert.fetch-x-post'), [
            'url' => 'https://x.com/JEPVzla',
        ])->assertStatus(422)->assertJsonValidationErrors('url');
    }

    public function test_jep_last_editorial_update_uses_the_latest_snapshot_timestamp(): void
    {
        $organization = $this->organization();
        JepMetricSnapshot::create([
            'organization_id' => $organization->id,
            'total_political_prisoners' => 1,
            'women' => 1,
            'seriously_ill' => 1,
            'foreign_or_dual_nationality' => 1,
            'releases' => 1,
            'active_retired_officials' => 1,
            'new_detentions' => 1,
            'missing_location' => 1,
            'deaths_in_custody' => 1,
            'valid_from' => '2026-09-07 22:25:00',
        ]);
        JepMetricSnapshot::create([
            'organization_id' => $organization->id,
            'total_political_prisoners' => 2,
            'women' => 2,
            'seriously_ill' => 2,
            'foreign_or_dual_nationality' => 2,
            'releases' => 2,
            'active_retired_officials' => 2,
            'new_detentions' => 2,
            'missing_location' => 2,
            'deaths_in_custody' => 2,
            'valid_from' => '2026-09-07 23:10:00',
        ]);

        $lastUpdate = app(\App\Services\JepEditorialMetricsService::class)->lastEditorialUpdate($organization);

        $this->assertNotNull($lastUpdate);
        $this->assertSame('2026-09-07 19:10:00', $lastUpdate->format('Y-m-d H:i:s'));
        $this->assertSame('America/Caracas', $lastUpdate->getTimezone()->getName());
    }

    private function organization(): Organization
    {
        return Organization::create(['slug' => 'jep', 'name' => 'Justicia, Encuentro y Perdón', 'active' => true]);
    }

    private function payload(array $source): array
    {
        $keys = [
            'total_political_prisoners', 'women', 'seriously_ill', 'foreign_or_dual_nationality', 'releases',
            'releases_period_start_month', 'releases_period_start_day', 'releases_period_start_year',
            'releases_period_end_month', 'releases_period_end_day', 'releases_period_end_year',
            'active_retired_officials', 'new_detentions', 'missing_location', 'deaths_in_custody',
            'deaths_period_start_month', 'deaths_period_start_year', 'deaths_period_end_month', 'deaths_period_end_year',
            'detentions_methodology_note',
            'monthly_alert_title', 'monthly_alert_excerpt', 'monthly_alert_x_url',
            'featured_indicator_title', 'featured_indicator_text', 'featured_indicator_instagram_url', 'featured_indicator_x_url',
        ];
        $payload = collect($keys)->mapWithKeys(fn ($key) => [$key => $source[$key] ?? null])->all();
        $payload['data_date'] = null;
        $payload['groups'] = [
            ['group_key' => 'sindicalistas', 'label' => 'Sindicalistas', 'value' => 2, 'sort_order' => 1],
            ['group_key' => 'organizaciones_politicas', 'label' => 'Organizaciones políticas', 'value' => 10, 'sort_order' => 2],
            ['group_key' => 'sociedad_civil', 'label' => 'Sociedad civil', 'value' => 222, 'sort_order' => 3],
        ];
        $payload['centers'] = [
            ['name' => 'Centro Penitenciario Rodeo I', 'value' => 109, 'sort_order' => 1],
            ['name' => 'Centro Nacional de Procesados Militares (Ramo Verde)', 'value' => 76, 'sort_order' => 2],
            ['name' => 'Centro Penitenciario Fuerte Guaicaipuro', 'value' => 46, 'sort_order' => 3],
        ];
        $payload['death_custody_distribution'] = [
            ['category_key' => 'home_arrest', 'label' => 'Arresto domiciliario', 'value' => 2, 'sort_order' => 1],
            ['category_key' => 'detention_centers', 'label' => 'En centros de reclusión', 'value' => 7, 'sort_order' => 2],
            ['category_key' => 'hospitals', 'label' => 'Hospitales', 'value' => 1, 'sort_order' => 3],
        ];
        return $payload;
    }

    private function userWithPermissions(array $permissions): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $role = Role::create(['name' => 'jep-test-'.uniqid(), 'guard_name' => 'web']);
        $role->syncPermissions(collect($permissions)->map(fn ($name) => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web'])));
        $user->assignRole($role);
        return $user;
    }
}
