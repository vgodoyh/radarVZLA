<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PublicDashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            ['label' => __('dashboard.stats.political_prisoners'), 'value' => '1.875', 'change' => '+5,2%', 'icon' => 'bi-people-fill', 'trend' => 'up'],
            ['label' => __('dashboard.stats.new_detentions'), 'value' => '142', 'change' => '+18,3%', 'icon' => 'bi-person-lock', 'trend' => 'up-danger'],
            ['label' => __('dashboard.stats.women'), 'value' => '234', 'change' => '+6,4%', 'icon' => 'bi-gender-female', 'trend' => 'up'],
            ['label' => __('dashboard.stats.murders'), 'value' => '23', 'change' => '+21,1%', 'icon' => 'bi-droplet-fill', 'trend' => 'up-danger'],
            ['label' => __('dashboard.stats.releases'), 'value' => '87', 'change' => '+12,9%', 'icon' => 'bi-unlock-fill', 'trend' => 'up'],
        ];

        return view('dashboard.index', compact('stats'));
    }
}
