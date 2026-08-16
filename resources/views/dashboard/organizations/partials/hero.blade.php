@php
    $lastSyncAt = filled($lastSync)
        ? \Carbon\Carbon::parse($lastSync)->setTimezone('America/Caracas')->locale(app()->getLocale())
        : null;
@endphp

@include('dashboard.partials.global-header', ['headerAccent' => '#ff6500'])

@include('dashboard.organizations.partials.organization-hero', [
    'heroClass' => 'organization-v2-hero--access',
    'accent' => '#ff6500',
    'accentRgb' => '255, 101, 0',
    'logo' => asset('assets/img/organizations/acceso-justicia-x.png'),
    'category' => app()->isLocale('en') ? 'Rule of law and justice' : 'Estado de Derecho y Justicia',
    'title' => $organization['name'],
    'description' => __('dashboard.acceso_institutional_description'),
    'illustrationPartial' => 'dashboard.organizations.partials.illustrations.access',
    'lastSyncAt' => $lastSyncAt,
    'timeLabel' => app()->isLocale('en') ? 'Venezuela time (GMT-4)' : 'Hora de Venezuela (GMT-4)',
])
