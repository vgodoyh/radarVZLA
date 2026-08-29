<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserActivityTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_recent_activity_is_online_and_old_or_missing_activity_is_offline(): void
    {
        Carbon::setTestNow('2026-08-28 18:00:00');
        $online = User::factory()->create(['last_activity_at' => now()->subMinutes(2)]);
        $offline = User::factory()->create(['last_activity_at' => now()->subMinutes(6)]);
        $never = User::factory()->create(['last_activity_at' => null]);

        $this->assertTrue($online->isOnline());
        $this->assertFalse($offline->isOnline());
        $this->assertFalse($never->isOnline());
    }

    public function test_admin_activity_middleware_updates_once_and_is_throttled(): void
    {
        Carbon::setTestNow('2026-08-28 18:00:00');
        $user = $this->admin();

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
        $user->refresh();
        $firstActivity = $user->last_activity_at;

        Carbon::setTestNow('2026-08-28 18:01:00');
        $this->actingAs($user)->get(route('dashboard'))->assertOk();
        $user->refresh();

        $this->assertTrue($user->last_activity_at->equalTo($firstActivity));
    }

    public function test_activity_stores_device_platform_and_browser_in_the_same_update(): void
    {
        Carbon::setTestNow('2026-08-28 18:00:00');
        $user = $this->admin();

        $this->actingAs($user)
            ->withHeader('User-Agent', 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Version/17.0 Mobile/15E148 Safari/604.1')
            ->get(route('dashboard'))
            ->assertOk();

        $user->refresh();

        $this->assertSame('mobile', $user->last_device_type);
        $this->assertSame('iOS', $user->last_platform);
        $this->assertSame('Safari', $user->last_browser);
    }

    public function test_activity_card_is_visible_only_to_general_admins(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Usuarios conectados');

        $organizationUser = User::factory()->create();
        $role = Role::create(['name' => 'activity-test-organization', 'guard_name' => 'web']);
        $organizationUser->assignRole($role);

        $this->actingAs($organizationUser)->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Usuarios conectados');
    }

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::create(['name' => 'admin', 'guard_name' => 'web']));

        return $user;
    }
}
