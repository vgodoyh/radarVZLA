<?php

namespace Tests\Feature\Admin;

use App\Models\ObuMetricSnapshot;
use App\Models\ObuMonitoringPeriod;
use App\Models\ObuDatasetValue;
use App\Models\ObuDatasetValueVersion;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\ObuDatasetSeeder;
use Database\Seeders\ObuMetricSnapshotSeeder;
use Database\Seeders\ObuMonitoringPeriodSeeder;
use Database\Seeders\ObuRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ObuMetricSnapshotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_initial_seed_is_idempotent_and_has_requested_values(): void
    {
        $this->organization();

        $this->seed(ObuMetricSnapshotSeeder::class);
        $this->seed(ObuMetricSnapshotSeeder::class);

        $snapshot = ObuMetricSnapshot::query()->current()->firstOrFail();
        $this->assertSame(94, $snapshot->universities_monitored);
        $this->assertSame(75, $snapshot->protests);
        $this->assertSame(68, $snapshot->complaints);
        $this->assertSame(1226, $snapshot->complaints_five_years);
        $this->assertSame($this->rightsBreakdown(), $snapshot->rights_breakdown);
        $this->assertNull($snapshot->data_date);
        $this->assertSame(1, ObuMetricSnapshot::count());
    }

    public function test_admin_dashboard_requires_view_permission_and_shows_editorial_section(): void
    {
        $this->organization();
        $this->actingAs(User::factory()->create())
            ->get(route('admin.obu.index'))
            ->assertForbidden();

        $user = $this->userWithPermissions(['view obu dashboard', 'edit obu metrics']);
        $this->actingAs($user)
            ->get(route('admin.obu.index'))
            ->assertOk()
            ->assertSee('Editar cifras de OBU')
            ->assertSee('Historial de cifras de OBU')
            ->assertSee('name="analyzed_information"', false)
            ->assertDontSee('Datos del monitoreo')
            ->assertDontSee('Guardar datos de monitoreo');
    }

    public function test_public_index_uses_current_obu_complaints_value_and_period(): void
    {
        app()->setLocale('es');
        $this->organization();
        $this->snapshot();
        $this->seed(ObuMonitoringPeriodSeeder::class);
        $this->seed(ObuDatasetSeeder::class);

        $index = $this->get(route('dashboard.public'))->assertOk();
        $panorama = $this->get(route('organizations.universidades'))->assertOk();

        $this->assertStringContainsString('Denuncias Universitarias', $index->getContent());
        $indexHtml = $index->getContent();
        preg_match('/<article\\s+class="dashboard-v2-stat dashboard-v2-stat--obu"[^>]*>.*?<\\/article>/s', $indexHtml, $obuPulseCardMatches);
        $this->assertCount(1, $obuPulseCardMatches, 'No se encontró el card OBU de El Pulso.');
        $obuPulseCard = $obuPulseCardMatches[0];
        $this->assertStringContainsString('Denuncias Universitarias', $obuPulseCard);
        $this->assertStringContainsString('>68<', $obuPulseCard);
        $this->assertStringContainsString('enero', $obuPulseCard);
        $this->assertStringContainsString('junio 2026', $obuPulseCard);
        $this->assertStringNotContainsString('>1226<', $obuPulseCard);
        $this->assertStringNotContainsString('en 5 años', $obuPulseCard);
        $this->assertStringContainsString('Salarios dignos', $index->getContent());
        $this->assertStringNotContainsString('Derechos estudiantiles', $index->getContent());
    }

    public function test_update_creates_a_new_monitoring_period_version_and_closes_the_previous_one(): void
    {
        $user = $this->userWithPermissions(['view obu dashboard', 'edit obu metrics']);
        $organization = $this->organization();
        $this->snapshot();

        Carbon::setTestNow('2026-08-30 12:00:00');
        $this->actingAs($user)->patch(route('admin.obu.metrics.update'), [
            'analyzed_information' => 934,
            'protests' => 75,
            'complaints' => 70,
            'period_start' => '2026-01',
            'period_end' => '2026-06',
        ])->assertRedirect(route('admin.obu.index'))
            ->assertSessionHas('obu_metrics_success', 'Cifras de OBU actualizadas correctamente.');

        $current = ObuMonitoringPeriod::query()->where('organization_id', $organization->id)->current()->firstOrFail();
        $this->assertSame(1, ObuMonitoringPeriod::count());
        $this->assertSame('2026-01-01', $current->period_start->toDateString());
        $this->assertSame('2026-06-30', $current->period_end->toDateString());
        $this->assertSame(934, $current->analyzed_information);
        $this->assertSame(70, $current->complaints);
        $this->assertSame($user->id, $current->user_id);
        Carbon::setTestNow();
    }

    public function test_seeder_versions_old_initial_values_instead_of_overwriting_history(): void
    {
        $organization = $this->organization();
        $old = $this->snapshot([
            'universities_monitored' => 563,
            'protests' => 6,
            'complaints' => 68,
        ]);

        $this->seed(ObuMetricSnapshotSeeder::class);

        $old->refresh();
        $current = ObuMetricSnapshot::query()->where('organization_id', $organization->id)->current()->firstOrFail();
        $this->assertSame(2, ObuMetricSnapshot::count());
        $this->assertNotNull($old->valid_until);
        $this->assertTrue($old->valid_until->equalTo($current->valid_from));
        $this->assertSame(94, $current->universities_monitored);
        $this->assertSame(75, $current->protests);
        $this->assertSame(68, $current->complaints);
        $this->assertSame(1226, $current->complaints_five_years);
        $this->assertNull($current->valid_until);
    }

    public function test_current_metrics_update_preserves_five_year_complaints_history(): void
    {
        $user = $this->userWithPermissions(['edit obu metrics']);
        $this->organization();
        $snapshot = $this->snapshot();
        $this->seed(ObuMonitoringPeriodSeeder::class);

        $this->actingAs($user)->patch(route('admin.obu.metrics.update'), [
            'analyzed_information' => 934,
            'protests' => 75,
            'complaints' => 68,
            'period_start' => '2026-01',
            'period_end' => '2026-06',
        ])->assertRedirect();

        $current = ObuMonitoringPeriod::query()->current()->firstOrFail();
        $this->assertSame(68, $current->complaints);
        $this->assertSame(1, ObuMetricSnapshot::count());
        $this->assertSame(1226, $snapshot->refresh()->complaints_five_years);
    }

    public function test_unchanged_values_do_not_create_a_new_version(): void
    {
        $user = $this->userWithPermissions(['edit obu metrics']);
        $this->organization();
        $this->snapshot();
        $this->seed(ObuMonitoringPeriodSeeder::class);

        $this->actingAs($user)->patch(route('admin.obu.metrics.update'), [
            'analyzed_information' => 934,
            'protests' => 75,
            'complaints' => 68,
            'period_start' => '2026-01',
            'period_end' => '2026-06',
            'complaints_five_years' => 1226,
            'rights_breakdown' => $this->rightsBreakdown(),
            'data_date' => null,
        ])->assertRedirect()
            ->assertSessionHas('obu_metrics_info', 'No se detectaron cambios para guardar.');

        $this->assertSame(1, ObuMetricSnapshot::count());
    }

    public function test_user_without_edit_permission_cannot_modify_metrics(): void
    {
        $user = $this->userWithPermissions(['view obu dashboard']);
        $this->organization();
        $this->snapshot();

        $this->actingAs($user)
            ->patch(route('admin.obu.metrics.update'), [
                'universities_monitored' => 1,
                'protests' => 1,
                'complaints' => 1,
                'rights_breakdown' => $this->rightsBreakdown(),
            ])
            ->assertForbidden();
    }

    public function test_metrics_form_loads_the_current_monitoring_period_as_month_inputs(): void
    {
        $user = $this->userWithPermissions(['view obu dashboard', 'edit obu metrics']);
        $this->organization();
        $this->snapshot();
        $this->seed(ObuMonitoringPeriodSeeder::class);

        $this->actingAs($user)
            ->get(route('admin.obu.index'))
            ->assertOk()
            ->assertSee('Período de monitoreo')
            ->assertSee('name="analyzed_information"', false)
            ->assertSee('name="protests"', false)
            ->assertSee('name="complaints"', false)
            ->assertDontSee('Universidades monitoreadas')
            ->assertDontSee('Denuncias acumuladas en 5 aÃ±os')
            ->assertSee('name="period_start"', false)
            ->assertSee('value="2026-01"', false)
            ->assertSee('name="period_end"', false)
            ->assertSee('value="2026-06"', false);
    }

    public function test_metrics_update_versions_monitoring_period_and_public_views_use_it(): void
    {
        app()->setLocale('es');
        $user = $this->userWithPermissions(['view obu dashboard', 'edit obu metrics']);
        $this->organization();
        $this->snapshot();
        $this->seed(ObuMonitoringPeriodSeeder::class);
        $this->seed(ObuDatasetSeeder::class);

        $this->actingAs($user)->patch(route('admin.obu.metrics.update'), [
            'analyzed_information' => 934,
            'protests' => 75,
            'complaints' => 68,
            'period_start' => '2026-02',
            'period_end' => '2026-08',
        ])->assertRedirect();

        $current = ObuMonitoringPeriod::query()->current()->firstOrFail();
        $this->assertSame('2026-02-01', $current->period_start->toDateString());
        $this->assertSame('2026-08-31', $current->period_end->toDateString());
        $this->assertSame(2, ObuMonitoringPeriod::count());
    }

    public function test_metrics_update_rejects_a_monitoring_period_that_ends_before_it_starts(): void
    {
        $user = $this->userWithPermissions(['edit obu metrics']);
        $this->organization();
        $this->snapshot();
        $this->seed(ObuMonitoringPeriodSeeder::class);

        $this->actingAs($user)->from(route('admin.obu.index'))
            ->patch(route('admin.obu.metrics.update'), [
                'analyzed_information' => 934,
                'protests' => 75,
                'complaints' => 68,
                'period_start' => '2026-06',
                'period_end' => '2026-01',
            ])
            ->assertRedirect(route('admin.obu.index'))
            ->assertSessionHasErrors('period_end');

        $this->assertSame(1, ObuMonitoringPeriod::count());
    }

    public function test_simple_obu_datasets_are_rendered_as_value_cards_without_technical_fields(): void
    {
        $user = $this->userWithPermissions(['view obu dashboard', 'edit obu metrics']);
        $this->organization();
        $this->seed(ObuDatasetSeeder::class);

        $response = $this->actingAs($user)->get(route('admin.obu.index'))->assertOk();
        $html = $response->getContent();

        $this->assertSame(11, substr_count($html, 'class="obu-simple-dataset-item"'));
        $this->assertStringContainsString('Salarios dignos', $html);
        $this->assertStringContainsString('Paro', $html);
        $this->assertStringContainsString('dataset_key" value="documented_complaints', $html);
        $this->assertStringContainsString('dataset_key" value="protest_types', $html);
        $this->assertStringNotContainsString('values[1][category]', $html);
        $this->assertStringNotContainsString('values[1][sort_order]', $html);
    }

    public function test_simple_obu_dataset_updates_only_value_by_row_identity_and_keeps_version(): void
    {
        $user = $this->userWithPermissions(['view obu dashboard', 'edit obu metrics']);
        $organization = $this->organization();
        $this->seed(ObuDatasetSeeder::class);

        $march = ObuDatasetValue::query()->where('organization_id', $organization->id)->where('dataset_key', 'protest_types')->where('label', 'Marcha')->firstOrFail();
        $banner = ObuDatasetValue::query()->where('organization_id', $organization->id)->where('dataset_key', 'protest_types')->where('label', 'Pancartazo')->firstOrFail();
        $oldSortOrder = $march->sort_order;

        $this->actingAs($user)->patch(route('admin.obu.dataset.update'), [
            'dataset_key' => 'protest_types',
            'values' => [
                ['id' => $march->id, 'value' => 15],
                ['id' => $banner->id, 'value' => 8],
            ],
        ])->assertRedirect();

        $this->assertSame(15, $march->refresh()->value);
        $this->assertSame(8, $banner->refresh()->value);
        $this->assertSame($oldSortOrder, $march->sort_order);
        $this->assertTrue(ObuDatasetValueVersion::query()->where('obu_dataset_value_id', $march->id)->where('value', 14)->exists());
        $this->assertTrue(ObuDatasetValueVersion::query()->where('obu_dataset_value_id', $banner->id)->where('value', 7)->exists());
    }

    public function test_obu_role_seeder_is_idempotent_and_admin_can_edit(): void
    {
        $this->seed(ObuRoleSeeder::class);
        $this->seed(ObuRoleSeeder::class);

        $role = Role::findByName('obu', 'web');
        $this->assertSame(['view obu dashboard', 'edit obu metrics'], $role->permissions->pluck('name')->all());
        $this->assertCount(1, Permission::where('name', 'edit obu metrics')->where('guard_name', 'web')->get());
    }

    public function test_obu_last_editorial_update_uses_the_latest_editorial_model_timestamp(): void
    {
        $organization = $this->organization();
        ObuMetricSnapshot::create([
            'organization_id' => $organization->id,
            'universities_monitored' => 1,
            'protests' => 1,
            'complaints' => 1,
            'valid_from' => '2026-09-07 22:25:00',
        ]);

        $lastUpdate = app(\App\Services\ObuDashboardDataService::class)->lastEditorialUpdate();

        $this->assertNotNull($lastUpdate);
        $this->assertSame('2026-09-07 18:25:00', $lastUpdate->format('Y-m-d H:i:s'));
        $this->assertSame('America/Caracas', $lastUpdate->getTimezone()->getName());
    }

    private function organization(): Organization
    {
        return Organization::create([
            'slug' => 'universidades',
            'name' => 'Observatorio de Universidades',
            'active' => true,
        ]);
    }

    private function snapshot(array $overrides = []): ObuMetricSnapshot
    {
        return ObuMetricSnapshot::create(array_merge([
            'organization_id' => Organization::query()->where('slug', 'universidades')->value('id'),
            'universities_monitored' => 94,
            'protests' => 75,
            'complaints' => 68,
            'complaints_five_years' => 1226,
            'rights_breakdown' => $this->rightsBreakdown(),
            'data_date' => null,
            'valid_from' => now()->subDay(),
        ], $overrides));
    }

    private function rightsBreakdown(): array
    {
        return [
            'fair_wages' => 68,
            'infrastructure_damage' => 19,
            'student_welfare' => 12,
            'university_autonomy' => 8,
            'freedom_of_expression' => 6,
            'public_affairs_participation' => 14,
            'strike' => 29,
            'gathering' => 20,
            'banner_protest' => 7,
            'march' => 14,
            'other' => 5,
        ];
    }

    private function userWithPermissions(array $permissions): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $role = Role::create(['name' => 'obu-test-'.uniqid(), 'guard_name' => 'web']);
        $role->syncPermissions(collect($permissions)->map(fn ($name) => Permission::firstOrCreate([
            'name' => $name, 'guard_name' => 'web',
        ])));
        $user->assignRole($role);

        return $user;
    }
}
