@php
    $headerAccent = $headerAccent ?? '#1769f6';
    $navigationItems = [
        ['label' => 'Inicio', 'route' => 'dashboard.public'],
        ['label' => 'JEP', 'route' => 'organizations.jep'],
        ['label' => 'Acceso a la Justicia', 'route' => 'organizations.acceso-justicia'],
        ['label' => 'Fake News', 'route' => 'organizations.fake-news'],
        ['label' => 'OBU', 'route' => 'organizations.universidades'],
    ];
@endphp

<header class="pulso-global-header access-v2-header dashboard-v2-header" style="--organization-accent: {{ $headerAccent }};">
    <div class="dashboard-v2-container access-v2-shell">
        <div class="dashboard-v2-navbar access-v2-navbar">
            <a href="{{ route('dashboard.public') }}" class="dashboard-v2-brand" aria-label="{{ __('dashboard.site_name') }}">
                <img
                    src="{{ asset('assets/img/pulso-venezuela-color.png') }}"
                    alt="{{ __('dashboard.site_name') }}"
                    class="dashboard-v2-brand__isotype"
                >
            </a>

            <nav class="dashboard-v2-navigation access-v2-navigation" aria-label="{{ __('dashboard.dashboard_v2.main_navigation') }}">
                @foreach ($navigationItems as $item)
                    <a
                        href="{{ route($item['route']) }}"
                        class="{{ request()->routeIs($item['route']) ? 'active' : '' }}"
                        @if (request()->routeIs($item['route'])) aria-current="page" @endif
                    >{{ $item['label'] }}</a>
                @endforeach
            </nav>

            <div class="dashboard-v2-language" aria-label="{{ __('dashboard.language') }}">
                <a href="{{ route('language.switch', 'es') }}" class="{{ app()->isLocale('es') ? 'active' : '' }}" lang="es">ES</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('language.switch', 'en') }}" class="{{ app()->isLocale('en') ? 'active' : '' }}" lang="en">EN</a>
            </div>
        </div>
    </div>
</header>
