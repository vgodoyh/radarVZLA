<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Component;

class Dashboard extends Component
{
    public Collection $recentUsers;

    public int $onlineUsers = 0;

    public function mount(): void
    {
        $user = auth()->user();

        if (! $user?->hasAnyRole(['admin', 'super-admin'])) {
            $this->recentUsers = collect();

            return;
        }

        $onlineSince = now()->subMinutes(5);

        $this->onlineUsers = User::query()
            ->where('last_activity_at', '>=', $onlineSince)
            ->count();

        $this->recentUsers = User::query()
            ->with('roles')
            ->orderByRaw('CASE WHEN last_activity_at >= ? THEN 0 ELSE 1 END', [$onlineSince])
            ->orderByDesc('last_activity_at')
            ->limit(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.dashboard')
            ->layout('layouts.admin');
    }
}
