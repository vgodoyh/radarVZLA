<?php

namespace Tests\Feature\Admin;

use App\Models\Organization;
use App\Models\OvfnPlatformDistribution;
use App\Models\OvfnVerificationTotal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OvfnVerificationTotalTest extends TestCase
{
    use RefreshDatabase;

    public function test_view_permission_can_open_ovfn_dashboard(): void
    {
        $user = $this->userWithPermissions(['view ovfn dashboard']);
        $this->organization();

        $this->actingAs($user)->get(route('admin.ovfn.index'))->assertOk();
    }

    public function test_view_only_user_can_see_current_verification_total_but_not_edit_controls(): void
    {
        $user = $this->userWithPermissions(['view ovfn dashboard']);
        $organization = $this->organization();
        OvfnVerificationTotal::create([
            'organization_id' => $organization->id, 'total' => 137,
            'data_date' => '2026-07-31', 'valid_from' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('admin.ovfn.index'))
            ->assertOk()
            ->assertSee('137')
            ->assertDontSee('Editar Total de Verificaciones')
            ->assertDontSee('name="total"');
    }

    public function test_missing_view_permission_is_forbidden(): void
    {
        $this->actingAs(User::factory()->create(['email_verified_at' => now()]))
            ->get(route('admin.ovfn.index'))
            ->assertForbidden();
    }

    public function test_update_creates_a_new_version_and_closes_the_previous_one(): void
    {
        $user = $this->userWithPermissions(['view ovfn dashboard', 'edit ovfn metrics']);
        $organization = $this->organization();
        $old = OvfnVerificationTotal::create([
            'organization_id' => $organization->id, 'total' => 137,
            'data_date' => '2026-07-31', 'valid_from' => now()->subDay(),
        ]);

        $this->actingAs($user)->patch(route('admin.ovfn.total-verifications.update'), [
            'total' => 150, 'data_date' => '2026-08-31',
        ])->assertRedirect(route('admin.ovfn.index'))
            ->assertSessionHas('ovfn_verification_success', 'Total de verificaciones actualizado correctamente.');

        $old->refresh();
        $current = OvfnVerificationTotal::query()->current()->firstOrFail();
        $this->assertNotNull($old->valid_until);
        $this->assertTrue($old->valid_until->equalTo($current->valid_from));
        $this->assertSame(150, $current->total);
        $this->assertSame('2026-08-31', $current->data_date->toDateString());
        $this->assertSame($user->id, $current->user_id);
        $this->assertSame(1, OvfnVerificationTotal::query()->current()->count());
    }

    public function test_unchanged_values_do_not_create_a_new_version(): void
    {
        $user = $this->userWithPermissions(['edit ovfn metrics']);
        $organization = $this->organization();
        OvfnVerificationTotal::create([
            'organization_id' => $organization->id, 'total' => 137,
            'data_date' => '2026-07-31', 'valid_from' => now(),
        ]);

        $this->actingAs($user)->patch(route('admin.ovfn.total-verifications.update'), [
            'total' => 137, 'data_date' => '2026-07-31',
        ])->assertRedirect()
            ->assertSessionHas('ovfn_verification_info', 'No se detectaron cambios para guardar.');

        $this->assertSame(1, OvfnVerificationTotal::count());
    }

    public function test_user_without_edit_permission_cannot_update(): void
    {
        $user = $this->userWithPermissions(['view ovfn dashboard']);

        $this->actingAs($user)
            ->patch(route('admin.ovfn.total-verifications.update'), ['total' => 1, 'data_date' => '2026-08-01'])
            ->assertForbidden();
    }

    public function test_last_editorial_update_uses_the_latest_ovfn_version_and_not_the_data_date(): void
    {
        $organization = $this->organization();

        OvfnVerificationTotal::create([
            'organization_id' => $organization->id,
            'total' => 150,
            'data_date' => '2030-01-01',
            'valid_from' => '2026-09-07 22:25:00',
        ]);
        OvfnPlatformDistribution::create([
            'organization_id' => $organization->id,
            'data_from_date' => '2030-02-02',
            'valid_from' => '2026-09-07 23:10:00',
        ]);

        $lastUpdate = app(\App\Services\OvfnEditorialMetricsService::class)->lastEditorialUpdate();

        $this->assertNotNull($lastUpdate);
        $this->assertSame('2026-09-07 19:10:00', $lastUpdate->format('Y-m-d H:i:s'));
        $this->assertSame('America/Caracas', $lastUpdate->getTimezone()->getName());
    }

    public function test_last_editorial_update_is_null_when_no_editorial_versions_exist(): void
    {
        $this->organization();

        $this->assertNull(app(\App\Services\OvfnEditorialMetricsService::class)->lastEditorialUpdate());
    }

    private function organization(): Organization
    {
        return Organization::create(['slug' => 'fake-news', 'name' => 'OVFN', 'active' => true]);
    }

    private function userWithPermissions(array $permissions): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $role = Role::create(['name' => 'test-'.uniqid(), 'guard_name' => 'web']);
        $role->syncPermissions(collect($permissions)->map(fn ($name) => Permission::firstOrCreate([
            'name' => $name, 'guard_name' => 'web',
        ])));
        $user->assignRole($role);

        return $user;
    }
}
