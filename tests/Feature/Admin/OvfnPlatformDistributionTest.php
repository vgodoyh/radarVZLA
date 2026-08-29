<?php

namespace Tests\Feature\Admin;

use App\Models\Organization;
use App\Models\OvfnPlatformDistribution;
use App\Models\OvfnPlatformDistributionItem;
use App\Models\OvfnVerificationTotal;
use App\Models\User;
use App\Services\OvfnEditorialMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OvfnPlatformDistributionTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_service_returns_real_items_and_derived_percentages(): void
    {
        $organization = $this->organization();
        $distribution = OvfnPlatformDistribution::create([
            'organization_id' => $organization->id, 'data_from_date' => '2026-06-01', 'valid_from' => now(),
        ]);
        foreach (['tiktok' => 48, 'whatsapp' => 41, 'x' => 17, 'instagram' => 16, 'facebook' => 11] as $platform => $value) {
            OvfnPlatformDistributionItem::create(['distribution_id' => $distribution->id, 'platform' => $platform, 'value' => $value]);
        }

        $items = app(OvfnEditorialMetricsService::class)->currentPlatformItems();

        $this->assertSame(36.1, $items->firstWhere('key', 'tiktok')['percentage']);
        $this->assertSame(30.8, $items->firstWhere('key', 'whatsapp')['percentage']);
        $this->assertSame(133, $items->sum('total'));
    }

    public function test_public_index_and_fake_news_page_use_the_current_verification_total(): void
    {
        $this->withoutVite();
        Http::fake(['https://fakenewsvenezuela.org/*' => Http::response('', 200)]);
        $organization = $this->organization();
        OvfnVerificationTotal::create([
            'organization_id' => $organization->id, 'total' => 157,
            'data_date' => '2026-08-28', 'valid_from' => now(),
        ]);

        $this->get(route('dashboard.public'))->assertOk()->assertSee('157');
        $this->get(route('organizations.fake-news'))->assertOk()->assertSee('157');
    }

    public function test_update_creates_a_new_version_and_closes_the_previous_one(): void
    {
        $user = $this->userWithPermission('edit ovfn metrics');
        $organization = $this->organization();
        $old = OvfnPlatformDistribution::create(['organization_id' => $organization->id, 'data_from_date' => '2026-06-01', 'valid_from' => now()]);
        foreach (['tiktok' => 48, 'whatsapp' => 41, 'x' => 17, 'instagram' => 16, 'facebook' => 11] as $platform => $value) {
            OvfnPlatformDistributionItem::create(['distribution_id' => $old->id, 'platform' => $platform, 'value' => $value]);
        }

        $this->actingAs($user)->patch(route('admin.ovfn.platform-distribution.update'), [
            'data_from_date' => '2026-08-01',
            'platforms' => ['tiktok' => 50, 'whatsapp' => 41, 'x' => 17, 'instagram' => 16, 'facebook' => 11],
        ])->assertRedirect(route('admin.ovfn.index'))
            ->assertSessionHas('ovfn_distribution_success', 'Distribución por plataforma actualizada correctamente.');

        $current = OvfnPlatformDistribution::query()->current()->with('items')->firstOrFail();
        $old->refresh();
        $this->assertNotNull($old->valid_until);
        $this->assertTrue($old->valid_until->equalTo($current->valid_from));
        $this->assertSame(50, $current->items->firstWhere('platform', 'tiktok')->value);
        $this->assertSame($user->id, $current->user_id);
        $this->assertSame(1, OvfnPlatformDistribution::query()->current()->count());
    }

    public function test_unchanged_distribution_does_not_create_a_new_version(): void
    {
        $user = $this->userWithPermission('edit ovfn metrics');
        $organization = $this->organization();
        $current = OvfnPlatformDistribution::create(['organization_id' => $organization->id, 'data_from_date' => '2026-06-01', 'valid_from' => now()]);
        foreach (['tiktok' => 48, 'whatsapp' => 41, 'x' => 17, 'instagram' => 16, 'facebook' => 11] as $platform => $value) {
            OvfnPlatformDistributionItem::create(['distribution_id' => $current->id, 'platform' => $platform, 'value' => $value]);
        }

        $this->actingAs($user)->patch(route('admin.ovfn.platform-distribution.update'), [
            'data_from_date' => '2026-06-01', 'platforms' => ['tiktok' => 48, 'whatsapp' => 41, 'x' => 17, 'instagram' => 16, 'facebook' => 11],
        ])->assertSessionHas('ovfn_distribution_info', 'No se detectaron cambios para guardar.');

        $this->assertSame(1, OvfnPlatformDistribution::count());
    }

    public function test_user_without_edit_permission_cannot_update_distribution(): void
    {
        $this->actingAs(User::factory()->create(['email_verified_at' => now()]))
            ->patch(route('admin.ovfn.platform-distribution.update'), ['data_from_date' => '2026-06-01', 'platforms' => []])
            ->assertForbidden();
    }

    private function organization(): Organization
    {
        return Organization::create(['slug' => 'fake-news', 'name' => 'OVFN', 'active' => true]);
    }

    private function userWithPermission(string $permission): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $role = Role::create(['name' => 'test-'.uniqid(), 'guard_name' => 'web']);
        $role->syncPermissions(Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']));
        $user->assignRole($role);

        return $user;
    }
}
