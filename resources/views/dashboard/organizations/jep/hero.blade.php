@php
    $lastSyncAt = filled($lastSync ?? null)
        ? \Carbon\Carbon::parse($lastSync)->setTimezone('America/Caracas')->locale(app()->getLocale())
        : null;
@endphp

@include('dashboard.organizations.partials.organization-hero', [
    'heroClass' => 'organization-v2-hero--jep',
    'accent' => '#1769f6',
    'accentRgb' => '23, 105, 246',
    'logo' => $organization['logo'],
    'category' => __('dashboard.jep_page.badge'),
    'title' => $organization['name'],
    'description' => __('dashboard.jep_page.description'),
    'illustrationPartial' => 'dashboard.organizations.partials.illustrations.jep',
    'lastSyncAt' => $lastSyncAt,
    'timeLabel' => app()->isLocale('en') ? 'Venezuela time (GMT-4)' : 'Hora de Venezuela (GMT-4)',
])
