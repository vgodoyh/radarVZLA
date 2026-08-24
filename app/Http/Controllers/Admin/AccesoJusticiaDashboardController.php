<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Analytics\OrganizationAnalyticsService;
use Illuminate\View\View;

class AccesoJusticiaDashboardController extends Controller
{
    public function __invoke(OrganizationAnalyticsService $analytics): View
    {
        return view(
            'admin.organizations.acceso-justicia.index',
            $analytics->dashboard('acceso_justicia'),
        );
    }
}
