<?php

namespace App\Livewire\Admin\Analytics;

use App\Services\Analytics\OrganizationAnalyticsService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class AccesoJusticiaAlertRanking extends Component
{
    use WithoutUrlPagination, WithPagination;

    private const ORGANIZATION = 'acceso_justicia';

    private const PAGE_NAME = 'alertsPage';

    public function render(): View
    {
        /** @var LengthAwarePaginator<int, array<string, mixed>> $ranking */
        $ranking = app(OrganizationAnalyticsService::class)->alertRanking(
            self::ORGANIZATION,
            30,
            5,
            $this->getPage(self::PAGE_NAME),
            self::PAGE_NAME,
        );

        return view('livewire.admin.analytics.acceso-justicia-alert-ranking', [
            'ranking' => $ranking,
        ]);
    }
}
