<?php

namespace Tests\Feature\Admin;

use App\Jobs\SyncDashboardData;
use App\Models\DashboardSyncRun;
use App\Models\Organization;
use App\Models\Publication;
use App\Models\User;
use Database\Seeders\AccessJusticeRoleSeeder;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrganizationDashboardAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_admin_user_seeder_is_idempotent_and_preserves_existing_credentials(): void
    {
        $user = User::factory()->create([
            'name' => 'Nombre existente',
            'email' => 'vanessa.godoy.h@gmail.com',
            'password' => Hash::make('contraseña-existente'),
        ]);
        $password = $user->password;

        $this->seed(AdminUserSeeder::class);
        $this->seed(AdminUserSeeder::class);

        $user->refresh();
        $admin = Role::findByName('admin', 'web');
        $organizationPermissions = [
            'view jep dashboard',
            'view acceso justicia dashboard',
            'view ovfn dashboard',
            'view obu dashboard',
        ];

        $this->assertSame('Nombre existente', $user->name);
        $this->assertSame($password, $user->password);
        $this->assertTrue($user->hasRole($admin));
        $this->assertCount(1, Role::where('name', 'admin')->where('guard_name', 'web')->get());
        $this->assertCount(4, Permission::whereIn('name', $organizationPermissions)->where('guard_name', 'web')->get());
        $this->assertTrue($admin->hasAllPermissions($organizationPermissions));
        $this->assertCount(0, $user->permissions);

        $this->actingAs($user);
        $adminSidebar = view('admin.partials.sidebar')->render();
        $this->assertStringContainsString('Publicar', $adminSidebar);
        $this->assertStringContainsString('Informes', $adminSidebar);
        $this->assertStringContainsString('Permisos', $adminSidebar);
        $this->assertStringContainsString('Roles', $adminSidebar);
        $this->assertStringContainsString('Usuarios', $adminSidebar);
    }

    public function test_user_without_permission_cannot_access_access_justice_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.acceso-justicia.index'))
            ->assertForbidden();
    }

    public function test_access_justice_role_seeder_is_idempotent_and_has_only_dashboard_permission(): void
    {
        $this->seed(AccessJusticeRoleSeeder::class);
        $this->seed(AccessJusticeRoleSeeder::class);

        $role = Role::findByName('acceso-justicia', 'web');

        $this->assertCount(1, Role::where('name', 'acceso-justicia')->where('guard_name', 'web')->get());
        $this->assertSame(['view acceso justicia dashboard'], $role->permissions->pluck('name')->all());
        $this->assertFalse($role->permissions->pluck('name')->contains('view jep dashboard'));
        $this->assertFalse($role->permissions->pluck('name')->contains('view ovfn dashboard'));
        $this->assertFalse($role->permissions->pluck('name')->contains('view obu dashboard'));
        $this->assertFalse($role->permissions->pluck('name')->contains('manage users'));
    }

    public function test_user_with_access_justice_permission_can_access_dashboard(): void
    {
        $user = $this->userWithRolePermission('access-role', 'view acceso justicia dashboard');
        $organization = Organization::create([
            'slug' => 'acceso-justicia',
            'name' => 'Acceso a la Justicia',
            'active' => true,
            'last_synced_at' => Carbon::parse('2026-08-22 09:15', 'America/Caracas')->utc(),
        ]);
        Publication::create([
            'organization_id' => $organization->id,
            'source' => 'x',
            'external_id' => 'alerta-1',
            'excerpt' => '#AlertaLegal de prueba',
            'url' => 'https://example.org/alerta-1',
        ]);

        $this->actingAs($user)
            ->get(route('admin.acceso-justicia.index'))
            ->assertOk()
            ->assertSee('Visitas al portal Pulso Venezuela')
            ->assertSee('Clics desde Pulso')
            ->assertSee('Alertas sincronizadas')
            ->assertSee('Última sincronización')
            ->assertSee('22 ago. 2026')
            ->assertSee('Aún no hay ejecuciones registradas')
            ->assertDontSee('Sincronizar ahora');
    }

    public function test_sync_action_requires_access_justice_permission(): void
    {
        Queue::fake();

        $this->actingAs(User::factory()->create())
            ->post(route('admin.acceso-justicia.sync'))
            ->assertForbidden();

        $accessUser = $this->userWithRolePermission('acceso-justicia', 'view acceso justicia dashboard');

        $this->actingAs($accessUser)
            ->post(route('admin.acceso-justicia.sync'))
            ->assertForbidden();

        Queue::assertNothingPushed();
    }

    public function test_admin_can_queue_existing_sync_job_once(): void
    {
        Queue::fake();
        $user = $this->userWithRolePermission('admin', 'sync acceso justicia dashboard');

        $this->actingAs($user)
            ->post(route('admin.acceso-justicia.sync'))
            ->assertRedirect();

        $run = DashboardSyncRun::firstOrFail();
        $this->assertSame('acceso_justicia', $run->organization);
        $this->assertSame('publications', $run->process);
        $this->assertSame('running', $run->status);
        Queue::assertPushed(
            SyncDashboardData::class,
            fn (SyncDashboardData $job) => $job->organizationSlug === 'acceso-justicia'
                && $job->runId === $run->id,
        );

        $this->actingAs($user)
            ->post(route('admin.acceso-justicia.sync'))
            ->assertSessionHas('sync_error');

        Queue::assertPushed(SyncDashboardData::class, 1);
    }

    public function test_jep_permission_does_not_grant_access_to_access_justice_dashboard(): void
    {
        $user = $this->userWithRolePermission('jep-role', 'view jep dashboard');

        $this->actingAs($user)
            ->get(route('admin.acceso-justicia.index'))
            ->assertForbidden();
    }

    public function test_sidebar_respects_organization_permissions(): void
    {
        $accessUser = $this->userWithRolePermission('access-role', 'view acceso justicia dashboard');
        $this->actingAs($accessUser);
        $accessSidebar = view('admin.partials.sidebar')->render();

        $this->assertStringContainsString('Acceso a la Justicia', $accessSidebar);
        $this->assertStringNotContainsString('Inicio', $accessSidebar);
        $this->assertStringNotContainsString('>JEP</span>', $accessSidebar);
        $this->assertStringNotContainsString('>OVFN</span>', $accessSidebar);
        $this->assertStringNotContainsString('>OBU</span>', $accessSidebar);
        $this->assertStringNotContainsString('Publicar', $accessSidebar);
        $this->assertStringNotContainsString('Informes', $accessSidebar);
        $this->assertStringNotContainsString('Permisos', $accessSidebar);
        $this->assertStringNotContainsString('Roles', $accessSidebar);
        $this->assertStringNotContainsString('Usuarios', $accessSidebar);
        $this->assertStringNotContainsString('Seguridad del Sistema', $accessSidebar);

        $jepUser = $this->userWithRolePermission('jep-role', 'view jep dashboard');
        $this->actingAs($jepUser);
        $jepSidebar = view('admin.partials.sidebar')->render();

        $this->assertStringContainsString('>JEP</span>', $jepSidebar);
        $this->assertStringNotContainsString('Acceso a la Justicia', $jepSidebar);
    }

    public function test_access_justice_role_is_redirected_from_admin_and_blocked_from_system_management_routes(): void
    {
        $user = $this->userWithRolePermission('acceso-justicia', 'view acceso justicia dashboard');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('admin.acceso-justicia.index', absolute: false));

        $this->actingAs($user)
            ->get(route('admin.acceso-justicia.index'))
            ->assertOk();

        foreach ([route('user.index'), route('role.index'), route('permission.index')] as $url) {
            $this->actingAs($user)->get($url)->assertForbidden();
        }
    }

    private function userWithRolePermission(string $roleName, string $permissionName): User
    {
        $permission = Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        $role->syncPermissions([$permission]);
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
