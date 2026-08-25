<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_the_admin_profile(): void
    {
        $this->get(route('admin.profile.edit'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_open_the_admin_profile(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.profile.edit'))
            ->assertOk()
            ->assertSee('Mi perfil')
            ->assertSee('Información personal')
            ->assertSee('Cambiar contraseña');
    }

    public function test_user_can_update_only_their_profile_information(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create(['email' => 'other@example.com']);

        $this->actingAs($user);

        Livewire::test(Profile::class)
            ->set('name', 'Nombre actualizado')
            ->set('email', 'updated@example.com')
            ->call('updateProfile')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nombre actualizado',
            'email' => 'updated@example.com',
        ]);
        $this->assertSame('other@example.com', $otherUser->refresh()->email);
    }

    public function test_email_must_be_unique(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs($user);

        Livewire::test(Profile::class)
            ->set('name', $user->name)
            ->set('email', $otherUser->email)
            ->call('updateProfile')
            ->assertHasErrors(['email']);
    }

    public function test_current_password_is_required_to_update_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('current-password')]);

        $this->actingAs($user);

        Livewire::test(Profile::class)
            ->set('current_password', 'wrong-password')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('updatePassword')
            ->assertHasErrors(['current_password']);

        $this->assertTrue(Hash::check('current-password', $user->refresh()->password));
    }

    public function test_user_can_update_password_with_the_current_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('current-password')]);

        $this->actingAs($user);

        Livewire::test(Profile::class)
            ->set('current_password', 'current-password')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('updatePassword')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
    }

    public function test_password_confirmation_must_match(): void
    {
        $user = User::factory()->create(['password' => Hash::make('current-password')]);

        $this->actingAs($user);

        Livewire::test(Profile::class)
            ->set('current_password', 'current-password')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'different-password')
            ->call('updatePassword')
            ->assertHasErrors(['password']);
    }

    public function test_profile_updates_do_not_change_roles(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'profile-test-role', 'guard_name' => 'web']);
        $user->assignRole($role);

        $this->actingAs($user);

        Livewire::test(Profile::class)
            ->set('name', 'Updated name')
            ->set('email', 'profile-role@example.com')
            ->call('updateProfile')
            ->assertHasNoErrors();

        $user->refresh();

        $this->assertTrue($user->hasRole('profile-test-role'));
        $this->assertSame([$role->id], $user->roles->pluck('id')->all());
    }
}
