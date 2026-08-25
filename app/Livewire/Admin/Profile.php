<?php

namespace App\Livewire\Admin;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Mi perfil')]
class Profile extends Component
{
    use PasswordValidationRules;
    use ProfileValidationRules;

    public string $name = '';

    public string $email = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        $user = Auth::user();

        $this->name = $user->name;
        $this->email = $user->email;
    }

    public function updateProfile(): void
    {
        $user = Auth::user();
        $this->email = Str::lower(trim($this->email));

        $validated = $this->validate($this->profileRules($user->id));
        $emailChanged = $user->email !== $validated['email'];

        $user->fill($validated);

        if ($emailChanged && $user instanceof MustVerifyEmail) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($emailChanged && $user instanceof MustVerifyEmail) {
            $user->sendEmailVerificationNotification();
        }

        session()->flash('status', 'Perfil actualizado correctamente.');
    }

    public function updatePassword(): void
    {
        $validated = $this->validate([
            'current_password' => $this->currentPasswordRules(),
            'password' => $this->passwordRules(),
        ]);

        Auth::user()->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        $this->reset('current_password', 'password', 'password_confirmation');

        session()->flash('status', 'Contraseña actualizada correctamente.');
    }

    public function render()
    {
        return view('livewire.admin.profile')
            ->layout('layouts.admin');
    }
}
