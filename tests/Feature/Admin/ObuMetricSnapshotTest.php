<?php

namespace Tests\Feature\Admin;

use App\Models\ObuMetricSnapshot;
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
            ->assertSee('Historial de cifras de OBU');
    }

    public function test_public_index_and_obu_panorama_use_the_same_current_complaints_value(): void
    {
        app()->setLocale('es');
        $this->organization();
        $this->snapshot(['complaints' => 68]);
        $this->seed(ObuMonitoringPeriodSeeder::class);
        $this->seed(ObuDatasetSeeder::class);

        $index = $this->get(route('dashboard.public'))->assertOk();
        $panorama = $this->get(route('organizations.universidades'))->assertOk();

        $this->assertStringContainsString('Denuncias Universitarias', $index->getContent());
        $this->assertStringContainsString('>68<', $index->getContent());
        $this->assertStringNotContainsString('Universidades en cifras', $panorama->getContent());
        $this->assertStringContainsString('Denuncias registradas', $panorama->getContent());
        $this->assertStringContainsString('934', $panorama->getContent());
        $this->assertStringContainsString('assets/img/mapa-obu.png', $panorama->getContent());
        $this->assertStringContainsString('UCV', $panorama->getContent());
    }

    public function test_update_creates_a_new_version_and_closes_the_previous_one(): void
    {
        $user = $this->userWithPermissions(['view obu dashboard', 'edit obu metrics']);
        $organization = $this->organization();
        $old = $this->snapshot(['complaints' => 68]);

        Carbon::setTestNow('2026-08-30 12:00:00');
        $this->actingAs($user)->patch(route('admin.obu.metrics.update'), [
            'universities_monitored' => 94,
            'protests' => 75,
            'complaints' => 70,
            'data_date' => null,
        ])->assertRedirect(route('admin.obu.index'))
            ->assertSessionHas('obu_metrics_success', 'Cifras de OBU actualizadas correctamente.');

        $old->refresh();
        $current = ObuMetricSnapshot::query()->where('organization_id', $organization->id)->current()->firstOrFail();
        $this->assertSame('2026-08-30 12:00:00', $old->valid_until->toDateTimeString());
        $this->assertTrue($old->valid_until->equalTo($current->valid_from));
        $this->assertSame(70, $current->complaints);
        $this->assertNull($current->valid_until);
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
        $this->assertNull($current->valid_until);
    }

    public function test_unchanged_values_do_not_create_a_new_version(): void
    {
        $user = $this->userWithPermissions(['edit obu metrics']);
        $this->organization();
        $this->snapshot();

        $this->actingAs($user)->patch(route('admin.obu.metrics.update'), [
            'universities_monitored' => 94,
            'protests' => 75,
            'complaints' => 68,
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
            ])
            ->assertForbidden();
    }

    public function test_obu_role_seeder_is_idempotent_and_admin_can_edit(): void
    {
        $this->seed(ObuRoleSeeder::class);
        $this->seed(ObuRoleSeeder::class);

        $role = Role::findByName('obu', 'web');
        $this->assertSame(['view obu dashboard', 'edit obu metrics'], $role->permissions->pluck('name')->all());
        $this->assertCount(1, Permission::where('name', 'edit obu metrics')->where('guard_name', 'web')->get());
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
            'data_date' => null,
            'valid_from' => now()->subDay(),
        ], $overrides));
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
